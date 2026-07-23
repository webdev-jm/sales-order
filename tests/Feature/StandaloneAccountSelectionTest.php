<?php

namespace Tests\Feature;

use App\Http\Livewire\Accounts\ActiveAccountBanner;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Branch;
use App\Models\BranchLogin;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * The sales order and PPU modules stand on their own: the account a record is
 * tagged with is chosen from a banner on those pages rather than through the
 * home screen sign in.
 */
class StandaloneAccountSelectionTest extends TestCase
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
            'email'    => 'standalone@admin',
            'password' => bcrypt('p4ssw0rd'),
            'status'   => 'active',
        ]);
        $user->assignRole('superadmin');

        return $user;
    }

    private function createAssignedAccount(User $user, array $attributes = []): Account
    {
        $company = Company::factory()->create();
        $account = Account::factory()->create($attributes + ['company_id' => $company->id]);
        $account->users()->attach($user->id);

        return $account;
    }

    public function test_sales_order_list_is_hidden_until_an_account_is_selected(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200)
            ->assertViewHas('logged_account', null)
            ->assertSee('Select an account to continue');
    }

    public function test_ppu_list_is_hidden_until_an_account_is_selected(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)
            ->get('/ppu-form')
            ->assertStatus(200)
            ->assertViewHas('logged_account', null)
            ->assertSee('Select an account to continue');
    }

    public function test_ppu_create_is_sent_back_to_the_list_without_an_account(): void
    {
        $user = $this->createSuperadmin();

        $this->actingAs($user)
            ->get('/ppu-form/create')
            ->assertRedirect(route('ppu.index'));
    }

    public function test_sales_order_list_is_shown_once_an_account_is_selected(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200)
            ->assertViewHas('logged_account', fn ($logged) => $logged->account_id === $account->id)
            ->assertSee('List of Sales Orders');
    }

    /**
     * The banner must not swap Laravel's default pagination view for the
     * Livewire one, which dereferences $this and blows up in the plain Blade
     * paginator further down these pages. The paginator only renders its links
     * once there is more than one page, so the list has to spill over.
     */
    public function test_sales_order_list_renders_with_the_banner_when_the_list_paginates(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $account_login = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);

        \App\Models\SalesOrder::factory()->count(11)->create([
            'account_login_id' => $account_login->id,
            'status'           => 'draft',
        ]);

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200)
            ->assertSee('List of Sales Orders');
    }

    public function test_ppu_list_renders_with_the_banner_when_the_list_paginates(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $account_login = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);

        \App\Models\PPUForm::factory()->count(11)->create([
            'account_login_id' => $account_login->id,
        ]);

        $this->actingAs($user)
            ->get('/ppu-form')
            ->assertStatus(200)
            ->assertSee('List of PPU Forms');
    }

    public function test_banner_only_lists_accounts_assigned_to_the_user(): void
    {
        $user = $this->createSuperadmin();
        $this->createAssignedAccount($user, ['account_name' => 'ASSIGNED TRADING']);

        $company = Company::factory()->create();
        Account::factory()->create([
            'company_id'   => $company->id,
            'account_name' => 'UNASSIGNED TRADING',
        ]);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('openSelector')
            ->assertSee('ASSIGNED TRADING')
            ->assertDontSee('UNASSIGNED TRADING');
    }

    public function test_banner_search_filters_the_assigned_accounts(): void
    {
        $user = $this->createSuperadmin();
        $this->createAssignedAccount($user, ['account_code' => 'AC-100', 'account_name' => 'ALPHA STORES']);
        $this->createAssignedAccount($user, ['account_code' => 'AC-200', 'account_name' => 'BRAVO STORES']);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->set('search', 'ALPHA')
            ->assertSee('ALPHA STORES')
            ->assertDontSee('BRAVO STORES');
    }

    public function test_selecting_an_account_asks_for_confirmation_before_switching(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user, ['short_name' => 'ALPHA']);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('confirmAccount', $account->id)
            ->assertSet('confirm_account_id', $account->id)
            ->assertSee('Confirm Account Switch');

        $this->assertEquals(0, AccountLogin::where('user_id', $user->id)->count());
    }

    public function test_confirming_creates_the_account_login_used_to_tag_records(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('confirmAccount', $account->id)
            ->call('switchAccount');

        $this->assertDatabaseHas('account_logins', [
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);
    }

    public function test_switching_closes_the_previous_account_login(): void
    {
        $user     = $this->createSuperadmin();
        $previous = $this->createAssignedAccount($user);
        $next     = $this->createAssignedAccount($user);

        $previous_login = AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $previous->id,
            'time_out'   => null,
        ]);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('confirmAccount', $next->id)
            ->call('switchAccount');

        $this->assertNotNull($previous_login->fresh()->time_out);
        $this->assertEquals(1, AccountLogin::where('user_id', $user->id)->whereNull('time_out')->count());
        $this->assertEquals(
            $next->id,
            AccountLogin::where('user_id', $user->id)->whereNull('time_out')->first()->account_id
        );
    }

    public function test_switching_discards_the_in_progress_order_data(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        session(['order_data' => ['items' => [['stock_code' => 'KS01046']]], 'ppu_item' => ['items' => []]]);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('confirmAccount', $account->id)
            ->call('switchAccount');

        $this->assertNull(session('order_data'));
        $this->assertNull(session('ppu_item'));
    }

    public function test_an_account_not_assigned_to_the_user_cannot_be_selected(): void
    {
        $user    = $this->createSuperadmin();
        $company = Company::factory()->create();
        $account = Account::factory()->create(['company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('confirmAccount', $account->id)
            ->call('switchAccount')
            ->assertSet('confirm_account_id', null);

        $this->assertEquals(0, AccountLogin::where('user_id', $user->id)->count());
    }

    public function test_account_can_be_selected_while_signed_in_to_another_branch(): void
    {
        $user = $this->createSuperadmin();

        $branch_account = $this->createAssignedAccount($user);
        $branch         = Branch::factory()->create(['account_id' => $branch_account->id]);
        BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'longitude' => '121.0437',
            'latitude'  => '14.6760',
            'accuracy'  => '12.50',
            'time_in'   => now(),
            'time_out'  => null,
        ]);

        $other_account = $this->createAssignedAccount($user);

        Livewire::actingAs($user)
            ->test(ActiveAccountBanner::class)
            ->call('confirmAccount', $other_account->id)
            ->call('switchAccount');

        $this->assertEquals(
            $other_account->id,
            AccountLogin::where('user_id', $user->id)->whereNull('time_out')->first()->account_id
        );
    }
}
