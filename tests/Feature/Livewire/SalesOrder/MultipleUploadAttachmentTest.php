<?php

namespace Tests\Feature\Livewire\SalesOrder;

use App\Http\Livewire\SalesOrder\Multiple\Upload;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Brand;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesGemini;
use Tests\TestCase;

/**
 * Upload Multiple SO with PO PDFs read by AI, and a PO attachment on each sales order card.
 */
class MultipleUploadAttachmentTest extends TestCase
{
    use DatabaseTransactions;
    use FakesGemini;

    private const PDF_CONTENT = "%PDF-1.4\n% fake purchase order\n";

    private Account $account;
    private AccountLogin $accountLogin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('activitylog.enabled', false);
        Config::set('sales-order.xml_generation_enabled', false);
        Storage::fake('local');
        Storage::fake('tmp-for-tests');
        Queue::fake();
        $this->configureGemini();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = Company::factory()->create();

        $this->account = Account::factory()->create([
            'company_id'         => $company->id,
            'price_code'         => 'A',
            'line_discount_code' => null,
            'sales_order_uom'    => null,
            'discount_id'        => null,
            'po_process_date'    => null,
            'po_prefix'          => 'PH-',
        ]);

        $this->user = $this->userWith(['po scan access']);

        $this->accountLogin = AccountLogin::factory()->create([
            'user_id'    => $this->user->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);
        $this->accountLogin->load('account');

        $product = Product::factory()->create([
            'stock_code' => 'KS-MULTI-01',
            'brand_id'   => Brand::factory()->create(['brand' => 'TEST BRAND'])->id,
            'stock_uom'  => 'CS',
            'order_uom'  => 'IN',
            'other_uom'  => 'PCS',
        ]);

        PriceCode::factory()->create([
            'company_id'    => $company->id,
            'product_id'    => $product->id,
            'code'          => 'A',
            'selling_price' => 100,
            'price_basis'   => 'S',
        ]);
    }

    /**
     * @param array<int, string> $permissions
     */
    private function userWith(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, self::PDF_CONTENT);
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseOrder(string $poNumber, ?string $deliveryDate = 'default', ?float $amount = 500.0): array
    {
        return [
            'po_number'         => $poNumber,
            'po_date'           => now()->format('Y-m-d'),
            'delivery_date'     => $deliveryDate === 'default' ? now()->addWeekdays(3)->format('Y-m-d') : (string) $deliveryDate,
            'cancellation_date' => '',
            'store_code'        => '',
            'store_name'        => '',
            'delivery_location' => '',
            'remarks'           => 'Gate 2',
            'lines'             => [
                ['sku' => 'KS-MULTI-01', 'barcode' => '', 'description' => 'KOJIESAN SOAP', 'quantity' => 5, 'uom' => 'CS', 'unit_price' => 100, 'amount' => $amount],
            ],
        ];
    }

    /**
     * Each call to the AI answers with the next reply, one per PDF.
     *
     * @param array<int, array<int, array<string, mixed>>> $repliesPerPdf
     */
    private function fakeGemini(array ...$repliesPerPdf): void
    {
        $sequence = Http::sequence();

        foreach ($repliesPerPdf as $orders) {
            $sequence->push($this->geminiReply(['purchase_orders' => $orders]));
        }

        Http::fake(['generativelanguage.googleapis.com/*' => $sequence]);
    }

    private function uploader(): TestableLivewire
    {
        return Livewire::test(Upload::class, ['logged_account' => $this->accountLogin]);
    }

    public function test_each_po_in_the_pdfs_becomes_a_card_with_its_pdf_attached(): void
    {
        $this->fakeGemini(
            [$this->purchaseOrder('PO-A1'), $this->purchaseOrder('PO-A2')],
            [$this->purchaseOrder('PO-B1')],
        );

        $component = $this->uploader()
            ->set('so_files', [$this->pdf('store-a.pdf'), $this->pdf('store-b.pdf')])
            ->call('checkFileData')
            ->assertHasNoErrors();

        $soData = $component->get('so_data');
        $this->assertSame(['PH-PO-A1', 'PH-PO-A2', 'PH-PO-B1'], array_keys($soData));
        $this->assertEquals(500, $soData['PH-PO-A1']['po_value']);
        $this->assertSame(now()->addWeekdays(3)->format('Y-m-d'), $soData['PH-PO-A1']['ship_date']);
        $this->assertSame('Gate 2', $soData['PH-PO-A1']['shipping_instruction']);
        $this->assertCount(1, $soData['PH-PO-A1']['lines']);

        $component->assertSee('store-a.pdf')->assertSee('store-b.pdf')->assertSee('scanned PO');
    }

    public function test_saving_a_scanned_order_attaches_its_pdf(): void
    {
        $this->fakeGemini([$this->purchaseOrder('PO-SAVE-1')]);

        $this->uploader()
            ->set('so_files', [$this->pdf('scanned.pdf')])
            ->call('checkFileData')
            ->call('saveSalesOrder', 'draft', 'PH-PO-SAVE-1')
            ->assertSet('err_data', null);

        $attachment = SalesOrder::where('po_number', 'PH-PO-SAVE-1')->firstOrFail()->attachment;
        $this->assertSame('scanned.pdf', $attachment->file_name);
        $this->assertSame(SalesOrderAttachment::SOURCE_SCAN, $attachment->source);
        $this->assertSame(self::PDF_CONTENT, Storage::disk('local')->get($attachment->file_path));
    }

    public function test_two_orders_from_one_pdf_each_keep_their_own_copy(): void
    {
        $this->fakeGemini([$this->purchaseOrder('PO-C1'), $this->purchaseOrder('PO-C2')]);

        $this->uploader()
            ->set('so_files', [$this->pdf('combined.pdf')])
            ->call('checkFileData')
            ->call('saveAll', 'draft');

        $first  = SalesOrder::where('po_number', 'PH-PO-C1')->firstOrFail()->attachment;
        $second = SalesOrder::where('po_number', 'PH-PO-C2')->firstOrFail()->attachment;

        $this->assertNotSame($first->file_path, $second->file_path);
        $this->assertTrue(Storage::disk('local')->exists($first->file_path));
        $this->assertTrue(Storage::disk('local')->exists($second->file_path));
    }

    public function test_a_card_attachment_can_be_chosen_and_is_saved(): void
    {
        $component = $this->uploader()->set('so_data', ['PH-MANUAL-1' => $this->soDataEntry('so0')]);

        $component->set('attachments.so0', UploadedFile::fake()->createWithContent('customer-po.pdf', self::PDF_CONTENT))
            ->assertHasNoErrors()
            ->call('saveSalesOrder', 'draft', 'PH-MANUAL-1');

        $attachment = SalesOrder::where('po_number', 'PH-MANUAL-1')->firstOrFail()->attachment;
        $this->assertSame('customer-po.pdf', $attachment->file_name);
        $this->assertSame(SalesOrderAttachment::SOURCE_UPLOAD, $attachment->source);
    }

    public function test_a_card_attachment_is_optional(): void
    {
        $this->uploader()
            ->set('so_data', ['PH-NOFILE-1' => $this->soDataEntry('so0')])
            ->call('saveSalesOrder', 'draft', 'PH-NOFILE-1');

        $this->assertNull(SalesOrder::where('po_number', 'PH-NOFILE-1')->firstOrFail()->attachment);
    }

    public function test_a_card_attachment_of_the_wrong_type_is_refused(): void
    {
        $this->uploader()
            ->set('so_data', ['PH-BAD-1' => $this->soDataEntry('so0')])
            ->set('attachments.so0', UploadedFile::fake()->createWithContent('run.php', '<?php echo 1;'))
            ->assertHasErrors('attachments.so0')
            ->assertSet('attachments', []);
    }

    public function test_a_card_attachment_can_be_removed_before_saving(): void
    {
        $this->fakeGemini([$this->purchaseOrder('PO-RM-1')]);

        $this->uploader()
            ->set('so_files', [$this->pdf('scanned.pdf')])
            ->call('checkFileData')
            ->call('removeAttachment', 'so0')
            ->assertSet('attachments', [])
            ->call('saveSalesOrder', 'draft', 'PH-PO-RM-1');

        $this->assertNull(SalesOrder::where('po_number', 'PH-PO-RM-1')->firstOrFail()->attachment);
    }

    public function test_a_missing_ship_date_and_po_value_can_be_filled_in(): void
    {
        $this->fakeGemini([$this->purchaseOrder('PO-FILL-1', deliveryDate: '', amount: null)]);

        $shipDate = now()->addWeekdays(4)->format('Y-m-d');

        $component = $this->uploader()
            ->set('so_files', [$this->pdf('no-dates.pdf')])
            ->call('checkFileData')
            ->call('saveSalesOrder', 'draft', 'PH-PO-FILL-1')
            ->assertSet('err_data.PH-PO-FILL-1.ship_date', 'Ship date is required.')
            ->assertSet('err_data.PH-PO-FILL-1.po_value', 'PO value is required')
            ->call('updateOrderField', 'so0', 'ship_date', $shipDate)
            ->call('updateOrderField', 'so0', 'po_value', '1,250.50')
            ->assertSet('so_data.PH-PO-FILL-1.ship_date', $shipDate)
            ->assertSet('so_data.PH-PO-FILL-1.po_value', 1250.5)
            ->call('saveSalesOrder', 'draft', 'PH-PO-FILL-1');

        $salesOrder = SalesOrder::where('po_number', 'PH-PO-FILL-1')->firstOrFail();
        $this->assertSame($shipDate, $salesOrder->ship_date);
        $this->assertEquals(1250.5, $salesOrder->po_value);
    }

    public function test_a_saved_order_cannot_be_changed_afterwards(): void
    {
        $this->fakeGemini([$this->purchaseOrder('PO-LOCK-1')]);

        $this->uploader()
            ->set('so_files', [$this->pdf('scanned.pdf')])
            ->call('checkFileData')
            ->call('saveSalesOrder', 'draft', 'PH-PO-LOCK-1')
            ->call('updateOrderField', 'so0', 'po_value', '9')
            ->assertSet('so_data.PH-PO-LOCK-1.po_value', 500.0);
    }

    public function test_unmatched_lines_are_listed_as_warnings(): void
    {
        $order = $this->purchaseOrder('PO-WARN-1');
        $order['lines'][] = ['sku' => 'NOPE-1', 'barcode' => '', 'description' => 'MYSTERY', 'quantity' => 1, 'uom' => 'CS', 'unit_price' => 1, 'amount' => 1];
        $this->fakeGemini([$order]);

        $warnings = $this->uploader()
            ->set('so_files', [$this->pdf('scanned.pdf')])
            ->call('checkFileData')
            ->get('so_data')['PH-PO-WARN-1']['warnings'];

        $this->assertSame(['NOPE-1'], array_column($warnings, 'stock_code'));
    }

    public function test_a_pdf_the_ai_cannot_read_is_reported_and_the_others_load(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push(['error' => ['message' => 'bad request']], 400)
            ->push($this->geminiReply(['purchase_orders' => [$this->purchaseOrder('PO-OK-1')]])),
        ]);

        $component = $this->uploader()
            ->set('so_files', [$this->pdf('broken.pdf'), $this->pdf('good.pdf')])
            ->call('checkFileData');

        $this->assertArrayHasKey('broken.pdf', $component->get('scanErrors'));
        $this->assertSame(['PH-PO-OK-1'], array_keys($component->get('so_data')));
        $component->assertSee('broken.pdf');
    }

    public function test_the_same_po_in_two_pdfs_is_loaded_once(): void
    {
        $this->fakeGemini([$this->purchaseOrder('PO-DUP-1')], [$this->purchaseOrder('PO-DUP-1')]);

        $component = $this->uploader()
            ->set('so_files', [$this->pdf('first.pdf'), $this->pdf('second.pdf')])
            ->call('checkFileData');

        $this->assertSame(['PH-PO-DUP-1'], array_keys($component->get('so_data')));
        $this->assertStringContainsString('first.pdf', $component->get('scanErrors')['second.pdf']);
    }

    public function test_pdfs_need_the_po_scan_permission(): void
    {
        Http::fake();
        $this->userWith([]);
        $this->accountLogin->update(['user_id' => auth()->id()]);

        $this->uploader()
            ->set('so_files', [$this->pdf('scanned.pdf')])
            ->call('checkFileData')
            ->assertHasErrors('so_files')
            ->assertSet('so_data', null);

        Http::assertNothingSent();
    }

    public function test_excel_and_pdfs_cannot_be_mixed(): void
    {
        Http::fake();

        $this->uploader()
            ->set('so_files', [
                UploadedFile::fake()->create('orders.xlsx', 5, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
                $this->pdf('scanned.pdf'),
            ])
            ->call('checkFileData')
            ->assertHasErrors('so_files');

        Http::assertNothingSent();
    }

    public function test_other_file_types_are_refused(): void
    {
        $this->uploader()
            ->set('so_files', [UploadedFile::fake()->createWithContent('notes.txt', 'hello')])
            ->call('checkFileData')
            ->assertHasErrors('so_files.0');
    }

    /**
     * @return array<string, mixed> one so_data entry as buildSoData() makes it
     */
    private function soDataEntry(string $key): array
    {
        $product = Product::where('stock_code', 'KS-MULTI-01')->firstOrFail();

        return [
            'key'                  => $key,
            'scanned_from'         => null,
            'ship_to_address'      => '',
            'shipping_address'     => [],
            'ship_date'            => now()->addWeekdays(3)->format('Y-m-d'),
            'ship_date_error'      => null,
            'po_value'             => 500,
            'paf_number'           => '',
            'shipping_instruction' => '',
            'discount'             => null,
            'lines'                => [[
                'sku_code'            => $product->stock_code,
                'product'             => $product->toArray(),
                'uom'                 => 'CS',
                'quantity'            => 5,
                'total'               => 500,
                'total_less_discount' => 500,
                'line_discount'       => '0',
            ]],
            'warnings'             => [],
            'calculated_data'      => [],
            'service_items_input'  => [
                $product->id => ['product' => $product->toArray(), 'data' => ['CS' => ['quantity' => 5, 'paf_rows' => []]]],
            ],
        ];
    }
}
