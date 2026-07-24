<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Branch;
use App\Models\BranchLogin;
use App\Models\Company;
use App\Models\OrganizationStructure;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $this->assertEquals('8.5 m', $trail[0]['accuracy']);
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
