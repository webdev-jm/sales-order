<?php

namespace Tests\Feature;

use App\Http\Livewire\Accounts\AccountBranchLoginForm;
use App\Http\Requests\StoreBranchLocationRequest;
use App\Models\Account;
use App\Models\Branch;
use App\Models\BranchLogin;
use App\Models\Company;
use App\Models\OrganizationStructure;
use App\Models\User;
use App\Services\Maps\LocationTrail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * Store location records: per-minute GPS capture into the branch login trail,
 * the permission-gated locations report, and the "report restricted" filter
 * that limits report visibility to the current user's subordinates.
 */
class StoreLocationRecordsTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    private function makeUser(string $firstname, string $lastname): User
    {
        return User::factory()->create([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'status'    => 'active',
        ]);
    }

    private function makeBranch(): Branch
    {
        $company = Company::factory()->create();
        $account = Account::factory()->create(['company_id' => $company->id]);

        return Branch::factory()->create(['account_id' => $account->id]);
    }

    private function openBranchLogin(User $user, ?Branch $branch = null): BranchLogin
    {
        return BranchLogin::factory()->create([
            'user_id'        => $user->id,
            'branch_id'      => ($branch ?? $this->makeBranch())->id,
            'time_in'        => now(),
            'time_out'       => null,
            'location_trail' => null,
        ]);
    }

    // ── Location capture endpoint ─────────────────────────────────────────────

    public function test_location_point_is_appended_to_the_open_branch_login(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), [
                'latitude'  => 14.6760,
                'longitude' => 121.0437,
                'accuracy'  => '8.5 m',
            ])
            ->assertOk()
            ->assertJson(['recorded' => true, 'points' => 1]);

        $trail = $login->fresh()->location_trail;

        $this->assertCount(1, $trail);
        $this->assertEquals(14.6760, $trail[0]['latitude']);
        $this->assertEquals(121.0437, $trail[0]['longitude']);
        $this->assertSame(8.5, $trail[0]['accuracy']);
        $this->assertArrayHasKey('recorded_at', $trail[0]);
    }

    public function test_location_points_accumulate_across_calls(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);

        $this->actingAs($user)->postJson(route('branch-location.store'), [
            'latitude' => 14.6760, 'longitude' => 121.0437,
        ])->assertOk();

        $this->actingAs($user)->postJson(route('branch-location.store'), [
            'latitude' => 14.6800, 'longitude' => 121.0500,
        ])->assertOk()->assertJson(['points' => 2]);

        $this->assertCount(2, $login->fresh()->location_trail);
    }

    public function test_location_point_is_rejected_without_an_open_branch_login(): void
    {
        $user = $this->makeUser('Idle', 'User');

        // A signed-out login must not receive points.
        BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $this->makeBranch()->id,
            'time_in'   => now()->subHour(),
            'time_out'  => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), [
                'latitude' => 14.6760, 'longitude' => 121.0437,
            ])
            ->assertStatus(409)
            ->assertJson(['recorded' => false]);
    }

    public function test_location_point_validates_coordinates(): void
    {
        $user = $this->makeUser('Field', 'Rep');
        $this->openBranchLogin($user);

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), [
                'latitude' => 999, 'longitude' => 'not-a-number',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    // ── Readings kept on the phone ────────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>
     */
    private function phoneReadings(Carbon $from, int $count, float $latitude = 14.6760): array
    {
        $points = [];
        for ($i = 0; $i < $count; $i++) {
            $points[] = [
                'latitude'    => $latitude + $i * 0.005,
                'longitude'   => 121.0437,
                'accuracy'    => 9.5,
                'recorded_at' => $from->copy()->addMinutes($i)->getTimestampMs(),
            ];
        }

        return $points;
    }

    public function test_a_batch_kept_offline_is_saved_with_the_phone_times(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);
        $login->update(['time_in' => now()->subHour()]);

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), [
                'branch_login_id' => $login->id,
                'points'          => $this->phoneReadings(now()->subMinutes(50), 3),
            ])
            ->assertOk()
            ->assertJson(['recorded' => true, 'points' => 3, 'added' => 3]);

        $trail = $login->fresh()->location_trail;
        $this->assertSame(now()->subMinutes(50)->toDateTimeString(), $trail[0]['recorded_at']);
        $this->assertSame(9.5, $trail[0]['accuracy']);
    }

    public function test_readings_still_reach_a_branch_login_after_sign_out(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $this->makeBranch()->id,
            'time_in'   => now()->subHours(3),
            'time_out'  => now()->subHour(),
        ]);

        $readings = array_merge(
            $this->phoneReadings(now()->subHours(2), 2),
            $this->phoneReadings(now()->subMinutes(30), 1, 14.70),
        );

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), ['branch_login_id' => $login->id, 'points' => $readings])
            ->assertOk()
            ->assertJson(['added' => 2, 'ignored' => 1]);

        $this->assertCount(2, $login->fresh()->location_trail, 'A reading taken after sign-out belongs to no visit.');
    }

    public function test_the_same_batch_sent_twice_is_stored_once(): void
    {
        $user     = $this->makeUser('Field', 'Rep');
        $login    = $this->openBranchLogin($user);
        $login->update(['time_in' => now()->subHour()]);
        $readings = $this->phoneReadings(now()->subMinutes(20), 4);

        $this->actingAs($user)->postJson(route('branch-location.store'), ['branch_login_id' => $login->id, 'points' => $readings])->assertOk();
        $this->actingAs($user)->postJson(route('branch-location.store'), ['branch_login_id' => $login->id, 'points' => $readings])
            ->assertOk()
            ->assertJson(['added' => 0, 'ignored' => 4, 'points' => 4]);
    }

    public function test_readings_at_the_same_spot_are_merged_into_one_point(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);
        $login->update(['time_in' => now()->subHour()]);

        $readings = array_map(fn (int $i) => [
            'latitude'    => 14.6760 + ($i % 2) * 0.00002,
            'longitude'   => 121.0437,
            'accuracy'    => 12,
            'recorded_at' => now()->subMinutes(40 - $i)->getTimestampMs(),
        ], range(0, 29));

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), ['branch_login_id' => $login->id, 'points' => $readings])
            ->assertOk()
            ->assertJson(['points' => 1, 'added' => 1, 'merged' => 29]);

        $this->assertSame(30, $login->fresh()->location_trail[0]['samples']);
    }

    public function test_another_users_branch_login_is_refused(): void
    {
        $owner    = $this->makeUser('Owner', 'Rep');
        $intruder = $this->makeUser('Other', 'Rep');
        $login    = $this->openBranchLogin($owner);

        $this->actingAs($intruder)
            ->postJson(route('branch-location.store'), ['branch_login_id' => $login->id, 'points' => $this->phoneReadings(now(), 1)])
            ->assertStatus(409);

        $this->assertNull($login->fresh()->location_trail);
    }

    public function test_a_branch_login_older_than_the_late_sync_window_is_refused(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = BranchLogin::factory()->create([
            'user_id'   => $user->id,
            'branch_id' => $this->makeBranch()->id,
            'time_in'   => now()->subDays(LocationTrail::LATE_SYNC_DAYS + 1),
            'time_out'  => now()->subDays(LocationTrail::LATE_SYNC_DAYS + 1)->addHours(8),
        ]);

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), ['branch_login_id' => $login->id, 'points' => $this->phoneReadings(now(), 1)])
            ->assertStatus(409);
    }

    public function test_a_batch_larger_than_the_limit_is_rejected(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);

        $this->actingAs($user)
            ->postJson(route('branch-location.store'), [
                'branch_login_id' => $login->id,
                'points'          => $this->phoneReadings(now(), StoreBranchLocationRequest::MAX_BATCH + 1),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['points']);
    }

    public function test_branch_sign_out_saves_the_readings_still_on_the_phone(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);
        $login->update(['time_in' => now()->subHours(2)]);
        session(['logged_branch' => $login]);

        Livewire::actingAs($user)
            ->test(AccountBranchLoginForm::class, ['logged_branch' => $login->fresh()])
            ->call('logout', $this->phoneReadings(now()->subMinutes(90), 3));

        $login->refresh();
        $this->assertNotNull($login->time_out);
        $this->assertCount(3, $login->location_trail);
    }

    public function test_branch_sign_out_without_readings_still_signs_out(): void
    {
        $user  = $this->makeUser('Field', 'Rep');
        $login = $this->openBranchLogin($user);

        Livewire::actingAs($user)
            ->test(AccountBranchLoginForm::class, ['logged_branch' => $login])
            ->call('logout');

        $this->assertNotNull($login->fresh()->time_out);
        $this->assertNull($login->fresh()->location_trail);
    }

    public function test_the_tracker_is_on_every_page_and_records_only_in_a_branch_session(): void
    {
        $user = $this->makeUser('Field', 'Rep');
        $user->givePermissionTo('location report access');

        $this->actingAs($user)->get(route('report.locations'))
            ->assertOk()
            ->assertSee('window.SmsLocationTrail', false)
            ->assertSee('var branchLoginId = null;', false);

        $login = $this->openBranchLogin($user);

        $this->actingAs($user)->withSession(['logged_branch' => $login])->get(route('report.locations'))
            ->assertOk()
            ->assertSee('var branchLoginId = ' . $login->id . ';', false);
    }

    // ── Locations report gating ───────────────────────────────────────────────

    public function test_locations_report_is_blocked_without_permission(): void
    {
        $user = $this->makeUser('No', 'Access');

        $this->actingAs($user)
            ->get(route('report.locations'))
            ->assertStatus(403);
    }

    public function test_locations_report_is_accessible_with_permission(): void
    {
        $user = $this->makeUser('Report', 'Viewer');
        $user->givePermissionTo('location report access');

        $this->actingAs($user)
            ->get(route('report.locations'))
            ->assertStatus(200);
    }

    // ── Report restricted filter ──────────────────────────────────────────────

    public function test_report_restricted_user_only_sees_subordinates_on_the_locations_report(): void
    {
        $manager = $this->makeUser('Manager', 'Prime');
        $manager->givePermissionTo(['location report access', 'report restricted']);
        $manager_org = OrganizationStructure::factory()->create([
            'user_id' => $manager->id,
            'type'    => 'NKAG',
        ]);

        $subordinate = $this->makeUser('Subordinate', 'Alpha');
        OrganizationStructure::factory()->create([
            'user_id'       => $subordinate->id,
            'reports_to_id' => $manager_org->id,
            'type'          => 'NKAG',
        ]);

        $stranger = $this->makeUser('Stranger', 'Beta');
        OrganizationStructure::factory()->create([
            'user_id' => $stranger->id,
            'type'    => 'NKAG',
        ]);

        $point = [[
            'latitude' => 14.6760, 'longitude' => 121.0437,
            'accuracy' => '8 m', 'recorded_at' => now()->toDateTimeString(),
        ]];

        BranchLogin::factory()->create([
            'user_id'        => $subordinate->id,
            'branch_id'      => $this->makeBranch()->id,
            'time_in'        => now(),
            'location_trail' => $point,
        ]);
        BranchLogin::factory()->create([
            'user_id'        => $stranger->id,
            'branch_id'      => $this->makeBranch()->id,
            'time_in'        => now(),
            'location_trail' => $point,
        ]);

        $today = now()->format('Y-m-d');

        $this->actingAs($manager)
            ->get(route('report.locations', ['date_from' => $today, 'date_to' => $today]))
            ->assertStatus(200)
            ->assertSee('Subordinate')
            ->assertDontSee('Stranger');
    }

    public function test_unrestricted_user_sees_all_users_on_the_locations_report(): void
    {
        $viewer = $this->makeUser('Open', 'Viewer');
        $viewer->givePermissionTo('location report access');

        $point = [[
            'latitude' => 14.6760, 'longitude' => 121.0437,
            'accuracy' => '8 m', 'recorded_at' => now()->toDateTimeString(),
        ]];

        $other = $this->makeUser('Someone', 'Elsewhere');
        BranchLogin::factory()->create([
            'user_id'        => $other->id,
            'branch_id'      => $this->makeBranch()->id,
            'time_in'        => now(),
            'location_trail' => $point,
        ]);

        $today = now()->format('Y-m-d');

        $this->actingAs($viewer)
            ->get(route('report.locations', ['date_from' => $today, 'date_to' => $today]))
            ->assertStatus(200)
            ->assertSee('Someone');
    }
}
