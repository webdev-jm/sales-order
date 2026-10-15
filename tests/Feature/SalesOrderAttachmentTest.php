<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\AiUsageLog;
use App\Models\Brand;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderAttachment;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Services\SalesOrderAttachmentService;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesGemini;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * The optional PO attachment on the single sales order form, and filling that form from a
 * PO PDF read with AI.
 */
class SalesOrderAttachmentTest extends TestCase
{
    use DatabaseTransactions;
    use FakesGemini;
    use SeedsReferenceData;

    private const PDF_CONTENT = "%PDF-1.4\n% fake purchase order\n";

    private const ORDER_PERMISSIONS = ['sales order access', 'sales order create', 'sales order edit'];

    private Account $account;
    private AccountLogin $accountLogin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedSettings();
        Config::set('activitylog.enabled', false);
        Config::set('sales-order.xml_generation_enabled', false);
        Storage::fake('local');
        Queue::fake();
        $this->configureGemini();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = Company::factory()->create();

        $this->account = Account::factory()->create([
            'company_id'         => $company->id,
            'account_code'       => 'MET001',
            'account_name'       => 'METRO SUPERMARKET',
            'price_code'         => 'A',
            'line_discount_code' => null,
            'sales_order_uom'    => null,
            'discount_id'        => null,
            'po_process_date'    => null,
            'po_prefix'          => null,
        ]);

        $this->user = $this->userWith([...self::ORDER_PERMISSIONS, 'po scan access']);
        $this->user->accounts()->attach($this->account->id);

        $this->accountLogin = AccountLogin::factory()->create([
            'user_id'    => $this->user->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);

        $product = Product::factory()->create([
            'stock_code' => 'KS-ATT-01',
            'brand_id'   => Brand::factory()->create(['brand' => 'TEST BRAND'])->id,
            'stock_uom'  => 'CS',
            'order_uom'  => 'IN',
            'other_uom'  => 'PCS',
            'status'     => '',
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

        return $user;
    }

    private function disk(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk('local');
    }

    private function pdf(string $name = 'metro-po.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, self::PDF_CONTENT);
    }

    /**
     * @return array<string, mixed>
     */
    private function storePayload(array $extra = []): array
    {
        return $extra + [
            'status'       => 'draft',
            'po_number'    => 'PO-ATT-001',
            'order_date'   => now()->format('Y-m-d'),
            'ship_date'    => now()->addWeekdays(2)->format('Y-m-d'),
            'ship_to_name' => 'Test Customer',
        ];
    }

    /**
     * Posts the order form with the order service mocked, so only the attachment handling runs.
     *
     * @param array<string, mixed> $extra
     * @param array<string, mixed> $session
     */
    private function storeOrder(array $extra = [], array $session = []): array
    {
        $salesOrder = SalesOrder::factory()->create(['account_login_id' => $this->accountLogin->id, 'status' => 'draft']);

        $this->mock(SalesOrderService::class)->shouldReceive('createOrder')->once()->andReturn($salesOrder);

        $response = $this->actingAs($this->user)
            ->withSession($session + ['order_data' => ['items' => [1], 'po_value' => 1000]])
            ->post('/sales-order-store', $this->storePayload($extra));

        return [$response, $salesOrder];
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseOrder(string $poNumber = 'PO-55501', string $storeCode = 'STORE-7', ?float $amount = 500.0): array
    {
        return [
            'po_number'         => $poNumber,
            'po_date'           => now()->format('Y-m-d'),
            'delivery_date'     => now()->addWeekdays(3)->format('m/d/Y'),
            'cancellation_date' => '',
            'store_code'        => $storeCode,
            'store_name'        => 'METRO CEBU',
            'delivery_location' => '',
            'remarks'           => 'Deliver before 10AM',
            'lines'             => [
                ['sku' => 'KS-ATT-01', 'barcode' => '', 'description' => 'KOJIESAN SOAP', 'quantity' => 5, 'uom' => 'CS', 'unit_price' => 100, 'amount' => $amount],
                ['sku' => 'UNKNOWN-9', 'barcode' => '', 'description' => 'MYSTERY ITEM', 'quantity' => 2, 'uom' => 'CS', 'unit_price' => 10, 'amount' => 20],
            ],
        ];
    }

    private function fakeGemini(array ...$orders): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['purchase_orders' => $orders]))]);
    }

    // -------------------------------------------------------------------------
    // Attaching on the order form
    // -------------------------------------------------------------------------

    public function test_the_attachment_is_optional(): void
    {
        [$response, $salesOrder] = $this->storeOrder();

        $response->assertRedirect(route('sales-order.index'))->assertSessionHasNoErrors();
        $this->assertNull($salesOrder->fresh()->attachment);
    }

    public function test_a_chosen_file_is_attached_to_the_new_order(): void
    {
        [$response, $salesOrder] = $this->storeOrder(['po_attachment' => $this->pdf('customer-po.pdf')]);

        $response->assertRedirect(route('sales-order.index'));

        $attachment = $salesOrder->fresh()->attachment;
        $this->assertSame('customer-po.pdf', $attachment->file_name);
        $this->assertSame(SalesOrderAttachment::SOURCE_UPLOAD, $attachment->source);
        $this->assertSame($this->user->id, $attachment->user_id);
        $this->assertStringStartsWith('sales-order-attachments/' . $salesOrder->id . '/', $attachment->file_path);
        $this->assertSame(self::PDF_CONTENT, $this->disk()->get($attachment->file_path));
    }

    public function test_a_disallowed_file_type_is_rejected(): void
    {
        $salesOrder = SalesOrder::factory()->create(['account_login_id' => $this->accountLogin->id]);
        $this->mock(SalesOrderService::class)->shouldNotReceive('createOrder');

        $this->actingAs($this->user)
            ->withSession(['order_data' => ['items' => [1], 'po_value' => 1000]])
            ->post('/sales-order-store', $this->storePayload(['po_attachment' => UploadedFile::fake()->createWithContent('run.php', '<?php echo 1;')]))
            ->assertSessionHasErrors('po_attachment');

        $this->assertNull($salesOrder->fresh()->attachment);
    }

    public function test_an_oversized_file_is_rejected(): void
    {
        Config::set('sales-order.attachments.max_kb', 1);
        $this->mock(SalesOrderService::class)->shouldNotReceive('createOrder');

        $this->actingAs($this->user)
            ->withSession(['order_data' => ['items' => [1], 'po_value' => 1000]])
            ->post('/sales-order-store', $this->storePayload(['po_attachment' => UploadedFile::fake()->create('big.pdf', 50, 'application/pdf')]))
            ->assertSessionHasErrors('po_attachment');
    }

    public function test_a_held_scanned_pdf_is_moved_onto_the_new_order(): void
    {
        $this->disk()->put('sales-order-attachments/pending/held.pdf', self::PDF_CONTENT);

        [, $salesOrder] = $this->storeOrder([], [SalesOrderAttachmentService::PENDING_SESSION_KEY => [
            'path' => 'sales-order-attachments/pending/held.pdf', 'name' => 'metro-po.pdf', 'mime' => 'application/pdf',
            'size' => 30, 'source' => SalesOrderAttachment::SOURCE_SCAN, 'temporary' => true,
        ]]);

        $attachment = $salesOrder->fresh()->attachment;
        $this->assertSame('metro-po.pdf', $attachment->file_name);
        $this->assertSame(SalesOrderAttachment::SOURCE_SCAN, $attachment->source);
        $this->assertTrue($this->disk()->exists($attachment->file_path));
        $this->assertFalse($this->disk()->exists('sales-order-attachments/pending/held.pdf'));
        $this->assertNull(session(SalesOrderAttachmentService::PENDING_SESSION_KEY));
    }

    public function test_a_removed_held_pdf_is_discarded(): void
    {
        $this->disk()->put('sales-order-attachments/pending/held.pdf', self::PDF_CONTENT);

        [, $salesOrder] = $this->storeOrder(['discard_pending_attachment' => 1], [SalesOrderAttachmentService::PENDING_SESSION_KEY => [
            'path' => 'sales-order-attachments/pending/held.pdf', 'name' => 'metro-po.pdf', 'mime' => 'application/pdf',
            'size' => 30, 'source' => SalesOrderAttachment::SOURCE_SCAN, 'temporary' => true,
        ]]);

        $this->assertNull($salesOrder->fresh()->attachment);
        $this->assertFalse($this->disk()->exists('sales-order-attachments/pending/held.pdf'));
    }

    public function test_a_chosen_file_wins_over_a_held_pdf(): void
    {
        $this->disk()->put('sales-order-attachments/pending/held.pdf', self::PDF_CONTENT);

        [, $salesOrder] = $this->storeOrder(['po_attachment' => $this->pdf('signed-po.pdf')], [SalesOrderAttachmentService::PENDING_SESSION_KEY => [
            'path' => 'sales-order-attachments/pending/held.pdf', 'name' => 'metro-po.pdf', 'mime' => 'application/pdf',
            'size' => 30, 'source' => SalesOrderAttachment::SOURCE_SCAN, 'temporary' => true,
        ]]);

        $this->assertSame('signed-po.pdf', $salesOrder->fresh()->attachment->file_name);
        $this->assertSame(1, SalesOrderAttachment::where('sales_order_id', $salesOrder->id)->count());
        $this->assertFalse($this->disk()->exists('sales-order-attachments/pending/held.pdf'));
    }

    // -------------------------------------------------------------------------
    // Editing, resubmitting, viewing
    // -------------------------------------------------------------------------

    private function orderWithAttachment(): SalesOrder
    {
        $salesOrder = SalesOrder::factory()->create([
            'account_login_id' => $this->accountLogin->id,
            'status'           => 'draft',
            'po_number'        => 'PO-EDIT-' . uniqid(),
        ]);

        $this->actingAs($this->user);
        app(SalesOrderAttachmentService::class)->attach($salesOrder, $this->pdf('first-po.pdf'));

        return $salesOrder;
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function updateOrder(SalesOrder $salesOrder, array $extra): void
    {
        $this->mock(SalesOrderService::class)->shouldReceive('updateOrder')->once()->andReturn($salesOrder);

        $this->actingAs($this->user)
            ->withSession(['order_data' => ['items' => [1], 'po_value' => 1000]])
            ->post('/sales-order/' . $salesOrder->id, $extra + [
                'status'         => 'draft',
                'control_number' => $salesOrder->control_number,
                'po_number'      => $salesOrder->po_number,
                'order_date'     => now()->format('Y-m-d'),
                'ship_date'      => now()->addWeekdays(2)->format('Y-m-d'),
                'ship_to_name'   => 'Test Customer',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_editing_replaces_the_attachment_and_deletes_the_old_file(): void
    {
        $salesOrder = $this->orderWithAttachment();
        $oldPath    = $salesOrder->attachment->file_path;

        $this->updateOrder($salesOrder, ['po_attachment' => $this->pdf('revised-po.pdf')]);

        $this->assertSame('revised-po.pdf', $salesOrder->fresh()->attachment->file_name);
        $this->assertSame(1, SalesOrderAttachment::where('sales_order_id', $salesOrder->id)->count());
        $this->assertFalse($this->disk()->exists($oldPath));
    }

    public function test_editing_can_remove_the_attachment(): void
    {
        $salesOrder = $this->orderWithAttachment();
        $oldPath    = $salesOrder->attachment->file_path;

        $this->updateOrder($salesOrder, ['remove_attachment' => 1]);

        $this->assertNull($salesOrder->fresh()->attachment);
        $this->assertFalse($this->disk()->exists($oldPath));
    }

    public function test_the_edit_form_shows_the_current_attachment(): void
    {
        $this->withoutCatalogProducts();
        $salesOrder = $this->orderWithAttachment();

        $this->actingAs($this->user)->get('/sales-order/' . $salesOrder->id . '/edit')
            ->assertOk()
            ->assertSee('first-po.pdf')
            ->assertSee(route('sales-order.attachment', $salesOrder->id))
            ->assertSee('name="remove_attachment"', false)
            ->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_editing_without_a_file_keeps_the_attachment(): void
    {
        $salesOrder = $this->orderWithAttachment();

        $this->updateOrder($salesOrder, []);

        $this->assertSame('first-po.pdf', $salesOrder->fresh()->attachment->file_name);
    }

    public function test_resubmitting_holds_the_attachment_for_the_new_order(): void
    {
        $this->withoutCatalogProducts();
        $original = $this->orderWithAttachment();

        $this->actingAs($this->user)->get('/sales-order/' . $original->id . '/resubmit')
            ->assertOk()
            ->assertSee('first-po.pdf')
            ->assertSee('from previous order');

        $pending = session(SalesOrderAttachmentService::PENDING_SESSION_KEY);
        $this->assertSame($original->attachment->file_path, $pending['path']);
        $this->assertFalse($pending['temporary']);
    }

    public function test_a_held_attachment_from_a_previous_order_is_copied_not_moved(): void
    {
        $original    = $this->orderWithAttachment();
        $replacement = SalesOrder::factory()->create(['account_login_id' => $this->accountLogin->id]);
        $service     = app(SalesOrderAttachmentService::class);

        $service->holdExisting($original->attachment);
        $service->attachPending($replacement);

        $this->assertSame('first-po.pdf', $replacement->fresh()->attachment->file_name);
        $this->assertTrue($this->disk()->exists($original->fresh()->attachment->file_path));
        $this->assertNotSame($original->fresh()->attachment->file_path, $replacement->fresh()->attachment->file_path);
        $this->assertNull(session(SalesOrderAttachmentService::PENDING_SESSION_KEY));
    }

    public function test_the_attachment_can_be_opened_from_the_order(): void
    {
        $salesOrder = $this->orderWithAttachment();

        $this->actingAs($this->user)->get('/sales-order/' . $salesOrder->id)
            ->assertOk()
            ->assertSee(route('sales-order.attachment', $salesOrder->id))
            ->assertSee('first-po.pdf');

        $response = $this->actingAs($this->user)->get(route('sales-order.attachment', $salesOrder->id));
        $response->assertOk();
        $this->assertSame(self::PDF_CONTENT, $response->streamedContent());
    }

    public function test_opening_a_missing_attachment_is_a_404(): void
    {
        $salesOrder = SalesOrder::factory()->create(['account_login_id' => $this->accountLogin->id]);

        $this->actingAs($this->user)->get(route('sales-order.attachment', $salesOrder->id))->assertNotFound();
    }

    public function test_opening_an_attachment_requires_sales_order_access(): void
    {
        $salesOrder = $this->orderWithAttachment();

        $this->actingAs(User::factory()->create(['status' => 'active']))
            ->get(route('sales-order.attachment', $salesOrder->id))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Filling the order form from a PO PDF
    // -------------------------------------------------------------------------

    public function test_a_po_pdf_fills_the_order_form_and_is_held_as_the_attachment(): void
    {
        $address = ShippingAddress::factory()->create([
            'account_id'   => $this->account->id,
            'address_code' => 'STORE-7',
            'ship_to_name' => 'METRO CEBU',
            'building'     => 'Metro Bldg',
            'street'       => 'Osmena Blvd',
            'city'         => 'Cebu City',
            'postal'       => '6000',
        ]);
        $this->fakeGemini($this->purchaseOrder());

        $this->actingAs($this->user)
            ->post('/sales-order/upload', ['upload_file' => $this->pdf()])
            ->assertRedirect(route('sales-order.create'))
            ->assertSessionHas('po_number', 'PO-55501')
            ->assertSessionHas('ship_date', now()->addWeekdays(3)->format('Y-m-d'))
            ->assertSessionHas('shipping_instruction', 'Deliver before 10AM')
            ->assertSessionHas('shipping_address_id', $address->id)
            ->assertSessionHas('ship_to_name', 'STORE-7 - METRO CEBU')
            ->assertSessionHas('po_value', 520.0);

        $orderData = session('order_data');
        $product   = Product::where('stock_code', 'KS-ATT-01')->first();
        $this->assertSame(5.0, $orderData['items'][$product->id]['data']['CS']['quantity']);
        $this->assertEquals(500, $orderData['total']);

        $warnings = collect(session('scan_warnings'))->pluck('stock_code')->all();
        $this->assertSame(['UNKNOWN-9'], $warnings);

        $pending = session(SalesOrderAttachmentService::PENDING_SESSION_KEY);
        $this->assertSame('metro-po.pdf', $pending['name']);
        $this->assertSame(SalesOrderAttachment::SOURCE_SCAN, $pending['source']);
        $this->assertTrue($this->disk()->exists($pending['path']));

        $this->assertSame(1, AiUsageLog::where('account_id', $this->account->id)->count());
    }

    public function test_the_create_form_shows_the_held_pdf_and_scan_warnings(): void
    {
        $this->withoutCatalogProducts();
        $this->disk()->put('sales-order-attachments/pending/held.pdf', self::PDF_CONTENT);

        $this->actingAs($this->user)
            ->withSession([
                SalesOrderAttachmentService::PENDING_SESSION_KEY => [
                    'path' => 'sales-order-attachments/pending/held.pdf', 'name' => 'metro-po.pdf', 'mime' => 'application/pdf',
                    'size' => 30, 'source' => SalesOrderAttachment::SOURCE_SCAN, 'temporary' => true,
                ],
                'scan_notices'  => ['This PDF holds 2 purchase orders.'],
                'scan_warnings' => [['stock_code' => 'UNKNOWN-9', 'reason' => 'Not found in the product catalog.']],
            ])
            ->get('/sales-order/create')
            ->assertOk()
            ->assertSee('metro-po.pdf')
            ->assertSee('scanned PO')
            ->assertSee('This PDF holds 2 purchase orders.')
            ->assertSee('UNKNOWN-9');
    }

    /**
     * The order form looks every listed product up in the legacy pafs columns, which the
     * migrations do not create, so form-rendering tests run without catalog products.
     */
    private function withoutCatalogProducts(): void
    {
        $product = Product::where('stock_code', 'KS-ATT-01')->first();
        PriceCode::where('product_id', $product->id)->forceDelete();
        $product->forceDelete();
    }

    public function test_a_pdf_with_several_pos_loads_the_first_and_says_so(): void
    {
        $this->fakeGemini($this->purchaseOrder('PO-1'), $this->purchaseOrder('PO-2'));

        $this->actingAs($this->user)
            ->post('/sales-order/upload', ['upload_file' => $this->pdf()])
            ->assertSessionHas('po_number', 'PO-1');

        $this->assertStringContainsString('Upload Multiple SO', implode(' ', session('scan_notices')));
    }

    public function test_a_pdf_without_amounts_asks_for_the_po_value(): void
    {
        $order = $this->purchaseOrder(amount: null);
        $order['lines'] = [$order['lines'][0]];
        $this->fakeGemini($order);

        $this->actingAs($this->user)
            ->post('/sales-order/upload', ['upload_file' => $this->pdf()])
            ->assertSessionHas('po_value', '');

        $this->assertStringContainsString('enter the PO value', implode(' ', session('scan_notices')));
    }

    public function test_scanning_a_pdf_needs_the_po_scan_permission(): void
    {
        Http::fake();
        $user = $this->userWith(self::ORDER_PERMISSIONS);
        $this->accountLogin->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post('/sales-order/upload', ['upload_file' => $this->pdf()])
            ->assertSessionHas('message_error');

        Http::assertNothingSent();
        $this->assertNull(session(SalesOrderAttachmentService::PENDING_SESSION_KEY));
    }

    public function test_an_ai_failure_is_shown_and_nothing_is_held(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'bad key']], 400)]);

        $this->actingAs($this->user)
            ->post('/sales-order/upload', ['upload_file' => $this->pdf()])
            ->assertSessionHas('message_error');

        $this->assertNull(session(SalesOrderAttachmentService::PENDING_SESSION_KEY));
        $this->assertNull(session('order_data'));
    }

    public function test_the_upload_accepts_only_excel_or_pdf(): void
    {
        $this->actingAs($this->user)
            ->post('/sales-order/upload', ['upload_file' => UploadedFile::fake()->createWithContent('notes.txt', 'hello')])
            ->assertSessionHasErrors('upload_file');
    }

    public function test_leaving_the_form_discards_a_held_pdf(): void
    {
        $this->disk()->put('sales-order-attachments/pending/held.pdf', self::PDF_CONTENT);

        $this->actingAs($this->user)
            ->withSession([SalesOrderAttachmentService::PENDING_SESSION_KEY => [
                'path' => 'sales-order-attachments/pending/held.pdf', 'name' => 'metro-po.pdf', 'mime' => 'application/pdf',
                'size' => 30, 'source' => SalesOrderAttachment::SOURCE_SCAN, 'temporary' => true,
            ]])
            ->get('/sales-order')
            ->assertOk();

        $this->assertFalse($this->disk()->exists('sales-order-attachments/pending/held.pdf'));
        $this->assertNull(session(SalesOrderAttachmentService::PENDING_SESSION_KEY));
    }
}
