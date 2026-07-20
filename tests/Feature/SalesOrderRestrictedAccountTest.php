<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Branch;
use App\Models\BranchLogin;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

class SalesOrderRestrictedAccountTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    private const RESTRICTED_CODE = '1200099';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->seedSettings();

        config(['sales-order.restricted_accounts' => [self::RESTRICTED_CODE]]);
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

    private function createAccount(string $account_code): Account
    {
        $company = Company::factory()->create();

        return Account::factory()->create([
            'company_id'   => $company->id,
            'account_code' => $account_code,
            'account_name' => 'RESTRICTED STORE',
        ]);
    }

    private function signInToAccount(Account $account): User
    {
        $user = $this->createSuperadmin();
        $account->users()->attach($user->id);

        AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);

        return $user;
    }

    private function signInToBranchOf(Account $account): User
    {
        $user   = $this->createSuperadmin();
        $branch = Branch::factory()->create(['account_id' => $account->id]);

        BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'time_in'   => now(),
            'time_out'  => null,
        ]);

        return $user;
    }

    public function test_restricted_account_cannot_open_sales_order_create(): void
    {
        $user = $this->signInToAccount($this->createAccount(self::RESTRICTED_CODE));

        $this->actingAs($user)
            ->get('/sales-order/create')
            ->assertRedirect('/home')
            ->assertSessionHas('message_error', 'RESTRICTED STORE is not available for sales order creation.');
    }

    public function test_restricted_account_cannot_open_multiple_upload(): void
    {
        $user = $this->signInToAccount($this->createAccount(self::RESTRICTED_CODE));

        $this->actingAs($user)
            ->get('/sales-order-multiple')
            ->assertRedirect('/home')
            ->assertSessionHas('message_error', 'RESTRICTED STORE is not available for sales order creation.');
    }

    public function test_branch_of_restricted_account_cannot_open_sales_order_create(): void
    {
        $user = $this->signInToBranchOf($this->createAccount(self::RESTRICTED_CODE));

        $this->actingAs($user)
            ->get('/sales-order/create')
            ->assertRedirect('/home')
            ->assertSessionHas('message_error', 'RESTRICTED STORE is not available for sales order creation.');
    }

    public function test_branch_of_restricted_account_cannot_open_multiple_upload(): void
    {
        $user = $this->signInToBranchOf($this->createAccount(self::RESTRICTED_CODE));

        $this->actingAs($user)
            ->get('/sales-order-multiple')
            ->assertRedirect('/home');
    }

    public function test_restricted_account_can_still_view_the_sales_order_list(): void
    {
        $user = $this->signInToAccount($this->createAccount(self::RESTRICTED_CODE));

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200)
            ->assertViewHas('restricted', true)
            ->assertDontSee('ADD SALES ORDER')
            ->assertSee('RESTRICTED STORE is not available for sales order creation.');
    }

    public function test_unrestricted_account_is_unaffected(): void
    {
        $user = $this->signInToAccount($this->createAccount('1200100'));

        $this->actingAs($user)
            ->get('/sales-order/create')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200)
            ->assertViewHas('restricted', false);
    }

    public function test_home_hides_the_sales_order_button_for_a_restricted_account(): void
    {
        $user = $this->signInToAccount($this->createAccount(self::RESTRICTED_CODE));

        $this->actingAs($user)
            ->get('/home')
            ->assertStatus(200)
            ->assertViewHas('restricted', true)
            ->assertSee('RESTRICTED STORE is not available for sales order creation.');
    }
}
