<?php

namespace Tests\Feature;

use App\Exceptions\AiUnavailableException;
use App\Http\Livewire\SalesOrderTemplate\Index;
use App\Http\Livewire\SalesOrderTemplate\PoScan;
use App\Models\Account;
use App\Models\AccountProductReference;
use App\Models\AiUsageLog;
use App\Models\Brand;
use App\Models\PoScanFormat;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Services\PoScanner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesAnthropic;
use Tests\Concerns\FakesGemini;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

class PoScanTest extends TestCase
{
    use DatabaseTransactions;
    use FakesAnthropic;
    use FakesGemini;
    use SeedsReferenceData;

    private const PDF_CONTENT = "%PDF-1.4
% fake purchase order
";

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('tmp-for-tests');
        $this->configureGemini();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->account = Account::factory()->create(['account_code' => 'MET001', 'account_name' => 'METRO SUPERMARKET']);
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions = ['so template access', 'po scan access'], bool $linkAccount = true): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        if ($linkAccount) {
            $user->accounts()->attach($this->account->id);
        }

        $this->actingAs($user);

        return $user;
    }

    private function pdf(string $name = 'metro-po.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, self::PDF_CONTENT);
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     * @return array<string, mixed>
     */
    private function purchaseOrder(array $lines, string $storeCode = 'STORE-7'): array
    {
        return ['purchase_orders' => [[
            'po_number'         => 'PO-55501',
            'po_date'           => '2026-10-12',
            'delivery_date'     => '10/20/2026',
            'cancellation_date' => '',
            'store_code'        => $storeCode,
            'store_name'        => 'METRO CEBU',
            'delivery_location' => 'Cebu DC',
            'remarks'           => 'Deliver before 10AM',
            'lines'             => $lines,
        ]]];
    }

    private function fakeGemini(array $json, string $finishReason = 'STOP'): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply($json, $finishReason))]);
    }

    private function usageLogs(): \Illuminate\Database\Eloquent\Collection
    {
        return AiUsageLog::where('account_id', $this->account->id)->get();
    }

    // -------------------------------------------------------------------------
    // Tab access
    // -------------------------------------------------------------------------

    public function test_scan_tab_is_hidden_without_permission(): void
    {
        $this->actingAsUser(['so template access']);

        Livewire::test(Index::class)
            ->assertDontSee('Scan PDF')
            ->call('setTab', 'scan')
            ->assertSet('activeTab', 'upload');
    }

    public function test_scan_tab_opens_with_permission(): void
    {
        $this->actingAsUser();

        Livewire::test(Index::class)
            ->assertSee('Scan PDF')
            ->call('setTab', 'scan')
            ->assertSet('activeTab', 'scan')
            ->assertSee('Scan Account PO (PDF)');
    }

    public function test_scanning_is_forbidden_without_permission(): void
    {
        $this->actingAsUser(['so template access']);
        Http::fake();

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertForbidden();

        Http::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_scan_requires_an_account_and_a_pdf(): void
    {
        $this->actingAsUser();

        Livewire::test(PoScan::class)
            ->call('scan')
            ->assertHasErrors(['account_id' => 'required', 'files' => 'required']);
    }

    public function test_scan_rejects_files_that_are_not_pdf(): void
    {
        $this->actingAsUser();
        Http::fake();

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [UploadedFile::fake()->create('orders.xlsx', 20, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')])
            ->call('scan')
            ->assertHasErrors(['files.0' => 'mimes']);

        Http::assertNothingSent();
    }

    public function test_scan_refuses_an_account_the_user_is_not_assigned_to(): void
    {
        $this->actingAsUser(linkAccount: false);
        Http::fake();

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertHasErrors('account_id');

        Http::assertNothingSent();
        $this->assertCount(0, $this->usageLogs());
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_selecting_an_account_picks_its_active_format(): void
    {
        $this->actingAsUser();
        PoScanFormat::factory()->inactive()->create(['account_id' => $this->account->id, 'name' => 'A old']);
        $format = PoScanFormat::factory()->create(['account_id' => $this->account->id, 'name' => 'B current']);

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->assertSet('format_id', (string) $format->id)
            ->assertSee('B current')
            ->assertDontSee('A old');
    }

    public function test_a_scanned_po_becomes_template_rows_using_the_account_format(): void
    {
        $user = $this->actingAsUser();

        $brandId = Brand::factory()->create(['brand' => 'KOJIE SAN'])->id;
        Product::factory()->create(['brand_id' => $brandId, 'stock_code' => 'KS1001', 'description' => 'KOJIESAN SOAP', 'size' => '135G']);
        $referenced = Product::factory()->create(['brand_id' => $brandId, 'stock_code' => 'KS2002']);
        AccountProductReference::create([
            'account_id'        => $this->account->id,
            'product_id'        => $referenced->id,
            'account_reference' => '987654',
            'description'       => 'METRO ARTICLE',
            'active'            => 1,
        ]);
        $barcoded = Product::factory()->create(['brand_id' => $brandId, 'stock_code' => 'KS3003', 'bar_code' => '4800000000017']);
        $address = ShippingAddress::factory()->create([
            'account_id'   => $this->account->id,
            'address_code' => 'STORE-7',
            'ship_to_name' => 'METRO CEBU',
            'building'     => 'Bldg 1',
            'street'       => 'Osmena Blvd',
            'city'         => 'Cebu',
        ]);
        $format = PoScanFormat::factory()->create([
            'account_id'   => $this->account->id,
            'instructions' => 'The item code is in the Article column.',
            'default_uom'  => 'PCS',
        ]);

        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'KS1001', 'barcode' => '', 'description' => 'SOAP 135G', 'quantity' => 12.0, 'uom' => 'CS', 'unit_price' => 1250.5, 'amount' => 15006],
            ['sku' => '987654', 'barcode' => '', 'description' => 'METRO ARTICLE', 'quantity' => 3, 'uom' => '', 'unit_price' => null, 'amount' => null],
            ['sku' => 'UNKNOWN', 'barcode' => '4800000000017', 'description' => 'BY BARCODE', 'quantity' => 5, 'uom' => 'IN', 'unit_price' => 10, 'amount' => 50],
            ['sku' => 'NOPE', 'barcode' => '', 'description' => 'NOT OURS', 'quantity' => 1, 'uom' => 'PCS', 'unit_price' => 1, 'amount' => 1],
            ['sku' => 'SKIPPED', 'barcode' => '', 'description' => 'NO QTY', 'quantity' => null, 'uom' => '', 'unit_price' => null, 'amount' => null],
        ]));

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->assertSet('format_id', (string) $format->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertHasNoErrors()
            ->assertSet('scanErrors', [])
            ->assertEmitted('poScanned', [
                'total' => 4, 'ok' => 3, 'sku_not_found' => 1, 'address_not_found' => 0, 'both_not_found' => 0,
            ], []);

        $rows = Session::get('so_template_rows');
        $this->assertCount(4, $rows);

        $this->assertSame('PO-55501', $rows[0]['po_number']);
        $this->assertSame('2026-10-12', $rows[0]['po_date']);
        $this->assertSame('2026-10-20', $rows[0]['delivery_date']);
        $this->assertSame('Deliver before 10AM', $rows[0]['po_remarks']);
        $this->assertSame('12', $rows[0]['qty']);
        $this->assertSame('1250.5', $rows[0]['list_price']);
        $this->assertSame('KS1001', $rows[0]['internal_sku']);
        $this->assertSame('KOJIESAN SOAP 135G', $rows[0]['product_name']);
        $this->assertSame($address->id, $rows[0]['shipping_address_id']);
        $this->assertSame('MET001', $rows[0]['account_code']);
        $this->assertSame('CS', $rows[0]['so_uom']);
        $this->assertSame('metro-po.pdf', $rows[0]['source_file']);
        $this->assertSame('ok', $rows[0]['lookup_status']);

        $this->assertSame('KS2002', $rows[1]['internal_sku']);
        $this->assertSame('PCS', $rows[1]['so_uom'], 'A line without a unit takes the format default.');

        $this->assertSame($barcoded->stock_code, $rows[2]['internal_sku']);
        $this->assertSame('IN', $rows[2]['so_uom']);

        $this->assertSame('sku_not_found', $rows[3]['lookup_status']);
        $this->assertSame('MET001', $rows[3]['account_code'], 'Every scanned row belongs to the chosen account.');

        Http::assertSent(function (Request $request): bool {
            return str_ends_with($request->url(), 'models/gemini-test:generateContent')
                && data_get($request->data(), 'contents.0.parts.0.inlineData.mimeType') === 'application/pdf'
                && data_get($request->data(), 'contents.0.parts.0.inlineData.data') === base64_encode(self::PDF_CONTENT)
                && str_contains(data_get($request->data(), 'contents.0.parts.1.text'), 'The item code is in the Article column.')
                && str_contains(data_get($request->data(), 'contents.0.parts.1.text'), '[MET001] METRO SUPERMARKET')
                && str_contains(data_get($request->data(), 'contents.0.parts.1.text'), 'Unit to use when the PO does not say: PCS');
        });

        $log = $this->usageLogs()->sole();
        $this->assertSame(AiUsageLog::STATUS_SUCCESS, $log->status);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('metro-po.pdf', $log->reference);
        $this->assertSame('gemini-test-001', $log->model);
        $this->assertSame(1200, $log->input_tokens);
        $this->assertSame(350, $log->output_tokens);
        $this->assertSame(1550, $log->total_tokens);
    }

    public function test_scanning_without_a_format_uses_the_general_rules(): void
    {
        $this->actingAsUser();
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => '', 'unit_price' => null, 'amount' => null],
        ], 'NO-SUCH-STORE'));

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->assertSet('format_id', '')
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertEmitted('poScanned');

        $rows = Session::get('so_template_rows');
        $this->assertSame('both_not_found', $rows[0]['lookup_status']);
        $this->assertSame('CS', $rows[0]['so_uom']);

        Http::assertSent(fn (Request $request): bool => str_contains(data_get($request->data(), 'contents.0.parts.1.text'), 'Account notes: none'));
    }

    // -------------------------------------------------------------------------
    // Token limit
    // -------------------------------------------------------------------------

    public function test_scanning_stops_once_the_monthly_token_limit_is_reached(): void
    {
        $this->actingAsUser();
        $this->seedSettings();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => 1000]);
        AiUsageLog::factory()->create(['total_tokens' => 1000]);
        Http::fake();

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSee('token limit has been reached')
            ->assertNotEmitted('poScanned');

        Http::assertNothingSent();
        $this->assertCount(0, $this->usageLogs());
    }

    public function test_tokens_from_an_earlier_month_do_not_count_against_the_limit(): void
    {
        $this->actingAsUser();
        $this->seedSettings();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => 1000]);
        AiUsageLog::factory()->create(['total_tokens' => 50000, 'created_at' => now()->subMonthNoOverflow()->startOfMonth()]);
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
        ]));

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertEmitted('poScanned');
    }

    public function test_scanning_stops_once_the_users_daily_token_limit_is_reached(): void
    {
        $user = $this->actingAsUser();
        $this->seedSettings();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => null, 'ai_daily_user_token_limit' => 1000]);
        AiUsageLog::factory()->create(['user_id' => $user->id, 'total_tokens' => 1000]);
        Http::fake();

        Livewire::test(PoScan::class)
            ->assertSee('Your tokens today: 1,000 / 1,000')
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSee('You have used your AI token limit for today (1,000 tokens).')
            ->assertNotEmitted('poScanned');

        Http::assertNothingSent();
    }

    public function test_other_users_and_yesterdays_tokens_do_not_count_against_the_daily_limit(): void
    {
        $user = $this->actingAsUser();
        $this->seedSettings();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => null, 'ai_daily_user_token_limit' => 1000]);
        AiUsageLog::factory()->create(['user_id' => User::factory()->create()->id, 'total_tokens' => 5000]);
        AiUsageLog::factory()->create(['user_id' => $user->id, 'total_tokens' => 5000, 'created_at' => now()->subDay()]);
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
        ]));

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertEmitted('poScanned');
    }

    // -------------------------------------------------------------------------
    // Saved scans
    // -------------------------------------------------------------------------

    private function scanAgainWith(bool $readAgain = false): void
    {
        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->set('readAgain', $readAgain)
            ->call('scan')
            ->assertEmitted('poScanned')
            ->assertSet('readAgain', false);
    }

    public function test_the_same_pdf_is_read_once_and_then_reused_without_tokens(): void
    {
        config(['ai.po_scan.cache_days' => 30]);
        $this->actingAsUser();
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
        ]));

        $this->scanAgainWith();
        $firstRows = Session::get('so_template_rows');
        $this->scanAgainWith();

        Http::assertSentCount(1);
        $this->assertSame($firstRows, Session::get('so_template_rows'));

        $logs = $this->usageLogs()->sortBy('id')->values();
        $this->assertSame([AiUsageLog::STATUS_SUCCESS, AiUsageLog::STATUS_CACHED], $logs->pluck('status')->all());
        $this->assertSame(0, $logs[1]->total_tokens);
        $this->assertSame('gemini-test-001', $logs[1]->model);
    }

    public function test_read_again_skips_the_saved_scan(): void
    {
        config(['ai.po_scan.cache_days' => 30]);
        $this->actingAsUser();
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
        ]));

        $this->scanAgainWith();
        $this->scanAgainWith(readAgain: true);

        Http::assertSentCount(2);
        $this->assertSame([AiUsageLog::STATUS_SUCCESS, AiUsageLog::STATUS_SUCCESS], $this->usageLogs()->sortBy('id')->pluck('status')->all());
    }

    public function test_editing_the_format_reads_the_pdf_again(): void
    {
        config(['ai.po_scan.cache_days' => 30]);
        $user = $this->actingAsUser();
        $format = PoScanFormat::factory()->create(['account_id' => $this->account->id]);
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
        ]));

        $scanner = app(PoScanner::class);
        $scanner->scan(self::PDF_CONTENT, 'po.pdf', $this->account, $format, $user);
        $scanner->scan(self::PDF_CONTENT, 'po.pdf', $this->account, $format, $user);
        Http::assertSentCount(1);

        $this->travel(5)->seconds();
        $format->update(['instructions' => 'Quantities are in pieces.']);
        $scanner->scan(self::PDF_CONTENT, 'po.pdf', $this->account, $format->fresh(), $user);

        Http::assertSentCount(2);
    }

    public function test_a_saved_scan_is_reused_even_after_the_limit_is_reached(): void
    {
        config(['ai.po_scan.cache_days' => 30]);
        $this->actingAsUser();
        $this->seedSettings();
        $this->fakeGemini($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
        ]));

        $this->scanAgainWith();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => 1]);
        $this->scanAgainWith();

        Http::assertSentCount(1);
    }

    public function test_a_read_that_failed_is_not_saved(): void
    {
        config(['ai.po_scan.cache_days' => 30]);
        $user = $this->actingAsUser();
        $this->fakeGemini($this->purchaseOrder([]));
        $scanner = app(PoScanner::class);

        foreach ([1, 2] as $attempt) {
            try {
                $scanner->scan(self::PDF_CONTENT, 'po.pdf', $this->account, null, $user);
                $this->fail('No exception was thrown.');
            } catch (AiUnavailableException $e) {
                $this->assertStringStartsWith('No PO lines were found', $e->getMessage());
            }
        }

        Http::assertSentCount(2);
    }

    // -------------------------------------------------------------------------
    // Failures
    // -------------------------------------------------------------------------

    public function test_scanning_without_an_api_key_explains_how_to_set_it_up(): void
    {
        $this->actingAsUser();
        config(['ai.providers.gemini.key' => null]);
        Http::fake();

        Livewire::test(PoScan::class)
            ->assertSee('AI is not set up yet')
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSet('scanErrors', ['metro-po.pdf' => 'AI is not set up yet. Add GEMINI_API_KEY to the .env file.']);

        Http::assertNothingSent();
        $this->assertCount(0, $this->usageLogs());
    }

    public function test_a_provider_error_is_shown_and_logged_as_a_failed_call(): void
    {
        $this->actingAsUser();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'down']], 503)]);

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSee('The AI service is having problems')
            ->assertNotEmitted('poScanned');

        $log = $this->usageLogs()->sole();
        $this->assertSame(AiUsageLog::STATUS_FAILED, $log->status);
        $this->assertSame('gemini-test', $log->model);
        $this->assertSame(0, $log->total_tokens);
    }

    public function test_a_reply_cut_off_at_the_token_cap_asks_for_a_smaller_pdf_and_still_logs_its_tokens(): void
    {
        $this->actingAsUser();
        $this->fakeGemini($this->purchaseOrder([]), 'MAX_TOKENS');

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSee('too many lines to read in one go');

        $log = $this->usageLogs()->sole();
        $this->assertSame(AiUsageLog::STATUS_FAILED, $log->status);
        $this->assertSame(1550, $log->total_tokens);
    }

    public function test_a_pdf_with_no_lines_is_reported(): void
    {
        $this->actingAsUser();
        $this->fakeGemini(['purchase_orders' => []]);

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSee('No PO lines were found in this PDF');

        $this->assertSame(AiUsageLog::STATUS_FAILED, $this->usageLogs()->sole()->status);
    }

    public function test_when_gemini_fails_claude_reads_the_pdf_and_both_calls_are_logged(): void
    {
        $this->actingAsUser();
        $this->configureAnthropic();
        config(['ai.failover' => 'claude']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'overloaded']], 503)]);
        $this->fakeAnthropic([$this->claudeReply($this->purchaseOrder([
            ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 4, 'uom' => 'PCS', 'unit_price' => null, 'amount' => null],
        ]))]);

        Livewire::test(PoScan::class)
            ->assertSee('Gemini · gemini-test')
            ->assertSee('Claude · claude-test')
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf()])
            ->call('scan')
            ->assertSet('scanErrors', [])
            ->assertEmitted('poScanned');

        $this->assertSame('PCS', Session::get('so_template_rows')[0]['so_uom']);

        $claudeContent = $this->anthropicRequestBody()['messages'][0]['content'];
        $this->assertSame(base64_encode(self::PDF_CONTENT), $claudeContent[0]['source']['data']);
        $this->assertStringContainsString('[MET001] METRO SUPERMARKET', $claudeContent[1]['text']);

        $logs = $this->usageLogs()->sortBy('id')->values();
        $this->assertSame(['gemini', 'anthropic'], $logs->pluck('provider')->all());
        $this->assertSame([AiUsageLog::STATUS_FAILED, AiUsageLog::STATUS_SUCCESS], $logs->pluck('status')->all());
        $this->assertSame('The AI service is having problems. Try again in a minute.', $logs[0]->error);
        $this->assertSame(0, $logs[0]->total_tokens);
        $this->assertSame('claude-test-20261001', $logs[1]->model);
        $this->assertSame(1150, $logs[1]->total_tokens);
    }

    public function test_a_failover_without_a_key_is_shown_as_skipped(): void
    {
        $this->actingAsUser();
        config(['ai.failover' => 'claude', 'ai.providers.anthropic.model' => 'claude-haiku-5-5']);

        Livewire::test(PoScan::class)
            ->assertSee('Claude · claude-haiku-5-5')
            ->assertSee('(no key, skipped)');
    }

    public function test_one_failed_pdf_does_not_lose_the_others(): void
    {
        $this->actingAsUser();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push($this->geminiReply($this->purchaseOrder([
                ['sku' => 'X1', 'barcode' => '', 'description' => 'ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => null, 'amount' => null],
            ])))
            ->push(['error' => ['message' => 'quota']], 429)
            ->push(['error' => ['message' => 'quota']], 429),
        ]);

        Livewire::test(PoScan::class)
            ->set('account_id', (string) $this->account->id)
            ->set('files', [$this->pdf('first.pdf'), $this->pdf('second.pdf')])
            ->call('scan')
            ->assertEmitted('poScanned', [
                'total' => 1, 'ok' => 0, 'sku_not_found' => 0, 'address_not_found' => 0, 'both_not_found' => 1,
            ], ['second.pdf' => 'The AI service is busy or its quota is used up. Try again later.']);

        $this->assertSame(['first.pdf'], array_values(array_unique(array_column(Session::get('so_template_rows'), 'source_file'))));
        $this->assertSame([AiUsageLog::STATUS_SUCCESS, AiUsageLog::STATUS_FAILED], $this->usageLogs()->sortBy('id')->pluck('status')->all());
    }

    // -------------------------------------------------------------------------
    // Review screen and export
    // -------------------------------------------------------------------------

    public function test_the_review_screen_shows_scanned_rows_and_warnings(): void
    {
        $this->actingAsUser();
        Session::put('so_template_rows', [$this->scannedRow()]);

        Livewire::test(Index::class)
            ->call('showScannedRows', ['total' => 1, 'ok' => 1, 'sku_not_found' => 0, 'address_not_found' => 0, 'both_not_found' => 0], ['bad.pdf' => 'Could not read.'])
            ->assertSet('step', 2)
            ->assertSet('activeTab', 'upload')
            ->assertSee('Read by AI from')
            ->assertSee('metro-po.pdf')
            ->assertSee('bad.pdf')
            ->assertSee('Could not read.')
            ->call('resetUpload')
            ->assertSet('scanWarnings', []);
    }

    public function test_the_upload_template_export_uses_the_scanned_unit(): void
    {
        $this->actingAsUser();
        Session::put('so_template_rows', [
            $this->scannedRow(['so_uom' => 'PCS']),
            array_diff_key($this->scannedRow(['po_number' => 'CSV-1']), ['so_uom' => true, 'source_file' => true]),
        ]);

        $component = Livewire::test(Index::class)->call('exportTemplate')->assertFileDownloaded();

        $path = tempnam(sys_get_temp_dir(), 'so_test_') . '.xlsx';
        file_put_contents($path, base64_decode(data_get($component->lastResponse, 'original.effects.download.content')));
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();

        $this->assertSame('PO-55501', $sheet->getCell('A2')->getValue());
        $this->assertSame('PCS', $sheet->getCell('F2')->getValue());
        $this->assertSame('CSV-1', $sheet->getCell('A3')->getValue());
        $this->assertSame('CS', $sheet->getCell('F3')->getValue(), 'CSV rows keep the CS default.');

        $sheet->getParent()->disconnectWorksheets();
        @unlink($path);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function scannedRow(array $overrides = []): array
    {
        return array_merge([
            'po_date' => '2026-10-12', 'po_number' => 'PO-55501', 'store_name' => 'METRO CEBU', 'store_code' => 'STORE-7',
            'delivery_date' => '2026-10-20', 'cancellation_date' => '', 'depot' => '', 'del_loc' => '', 'po_remarks' => '',
            'raw_sku' => 'KS1001', 'sku_code' => 'KS1001', 'description' => 'SOAP', 'uom' => 'PCS', 'qty' => '12',
            'list_price' => '10', 'amount' => '120', 'product_id' => 1, 'internal_sku' => 'KS1001', 'product_name' => 'SOAP',
            'account_id' => $this->account->id, 'shipping_address_id' => 1, 'shipping_name' => 'METRO CEBU', 'shipping_address' => 'Cebu',
            'lookup_status' => 'ok', 'account_code' => 'MET001', 'account_name' => 'METRO SUPERMARKET',
            'so_uom' => 'PCS', 'source_file' => 'metro-po.pdf',
        ], $overrides);
    }
}
