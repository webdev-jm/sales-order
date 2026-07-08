<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SalesOrderService;
use App\Models\Account;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\SalesOrder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private SalesOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
        $this->service = app(SalesOrderService::class);
    }

    /**
     * generateControlNumber() should return a string matching SO-YYYYMMDD-NNN format.
     */
    public function test_generate_control_number_returns_correct_format(): void
    {
        $number = $this->service->generateControlNumber();

        $this->assertMatchesRegularExpression('/^SO-\d{8}-\d{3}$/', $number);
    }

    /**
     * generateControlNumber() should start at SO-YYYYMMDD-001 when no orders exist.
     */
    public function test_generate_control_number_starts_at_001_with_no_orders(): void
    {
        $dateCode = date('Ymd');
        $number   = $this->service->generateControlNumber();

        $this->assertSame("SO-{$dateCode}-001", $number);
    }

    /**
     * generateControlNumber() should increment the sequence when an order already
     * exists for today's date.
     */
    public function test_generate_control_number_increments_sequence(): void
    {
        $dateCode = date('Ymd');

        SalesOrder::factory()->create(['control_number' => "SO-{$dateCode}-001"]);

        $number = $this->service->generateControlNumber();

        $this->assertSame("SO-{$dateCode}-002", $number);
    }

    /**
     * calculateOrderTotals() should return the expected structure keys when
     * called with an empty data array.
     */
    public function test_calculate_order_totals_returns_expected_structure_with_empty_data(): void
    {
        $account = new \App\Models\Account(['discount_id' => null]);

        $result = $this->service->calculateOrderTotals([], $account);

        $this->assertArrayHasKey('total_quantity', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('grand_total', $result);
        $this->assertArrayHasKey('po_value', $result);
        $this->assertSame(0, $result['total_quantity']);
        $this->assertSame(0, $result['total']);
        $this->assertSame(0, $result['grand_total']);
    }

    /**
     * calculateOrderTotals() should drop a product from the results and report it
     * under 'skipped_items' when no price code matches the account's company/code,
     * instead of silently disappearing with no explanation (the multiple-SO-upload bug).
     */
    public function test_calculate_order_totals_reports_skipped_item_when_no_price_code_matches(): void
    {
        $company = Company::factory()->create();
        $account = Account::factory()->create([
            'company_id' => $company->id,
            'price_code' => 'B',
            'line_discount_code' => null,
        ]);
        $product = Product::factory()->create([
            'brand_id' => \App\Models\Brand::factory()->create(['brand' => 'TEST BRAND'])->id,
        ]);

        // A price code exists for this product, but under a different code than the account uses.
        PriceCode::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'code' => 'A',
        ]);

        $data = [
            $product->id => [
                'product' => $product,
                'data' => [
                    $product->stock_uom => ['quantity' => 5],
                ],
            ],
        ];

        $result = $this->service->calculateOrderTotals($data, $account);

        $this->assertArrayNotHasKey($product->id, $result['items'] ?? []);
        $this->assertCount(1, $result['skipped_items']);
        $this->assertSame($product->stock_code, $result['skipped_items'][0]['stock_code']);
        $this->assertStringContainsString("No price code 'B'", $result['skipped_items'][0]['reason']);
    }

    /**
     * calculateOrderTotals() should include the product normally, with nothing
     * reported as skipped, once a matching price code exists.
     */
    public function test_calculate_order_totals_does_not_report_skipped_item_when_price_code_matches(): void
    {
        $company = Company::factory()->create();
        $account = Account::factory()->create([
            'company_id' => $company->id,
            'price_code' => 'A',
            'line_discount_code' => null,
        ]);
        $product = Product::factory()->create([
            'brand_id' => \App\Models\Brand::factory()->create(['brand' => 'TEST BRAND'])->id,
        ]);

        PriceCode::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'code' => 'A',
            'selling_price' => 100,
            'price_basis' => 'S',
        ]);

        $data = [
            $product->id => [
                'product' => $product,
                'data' => [
                    $product->stock_uom => ['quantity' => 5],
                ],
            ],
        ];

        $result = $this->service->calculateOrderTotals($data, $account);

        $this->assertArrayHasKey($product->id, $result['items']);
        $this->assertEmpty($result['skipped_items']);
    }
}
