<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SalesOrderService;
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
}
