<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * CheckSalesOrderStatus stores the ERP references joined with ", ". The details
 * page splits them back apart to badge each order part, and used to split on
 * " ," — a separator that never occurs, so nothing was ever split.
 */
class SalesOrderReferenceSplitTest extends TestCase
{
    use DatabaseTransactions;

    private function accountLoginFor(User $user): AccountLogin
    {
        return AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => Account::factory()->create([
                'company_id'  => Company::factory()->create()->id,
                'discount_id' => null,
            ])->id,
        ]);
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

    public function test_details_page_splits_each_erp_reference(): void
    {
        Config::set('activitylog.enabled', false);

        $user        = $this->userWithSalesOrderAccess();
        $sales_order = SalesOrder::factory()->create([
            'account_login_id' => $this->accountLoginFor($user)->id,
            'reference'        => '111111, 222222',
            'upload_status'    => 1,
        ]);

        $response = $this->actingAs($user)->get(route('sales-order.show', $sales_order->id));

        $response->assertOk();
        $this->assertSame(['111111', '222222'], $response->viewData('reference_arr'));
    }

    public function test_empty_reference_yields_an_empty_list(): void
    {
        Config::set('activitylog.enabled', false);

        $user        = $this->userWithSalesOrderAccess();
        $sales_order = SalesOrder::factory()->create([
            'account_login_id' => $this->accountLoginFor($user)->id,
            'reference'        => null,
            'upload_status'    => 1,
        ]);

        $response = $this->actingAs($user)->get(route('sales-order.show', $sales_order->id));

        $response->assertOk();
        $this->assertSame([], $response->viewData('reference_arr'));
    }
}
