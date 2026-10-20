<?php

namespace Tests\Feature;

use App\Jobs\CheckSalesOrderStatus;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The order page queues an ERP status check on every view while the order has no upload status.
 * Refreshing the page must not queue the same check again while one is still waiting.
 */
class CheckSalesOrderStatusThrottleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('activitylog.enabled', false);
        Queue::fake();
    }

    private function userWithSalesOrderAccess(): User
    {
        $user = User::factory()->create(['status' => 'active']);

        $user->givePermissionTo(
            Permission::firstOrCreate(
                ['name' => 'sales order access', 'guard_name' => 'web'],
                ['module' => 'sales order', 'description' => 'sales order access']
            )
        );

        return $user;
    }

    private function pendingOrderFor(User $user): SalesOrder
    {
        $login = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => Account::factory()->create([
                'company_id'  => Company::factory()->create()->id,
                'discount_id' => null,
            ])->id,
        ]);

        return SalesOrder::factory()->create([
            'account_login_id' => $login->id,
            'reference'        => null,
            'upload_status'    => null,
        ]);
    }

    public function test_refreshing_the_order_page_queues_one_status_check(): void
    {
        $user  = $this->userWithSalesOrderAccess();
        $order = $this->pendingOrderFor($user);

        $this->actingAs($user)->get(route('sales-order.show', $order->id))->assertOk();
        $this->actingAs($user)->get(route('sales-order.show', $order->id))->assertOk();
        $this->actingAs($user)->get(route('sales-order.show', $order->id))->assertOk();

        Queue::assertPushed(CheckSalesOrderStatus::class, 1);
    }

    public function test_each_order_gets_its_own_status_check(): void
    {
        $user   = $this->userWithSalesOrderAccess();
        $first  = $this->pendingOrderFor($user);
        $second = $this->pendingOrderFor($user);

        $this->actingAs($user)->get(route('sales-order.show', $first->id))->assertOk();
        $this->actingAs($user)->get(route('sales-order.show', $second->id))->assertOk();

        Queue::assertPushed(CheckSalesOrderStatus::class, 2);
    }

    public function test_the_same_order_id_on_another_database_is_checked_separately(): void
    {
        $user  = $this->userWithSalesOrderAccess();
        $order = $this->pendingOrderFor($user);

        $live = new CheckSalesOrderStatus($order);
        $test = new CheckSalesOrderStatus($order);
        $test->db_connection = 'mysql_test';
        $live->db_connection = 'mysql';

        $this->assertNotSame($live->uniqueId(), $test->uniqueId());
    }

    public function test_an_order_with_an_upload_status_is_not_checked(): void
    {
        $user  = $this->userWithSalesOrderAccess();
        $order = $this->pendingOrderFor($user);
        $order->update(['upload_status' => 1]);

        $this->actingAs($user)->get(route('sales-order.show', $order->id))->assertOk();

        Queue::assertNotPushed(CheckSalesOrderStatus::class);
    }
}
