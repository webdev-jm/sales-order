<?php

namespace Tests\Feature\Livewire\SalesOrder;

use App\Http\Livewire\SalesOrder\SalesOrderTotal;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Brand;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The running totals shown on the order screen delegate to SalesOrderService,
 * which tolerates products with no price code and zero UOM conversions. This
 * component used to do its own unguarded arithmetic and fatal on both.
 */
class SalesOrderTotalTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

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
        ]);

        $user = User::factory()->create();
        AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $this->account->id,
            'time_out'   => null,
        ]);

        $this->actingAs($user);
    }

    private function makeProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'brand_id'   => Brand::factory()->create(['brand' => 'TEST BRAND'])->id,
            'stock_uom'  => 'EA',
            'order_uom'  => 'CS',
            'other_uom'  => 'BOX',
            'warehouse'  => 'WH01',
        ], $attributes));
    }

    private function makePriceCode(Product $product, array $attributes = []): PriceCode
    {
        return PriceCode::factory()->create(array_merge([
            'company_id'    => $this->account->company_id,
            'product_id'    => $product->id,
            'code'          => 'A',
            'selling_price' => 100,
            'price_basis'   => 'S',
        ], $attributes));
    }

    /**
     * A product with no matching price code must be skipped, not fatal with
     * "Attempt to read property on null".
     */
    public function test_product_without_price_code_is_skipped_instead_of_throwing(): void
    {
        $product = $this->makeProduct();

        Livewire::test(SalesOrderTotal::class)
            ->call('getTotal', [$product->id => ['EA' => 5]])
            ->assertSet('total', '0.00')
            ->assertSet('grand_total', '0.00')
            ->assertStatus(200);

        $this->assertArrayNotHasKey($product->id, Session::get('order_data')['items'] ?? []);
    }

    /**
     * A zero UOM conversion must not raise a DivisionByZeroError while converting
     * the selling price down to stock UOM.
     */
    public function test_zero_uom_conversion_does_not_throw(): void
    {
        $product = $this->makeProduct([
            'order_uom_conversion' => 0,
            'order_uom_operator'   => 'M',
        ]);
        $this->makePriceCode($product, ['price_basis' => 'A']);

        Livewire::test(SalesOrderTotal::class)
            ->call('getTotal', [$product->id => ['EA' => 5]])
            ->assertSet('total', '500.00')
            ->assertStatus(200);
    }

    /**
     * The happy path still totals correctly and reports the quantity.
     */
    public function test_totals_are_calculated_for_a_priced_product(): void
    {
        $product = $this->makeProduct();
        $this->makePriceCode($product);

        Livewire::test(SalesOrderTotal::class)
            ->call('getTotal', [$product->id => ['EA' => 3]])
            ->assertSet('total', '300.00')
            ->assertSet('grand_total', '300.00');

        $order_data = Session::get('order_data');

        $this->assertSame(3.0, $order_data['items'][$product->id]['data']['EA']['quantity']);
        $this->assertSame(3.0, $order_data['total_quantity']);
    }

    /**
     * PAF rows are captured by a separate component straight into the session.
     * Recalculating the totals must not wipe them.
     */
    public function test_existing_paf_rows_survive_a_recalculation(): void
    {
        $product = $this->makeProduct();
        $this->makePriceCode($product);

        $paf_rows = [
            ['paf_number' => '2026-A-00001', 'uom' => 'EA', 'quantity' => 2],
        ];

        // Mirrors the session left behind by SalesOrderPaf::savePAF().
        Session::put('order_data', [
            'items' => [
                $product->id => [
                    'stock_code'  => $product->stock_code,
                    'description' => $product->description,
                    'size'        => $product->size,
                    'data'        => [
                        'EA' => [
                            'quantity'   => 3,
                            'total'      => 300,
                            'discount'   => '0',
                            'discounted' => 300,
                            'paf_rows'   => $paf_rows,
                        ],
                    ],
                ],
            ],
        ]);

        Livewire::test(SalesOrderTotal::class)
            ->call('getTotal', [$product->id => ['EA' => 3]]);

        $this->assertSame(
            $paf_rows,
            Session::get('order_data')['items'][$product->id]['data']['EA']['paf_rows']
        );
    }

    /**
     * The warehouse shown per line is still attached for non-order UOMs.
     */
    public function test_warehouse_is_attached_to_non_order_uom_lines(): void
    {
        $product = $this->makeProduct(['warehouse' => 'WH02']);
        $this->makePriceCode($product);

        Livewire::test(SalesOrderTotal::class)
            ->call('getTotal', [$product->id => ['EA' => 1]]);

        $this->assertSame(
            'WH02',
            Session::get('order_data')['items'][$product->id]['data']['EA']['warehouse']
        );
    }
}
