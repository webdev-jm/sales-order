<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Fluent;
use Tests\TestCase;

/**
 * The account's PO prefix must be applied exactly once, no matter which entry
 * point creates the order and no matter how many times it is edited afterwards.
 */
class SalesOrderPoPrefixTest extends TestCase
{
    use DatabaseTransactions;

    private SalesOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('activitylog.enabled', false);
        $this->service = app(SalesOrderService::class);
    }

    private function makeAccount(?string $prefix): Account
    {
        return Account::factory()->create([
            'company_id' => Company::factory()->create()->id,
            'po_prefix'  => $prefix,
        ]);
    }

    private function orderPayload(string $poNumber): Fluent
    {
        return new Fluent([
            'shipping_address_id'  => 'default',
            'po_number'            => $poNumber,
            'paf_number'           => '',
            'order_date'           => now()->format('Y-m-d'),
            'ship_date'            => now()->addWeekdays(2)->format('Y-m-d'),
            'shipping_instruction' => null,
            'ship_to_name'         => 'Test Customer',
            'ship_to_address1'     => null,
            'ship_to_address2'     => null,
            'ship_to_address3'     => null,
            'postal_code'          => null,
            'status'               => 'draft',
        ]);
    }

    private function emptyOrderData(): array
    {
        return [
            'items'          => [],
            'total_quantity' => 0,
            'total'          => 0,
            'grand_total'    => 0,
            'po_value'       => 0,
        ];
    }

    /**
     * A Fluent payload (the multiple-upload path) must still receive the prefix.
     * Fluent has no merge(), so the old implementation silently dropped it.
     */
    public function test_create_order_applies_po_prefix_once(): void
    {
        $account = $this->makeAccount('PH-');
        $user    = User::factory()->create();
        AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);
        $this->actingAs($user);

        $sales_order = $this->service->createOrder(
            $this->orderPayload('00123'),
            $account,
            $this->emptyOrderData()
        );

        $this->assertSame('PH-00123', $sales_order->fresh()->po_number);
    }

    /**
     * Editing an order posts back the stored, already-prefixed PO number.
     * It must not gain a second prefix.
     */
    public function test_update_order_does_not_reapply_po_prefix(): void
    {
        $account     = $this->makeAccount('PH-');
        $sales_order = SalesOrder::factory()->create(['po_number' => 'PH-00123']);

        $this->service->updateOrder(
            $sales_order,
            $this->orderPayload('PH-00123'),
            $account,
            $this->emptyOrderData()
        );

        $this->assertSame('PH-00123', $sales_order->fresh()->po_number);
    }

    /**
     * Repeated edits must remain stable.
     */
    public function test_update_order_is_idempotent_across_repeated_saves(): void
    {
        $account     = $this->makeAccount('PH-');
        $sales_order = SalesOrder::factory()->create(['po_number' => 'PH-00123']);

        foreach (range(1, 3) as $ignored) {
            $this->service->updateOrder(
                $sales_order,
                $this->orderPayload($sales_order->fresh()->po_number),
                $account,
                $this->emptyOrderData()
            );
        }

        $this->assertSame('PH-00123', $sales_order->fresh()->po_number);
    }

    /**
     * Accounts without a prefix are left untouched.
     */
    public function test_update_order_leaves_po_number_alone_without_prefix(): void
    {
        $account     = $this->makeAccount(null);
        $sales_order = SalesOrder::factory()->create(['po_number' => '00123']);

        $this->service->updateOrder(
            $sales_order,
            $this->orderPayload('00123'),
            $account,
            $this->emptyOrderData()
        );

        $this->assertSame('00123', $sales_order->fresh()->po_number);
    }

    /**
     * The parts toggle must be read from the sales-order config file; the key was
     * previously looked up at the config root, where it could never resolve.
     */
    public function test_enable_parts_config_key_is_namespaced(): void
    {
        $this->assertNull(Config::get('enable_parts'));
        $this->assertIsBool(Config::get('sales-order.enable_parts'));
    }
}
