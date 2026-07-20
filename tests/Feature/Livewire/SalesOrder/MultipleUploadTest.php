<?php

namespace Tests\Feature\Livewire\SalesOrder;

use App\Http\Livewire\SalesOrder\Multiple\Upload;
use App\Jobs\GenerateSalesOrderXml;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Brand;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the multiple-sales-order upload path, which builds its order payload by
 * hand and therefore drifted from the single-order controller.
 */
class MultipleUploadTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;
    private AccountLogin $accountLogin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('activitylog.enabled', false);

        $company = Company::factory()->create();

        $this->account = Account::factory()->create([
            'company_id'         => $company->id,
            'price_code'         => 'A',
            'line_discount_code' => null,
            'sales_order_uom'    => null,
            'discount_id'        => null,
            'po_process_date'    => null,
            'po_prefix'          => 'PH-',
            'ship_to_address1'   => 'Building One',
            'ship_to_address2'   => 'Second Street',
            'ship_to_address3'   => 'Third City',
        ]);

        $user = User::factory()->create();
        $this->accountLogin = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);
        $this->accountLogin->load('account');

        $this->actingAs($user);

        $this->product = Product::factory()->create([
            'brand_id'  => Brand::factory()->create(['brand' => 'TEST BRAND'])->id,
            'stock_uom' => 'EA',
            'order_uom' => 'CS',
            'other_uom' => 'BOX',
        ]);

        PriceCode::factory()->create([
            'company_id'    => $company->id,
            'product_id'    => $this->product->id,
            'code'          => 'A',
            'selling_price' => 100,
            'price_basis'   => 'S',
        ]);
    }

    /**
     * @return array<string, mixed> a single so_data entry as processData() builds it
     */
    private function soDataEntry(): array
    {
        return [
            'ship_to_address'      => '',
            'shipping_address'     => [],
            'ship_date'            => now()->addWeekdays(3)->format('Y-m-d'),
            'po_value'             => 300,
            'paf_number'           => '',
            'shipping_instruction' => '',
            'discount'             => null,
            'lines'                => [
                [
                    'sku_code'            => $this->product->stock_code,
                    'product'             => $this->product->toArray(),
                    'uom'                 => 'EA',
                    'quantity'            => 3,
                    'total'               => 300,
                    'total_less_discount' => 300,
                    'line_discount'       => '0',
                ],
            ],
            'warnings'             => [],
            'calculated_data'      => [],
            'service_items_input'  => [
                $this->product->id => [
                    'product' => $this->product->toArray(),
                    'data'    => [
                        'EA' => ['quantity' => 3, 'paf_rows' => []],
                    ],
                ],
            ],
        ];
    }

    private function saveOrder(string $po_number, string $status = 'finalized'): void
    {
        Livewire::test(Upload::class, ['logged_account' => $this->accountLogin])
            ->set('so_data', [$po_number => $this->soDataEntry()])
            ->call('saveSalesOrder', $status, $po_number)
            ->assertHasNoErrors();
    }

    public function test_finalizing_dispatches_xml_job_when_toggle_is_enabled(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', true);

        $this->saveOrder('PH-UPLOAD-001');

        Queue::assertPushed(GenerateSalesOrderXml::class);
    }

    /**
     * The single-order controller honours this toggle; the upload path used to
     * ignore it and push XML to the ERP from staging.
     */
    public function test_finalizing_does_not_dispatch_xml_job_when_toggle_is_disabled(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', false);

        $this->saveOrder('PH-UPLOAD-002');

        Queue::assertNotPushed(GenerateSalesOrderXml::class);
        $this->assertNotNull(SalesOrder::where('po_number', 'PH-UPLOAD-002')->first());
    }

    /**
     * With no shipping address on the row, the order falls back to the account's
     * own address — each line to its matching column.
     */
    public function test_ship_to_address_falls_back_to_the_matching_account_columns(): void
    {
        Queue::fake();

        $this->saveOrder('PH-UPLOAD-003', 'draft');

        $sales_order = SalesOrder::where('po_number', 'PH-UPLOAD-003')->firstOrFail();

        $this->assertSame('Building One', $sales_order->ship_to_building);
        $this->assertSame('Second Street', $sales_order->ship_to_street);
        $this->assertSame('Third City', $sales_order->ship_to_city);
    }

    /**
     * The component strips the account prefix before handing the number to the
     * service, which must put it back exactly once.
     */
    public function test_po_prefix_is_applied_exactly_once(): void
    {
        Queue::fake();

        $this->saveOrder('PH-UPLOAD-004', 'draft');

        $this->assertSame(1, SalesOrder::where('po_number', 'PH-UPLOAD-004')->count());
        $this->assertSame(0, SalesOrder::where('po_number', 'like', 'PH-PH-%')->count());
        $this->assertSame(0, SalesOrder::where('po_number', 'UPLOAD-004')->count());
    }
}
