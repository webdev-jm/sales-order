<?php

namespace Tests\Feature;

use App\Jobs\GenerateSalesOrderXml;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\SalesOrderService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class XmlGenerationToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(SettingSeeder::class);
        // Disable activity logging — the activity_log table is missing the batch_uuid
        // column required by spatie/laravel-activitylog v4 in the test schema.
        Config::set('activitylog.enabled', false);
    }

    private function createSuperadmin(): User
    {
        $user = User::factory()->create([
            'email'    => 'admin@admin',
            'password' => bcrypt('p4ssw0rd'),
            'status'   => 'active',
        ]);
        $user->assignRole('superadmin');
        return $user;
    }

    /**
     * Create an AccountLogin with po_process_date=null so the ship-date validation
     * only requires +1 weekday, letting us safely use +2 weekdays in all tests.
     */
    private function makeAccountLogin(User $user): AccountLogin
    {
        $account = Account::factory()->create(['po_process_date' => null]);
        $accountLogin = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);
        $accountLogin->load('account');
        return $accountLogin;
    }

    private function buildSession(AccountLogin $accountLogin): array
    {
        return [
            'logged_account' => $accountLogin,
            'order_data'     => ['items' => [1], 'po_value' => 1000],
        ];
    }

    private function validStorePayload(string $poNumber = 'PO-TOGGLE-001'): array
    {
        return [
            'status'       => 'finalized',
            'po_number'    => $poNumber,
            'order_date'   => now()->format('Y-m-d'),
            'ship_date'    => now()->addWeekdays(2)->format('Y-m-d'),
            'ship_to_name' => 'Test Customer',
        ];
    }

    private function validUpdatePayload(SalesOrder $order): array
    {
        return [
            'status'         => 'finalized',
            'control_number' => $order->control_number,
            'po_number'      => $order->po_number,
            'order_date'     => now()->format('Y-m-d'),
            'ship_date'      => now()->addWeekdays(2)->format('Y-m-d'),
            'ship_to_name'   => 'Test Customer',
        ];
    }

    public function test_xml_generation_enabled_config_defaults_to_true(): void
    {
        $this->assertTrue(config('sales-order.xml_generation_enabled'));
    }

    public function test_xml_generation_enabled_config_can_be_disabled(): void
    {
        Config::set('sales-order.xml_generation_enabled', false);

        $this->assertFalse(config('sales-order.xml_generation_enabled'));
    }

    public function test_store_dispatches_xml_job_when_toggle_is_enabled(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', true);

        $user         = $this->createSuperadmin();
        $accountLogin = $this->makeAccountLogin($user);
        $returnedOrder = SalesOrder::factory()->create(['account_login_id' => $accountLogin->id]);

        $mock = $this->mock(SalesOrderService::class);
        $mock->shouldReceive('createOrder')->once()->andReturn($returnedOrder);

        $this->actingAs($user)
            ->withSession($this->buildSession($accountLogin))
            ->post('/sales-order-store', $this->validStorePayload())
            ->assertRedirect();

        Queue::assertPushed(GenerateSalesOrderXml::class);
    }

    public function test_store_does_not_dispatch_xml_job_when_toggle_is_disabled(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', false);

        $user         = $this->createSuperadmin();
        $accountLogin = $this->makeAccountLogin($user);
        $returnedOrder = SalesOrder::factory()->create(['account_login_id' => $accountLogin->id]);

        $mock = $this->mock(SalesOrderService::class);
        $mock->shouldReceive('createOrder')->once()->andReturn($returnedOrder);

        $this->actingAs($user)
            ->withSession($this->buildSession($accountLogin))
            ->post('/sales-order-store', $this->validStorePayload())
            ->assertRedirect();

        Queue::assertNotPushed(GenerateSalesOrderXml::class);
    }

    public function test_store_does_not_dispatch_xml_job_for_draft_orders(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', true);

        $user         = $this->createSuperadmin();
        $accountLogin = $this->makeAccountLogin($user);
        $returnedOrder = SalesOrder::factory()->create(['account_login_id' => $accountLogin->id]);

        $mock = $this->mock(SalesOrderService::class);
        $mock->shouldReceive('createOrder')->once()->andReturn($returnedOrder);

        $payload             = $this->validStorePayload('PO-DRAFT-001');
        $payload['status']   = 'draft';

        $this->actingAs($user)
            ->withSession($this->buildSession($accountLogin))
            ->post('/sales-order-store', $payload)
            ->assertRedirect();

        Queue::assertNotPushed(GenerateSalesOrderXml::class);
    }

    public function test_update_dispatches_xml_job_when_toggle_is_enabled(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', true);

        $user         = $this->createSuperadmin();
        $accountLogin = $this->makeAccountLogin($user);
        $salesOrder   = SalesOrder::factory()->create([
            'account_login_id' => $accountLogin->id,
            'status'           => 'draft',
        ]);

        $mock = $this->mock(SalesOrderService::class);
        $mock->shouldReceive('updateOrder')
            ->once()
            ->andReturnUsing(function (SalesOrder $order): SalesOrder {
                $order->status = 'finalized';
                return $order;
            });

        $this->actingAs($user)
            ->withSession($this->buildSession($accountLogin))
            ->post("/sales-order/{$salesOrder->id}", $this->validUpdatePayload($salesOrder))
            ->assertRedirect();

        Queue::assertPushed(GenerateSalesOrderXml::class);
    }

    public function test_update_does_not_dispatch_xml_job_when_toggle_is_disabled(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', false);

        $user         = $this->createSuperadmin();
        $accountLogin = $this->makeAccountLogin($user);
        $salesOrder   = SalesOrder::factory()->create([
            'account_login_id' => $accountLogin->id,
            'status'           => 'draft',
        ]);

        $mock = $this->mock(SalesOrderService::class);
        $mock->shouldReceive('updateOrder')
            ->once()
            ->andReturnUsing(function (SalesOrder $order): SalesOrder {
                $order->status = 'finalized';
                return $order;
            });

        $this->actingAs($user)
            ->withSession($this->buildSession($accountLogin))
            ->post("/sales-order/{$salesOrder->id}", $this->validUpdatePayload($salesOrder))
            ->assertRedirect();

        Queue::assertNotPushed(GenerateSalesOrderXml::class);
    }

    public function test_get_xml_admin_route_dispatches_job_regardless_of_toggle(): void
    {
        Queue::fake();
        Config::set('sales-order.xml_generation_enabled', false);

        $user         = $this->createSuperadmin();
        $accountLogin = $this->makeAccountLogin($user);
        $salesOrder   = SalesOrder::factory()->create(['account_login_id' => $accountLogin->id]);

        $this->actingAs($user)
            ->get("/get-xml?id={$salesOrder->id}")
            ->assertRedirect();

        Queue::assertPushed(GenerateSalesOrderXml::class);
    }
}
