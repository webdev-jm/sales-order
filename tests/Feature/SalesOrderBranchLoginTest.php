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

class SalesOrderBranchLoginTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->seedSettings();
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
     * @return array{0: User, 1: Account, 2: BranchLogin}
     */
    private function signInToBranch(): array
    {
        $user    = $this->createSuperadmin();
        $company = Company::factory()->create();
        $account = Account::factory()->create(['company_id' => $company->id]);
        $branch  = Branch::factory()->create(['account_id' => $account->id]);

        $branch_login = BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'longitude' => '121.0437',
            'latitude'  => '14.6760',
            'accuracy'  => '12.50',
            'time_in'   => now(),
            'time_out'  => null,
        ]);

        return [$user, $account, $branch_login];
    }

    public function test_branch_login_can_access_sales_order_create(): void
    {
        [$user] = $this->signInToBranch();

        $this->actingAs($user)
            ->get('/sales-order/create')
            ->assertStatus(200);
    }

    public function test_branch_login_can_access_sales_order_index(): void
    {
        [$user] = $this->signInToBranch();

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200);
    }

    public function test_branch_login_can_access_multiple_upload(): void
    {
        [$user] = $this->signInToBranch();

        $this->actingAs($user)
            ->get('/sales-order-multiple')
            ->assertStatus(200);
    }

    public function test_account_login_is_created_from_the_branch_account(): void
    {
        [$user, $account] = $this->signInToBranch();

        $this->actingAs($user)->get('/sales-order/create')->assertStatus(200);

        $this->assertDatabaseHas('account_logins', [
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);
    }

    public function test_branch_login_coordinates_are_copied_to_the_account_login(): void
    {
        [$user, $account, $branch_login] = $this->signInToBranch();

        $this->actingAs($user)->get('/sales-order/create')->assertStatus(200);

        $account_login = AccountLogin::where('user_id', $user->id)->whereNull('time_out')->first();

        $this->assertNotNull($account_login);
        $this->assertEquals((float) $branch_login->longitude, (float) $account_login->longitude);
        $this->assertEquals((float) $branch_login->latitude, (float) $account_login->latitude);
        $this->assertEquals((float) $branch_login->accuracy, (float) $account_login->accuracy);
    }

    public function test_only_one_account_login_is_created_across_requests(): void
    {
        [$user] = $this->signInToBranch();

        $this->actingAs($user)->get('/sales-order/create')->assertStatus(200);
        $this->actingAs($user)->get('/sales-order')->assertStatus(200);

        $this->assertEquals(1, AccountLogin::where('user_id', $user->id)->whereNull('time_out')->count());
    }

    public function test_existing_account_login_is_kept_when_signed_in_to_an_account(): void
    {
        $user    = $this->createSuperadmin();
        $account = Account::factory()->create();
        $account->users()->attach($user->id);

        $account_login = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);

        $this->actingAs($user)->get('/sales-order/create')->assertStatus(200);

        $this->assertEquals(1, AccountLogin::where('user_id', $user->id)->whereNull('time_out')->count());
        $this->assertEquals(
            $account_login->id,
            AccountLogin::where('user_id', $user->id)->whereNull('time_out')->first()->id
        );
    }

    public function test_user_without_branch_or_account_login_is_redirected_home(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)->get('/sales-order/create')->assertRedirect('/home');
        $this->assertEquals(0, AccountLogin::where('user_id', $user->id)->count());
    }

    public function test_home_still_shows_the_branch_after_the_account_login_is_derived(): void
    {
        [$user, $account, $branch_login] = $this->signInToBranch();

        $this->actingAs($user)->get('/sales-order/create')->assertStatus(200);

        $this->actingAs($user)
            ->get('/home')
            ->assertStatus(200)
            ->assertViewHas('logged_account', null)
            ->assertViewHas('logged_branch', fn($logged) => $logged->id === $branch_login->id);
    }

    public function test_branch_banner_shows_the_owning_account_details(): void
    {
        [$user, $account] = $this->signInToBranch();

        $account->update([
            'account_code' => 'ACCT-42',
            'short_name'   => 'ACME',
            'account_name' => 'Acme Corporation',
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Accounts\AccountLogged::class)
            ->assertSee('Account:')
            ->assertSee('[ACCT-42] ACME - Acme Corporation');
    }
}
