<?php

namespace Tests\Feature;

use App\Http\Livewire\Accounts\AccountBranchLogin;
use App\Http\Livewire\Accounts\AccountBranchLoginForm;
use App\Http\Livewire\Accounts\AccountLoginForm;
use App\Http\Livewire\Schedules\ScheduleEvent;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Branch;
use App\Models\BranchAddress;
use App\Models\BranchLogin;
use App\Models\Company;
use App\Models\User;
use App\Models\UserBranchSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * Being signed in to an account no longer blocks a branch sign in, and signing
 * in to a second account switches rather than being ignored.
 */
class AccountAndBranchLoginTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->seedSettings();
        Http::fake();
    }

    private function createSuperadmin(): User
    {
        $user = User::factory()->create([
            'email'    => 'login-flow@admin',
            'password' => bcrypt('p4ssw0rd'),
            'status'   => 'active',
        ]);
        $user->assignRole('superadmin');

        return $user;
    }

    private function createAssignedAccount(User $user): Account
    {
        $company = Company::factory()->create();
        $account = Account::factory()->create(['company_id' => $company->id]);
        $account->users()->attach($user->id);

        return $account;
    }

    private function signInToAccount(User $user, Account $account): AccountLogin
    {
        return AccountLogin::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'time_out'   => null,
        ]);
    }

    // ── Branch sign in ────────────────────────────────────────────────────────

    public function test_branch_sign_in_is_allowed_while_signed_in_to_an_account(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);
        $this->signInToAccount($user, $account);

        $branch = Branch::factory()->create(['account_id' => $account->id]);
        BranchAddress::factory()->create(['branch_id' => $branch->id]);

        Livewire::actingAs($user)
            ->test(AccountBranchLogin::class)
            ->call('selectBranch', $branch->id)
            ->set('accuracy', '10 m')
            ->set('longitude', '121.0437')
            ->set('latitude', '14.6760')
            ->call('login');

        $this->assertDatabaseHas('branch_logins', [
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'time_out'  => null,
        ]);
    }

    public function test_branch_sign_in_is_still_blocked_while_signed_in_to_another_branch(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $current = Branch::factory()->create(['account_id' => $account->id]);
        BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $current->id,
            'time_in'   => now(),
            'time_out'  => null,
        ]);

        $other = Branch::factory()->create(['account_id' => $account->id]);
        BranchAddress::factory()->create(['branch_id' => $other->id]);

        Livewire::actingAs($user)
            ->test(AccountBranchLogin::class)
            ->call('selectBranch', $other->id)
            ->set('accuracy', '10 m')
            ->set('longitude', '121.0437')
            ->set('latitude', '14.6760')
            ->call('login');

        $this->assertDatabaseMissing('branch_logins', [
            'user_id'   => $user->id,
            'branch_id' => $other->id,
        ]);
    }

    public function test_schedule_sign_in_is_allowed_while_signed_in_to_an_account(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);
        $this->signInToAccount($user, $account);

        $branch   = Branch::factory()->create(['account_id' => $account->id]);
        $schedule = UserBranchSchedule::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'date'      => now()->format('Y-m-d'),
        ]);

        Livewire::actingAs($user)
            ->test(ScheduleEvent::class)
            ->set('schedule_data', $schedule)
            ->set('accuracy', '10 m')
            ->set('longitude', '121.0437')
            ->set('latitude', '14.6760')
            ->call('sign_in');

        $this->assertDatabaseHas('branch_logins', [
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'time_out'  => null,
        ]);
    }

    // ── Account sign in treated as a switch ───────────────────────────────────

    public function test_signing_in_to_another_account_switches_instead_of_being_ignored(): void
    {
        $user     = $this->createSuperadmin();
        $previous = $this->createAssignedAccount($user);
        $next     = $this->createAssignedAccount($user);

        $previous_login = $this->signInToAccount($user, $previous);

        Livewire::actingAs($user)
            ->test(AccountLoginForm::class)
            ->call('set', $next->id)
            ->set('accuracy', '10 m')
            ->set('longitude', '121.0437')
            ->set('latitude', '14.6760')
            ->call('login');

        $this->assertNotNull($previous_login->fresh()->time_out);

        $open = AccountLogin::where('user_id', $user->id)->whereNull('time_out')->get();
        $this->assertCount(1, $open);
        $this->assertEquals($next->id, $open->first()->account_id);
    }

    public function test_the_login_form_warns_that_signing_in_switches_account(): void
    {
        $user     = $this->createSuperadmin();
        $previous = $this->createAssignedAccount($user);
        $previous->update(['short_name' => 'ALPHA']);
        $next = $this->createAssignedAccount($user);

        $this->signInToAccount($user, $previous);

        Livewire::actingAs($user)
            ->test(AccountLoginForm::class)
            ->call('set', $next->id)
            ->assertSee('Switching Account')
            ->assertSee('ALPHA')
            ->assertSee('Switch Account');
    }

    // ── Branch sign out and the account login it may have derived ─────────────

    public function test_branch_sign_out_keeps_an_account_the_user_signed_in_to(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $account_login = $this->signInToAccount($user, $account);

        $branch       = Branch::factory()->create(['account_id' => $account->id]);
        $branch_login = BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'time_in'   => now()->addMinute(),
            'time_out'  => null,
        ]);

        Livewire::actingAs($user)
            ->test(AccountBranchLoginForm::class, ['logged_branch' => $branch_login])
            ->call('logout');

        $this->assertNull($account_login->fresh()->time_out, 'The account the user chose must survive branch sign out.');
    }

    public function test_branch_sign_out_closes_the_account_login_it_derived(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $branch       = Branch::factory()->create(['account_id' => $account->id]);
        $branch_login = BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'time_in'   => now(),
            'time_out'  => null,
        ]);

        // Visiting a sales order page derives the account login from the branch.
        $this->actingAs($user)->get('/sales-order/create')->assertStatus(200);

        $derived = AccountLogin::where('user_id', $user->id)->whereNull('time_out')->first();
        $this->assertNotNull($derived);

        Livewire::actingAs($user)
            ->test(AccountBranchLoginForm::class, ['logged_branch' => $branch_login])
            ->call('logout');

        $this->assertNotNull($derived->fresh()->time_out, 'A login derived from the branch must close with it.');
    }

    // ── Home page ─────────────────────────────────────────────────────────────

    public function test_home_hides_the_account_banner_while_signed_in_to_an_account(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $this->signInToAccount($user, $account);

        // "loggedForm" is the sign out control unique to the account banner.
        $this->actingAs($user)
            ->get('/home')
            ->assertStatus(200)
            ->assertDontSee('loggedForm', false);
    }

    public function test_home_hides_the_activities_card_while_signed_in_to_an_account(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $this->signInToAccount($user, $account);

        $this->actingAs($user)
            ->get('/home')
            ->assertStatus(200)
            ->assertDontSee('<h3 class="card-title">Activities</h3>', false);
    }

    public function test_the_account_banner_still_shows_outside_home(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);

        $this->signInToAccount($user, $account);

        $this->actingAs($user)
            ->get('/sales-order')
            ->assertStatus(200)
            ->assertSee('loggedForm', false);
    }

    public function test_home_still_lists_accounts_while_signed_in_to_an_account(): void
    {
        $user    = $this->createSuperadmin();
        $account = $this->createAssignedAccount($user);
        $account->update(['account_name' => 'ALPHA TRADING']);

        $this->signInToAccount($user, $account);

        $this->actingAs($user)
            ->get('/home')
            ->assertStatus(200)
            ->assertSee('User Accounts')
            ->assertSee('ALPHA TRADING')
            ->assertSee('Branches');
    }
}
