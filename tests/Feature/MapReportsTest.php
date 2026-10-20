<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Branch;
use App\Models\BranchAddress;
use App\Models\BranchLogin;
use App\Models\Company;
use App\Models\OrganizationStructure;
use App\Models\User;
use App\Models\UserBranchSchedule;
use App\Services\Maps\VisitMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * MCP MAP (branch sign-ins against the branch pin, schedule coverage) and MAPS (GPS routes).
 */
class MapReportsTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    private const DAY = '2026-10-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    private function user(string $firstname): User
    {
        return User::factory()->create(['firstname' => $firstname, 'lastname' => 'Tester', 'status' => 'active']);
    }

    private function viewer(array $permissions = ['report access', 'location report access']): User
    {
        $viewer = $this->user('Viewer');
        $viewer->givePermissionTo($permissions);

        return $viewer;
    }

    private function branch(?array $pin = [14.5600, 121.0200]): Branch
    {
        $account = Account::factory()->create(['company_id' => Company::factory()->create()->id]);
        $branch = Branch::factory()->create(['account_id' => $account->id]);

        if ($pin) {
            BranchAddress::create(['branch_id' => $branch->id, 'latitude' => $pin[0], 'longitude' => $pin[1]]);
        }

        return $branch;
    }

    private function visit(User $user, Branch $branch, float $latitude, float $longitude, string $day = self::DAY, array $trail = null): BranchLogin
    {
        return BranchLogin::factory()->create([
            'user_id'        => $user->id,
            'branch_id'      => $branch->id,
            'latitude'       => $latitude,
            'longitude'      => $longitude,
            'accuracy'       => '12.000 m',
            'time_in'        => $day . ' 09:00:00',
            'time_out'       => $day . ' 11:00:00',
            'time_out_latitude'  => $latitude,
            'time_out_longitude' => $longitude,
            'location_trail' => $trail,
        ]);
    }

    private function schedule(User $user, Branch $branch, array $attributes = []): UserBranchSchedule
    {
        return UserBranchSchedule::create($attributes + [
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'date'      => self::DAY,
            'source'    => 'activity-plan',
        ]);
    }

    public function test_sign_ins_are_classified_by_distance_from_the_branch_pin(): void
    {
        $rep = $this->user('Rep');
        $branch = $this->branch([14.5600, 121.0200]);

        $this->visit($rep, $branch, 14.5605, 121.0200);         // ~55 m
        $this->visit($rep, $branch, 14.5650, 121.0200);         // ~556 m
        $this->visit($rep, $branch, 14.6000, 121.0200);         // ~4.4 km
        $this->visit($rep, $this->branch(null), 14.56, 121.02); // no pin

        $report = $this->actingAs($this->viewer())
            ->get(route('report.map', ['date_from' => self::DAY, 'date_to' => self::DAY]))
            ->assertOk()
            ->viewData('report');

        $this->assertSame(['on_site', 'near', 'off_site', 'no_pin'], array_column($report['visits'], 'status'));
        $this->assertEqualsWithDelta(556, $report['visits'][1]['distance_m'], 5);
        $this->assertSame(33.3, $report['summary']['on_site_rate']);
        $this->assertSame(1, $report['summary']['off_site']);
    }

    public function test_schedule_coverage_counts_visited_and_missed_branches_as_the_mcp_dashboard_does(): void
    {
        $rep = $this->user('Rep');
        $visited = $this->branch();
        $missed = $this->branch([14.60, 121.05]);
        $onLeave = $this->branch([14.61, 121.06]);
        $unscheduled = $this->branch([14.62, 121.07]);

        $this->schedule($rep, $visited);
        $this->schedule($rep, $missed);
        $this->schedule($rep, $onLeave, ['objective' => 'on-leave']);
        $this->schedule($rep, $visited, ['date' => '2026-10-14', 'status' => 'deviated']);

        $this->visit($rep, $visited, 14.5601, 121.0201);
        $this->visit($rep, $unscheduled, 14.6201, 121.0701);

        $report = $this->actingAs($this->viewer())
            ->get(route('report.map', ['date_from' => '2026-10-14', 'date_to' => self::DAY]))
            ->assertOk()
            ->viewData('report');

        $this->assertSame(2, $report['summary']['scheduled']);
        $this->assertSame(1, $report['summary']['visited']);
        $this->assertSame(1, $report['summary']['missed']);
        $this->assertSame(50.0, $report['summary']['coverage_rate']);
        $this->assertSame(1, $report['summary']['unscheduled']);

        $states = collect($report['branches'])->pluck('state', 'name');
        $this->assertSame('missed', $states[collect($report['branches'])->firstWhere('lat', 14.60)['name']]);
        $this->assertSame(2, collect($report['branches'])->where('state', 'not_counted')->count());

        $row = collect($report['users'])->firstWhere('user', 'Rep Tester');
        $this->assertSame(['scheduled' => 2, 'visited' => 1, 'visits' => 2], ['scheduled' => $row['scheduled'], 'visited' => $row['visited'], 'visits' => $row['visits']]);
    }

    public function test_the_mcp_map_page_draws_the_summary_and_the_visits_table(): void
    {
        $rep = $this->user('Rep');
        $this->visit($rep, $this->branch(), 14.5601, 121.0201);

        $this->actingAs($this->viewer())
            ->get(route('report.map', ['date_from' => self::DAY, 'date_to' => self::DAY]))
            ->assertOk()
            ->assertSee('Schedule coverage')
            ->assertSee('visits-table', false)
            ->assertSee('vendor/highcharts/modules/map.js', false)
            ->assertDontSee('highcharts.com/samples', false);
    }

    public function test_the_mcp_map_asks_for_a_filter_before_loading_anything(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('report.map'))
            ->assertOk()
            ->assertViewHas('report', null)
            ->assertSee('click <b>Filter</b>', false);
    }

    public function test_the_mcp_map_only_shows_a_restricted_viewers_subordinates(): void
    {
        $manager = $this->viewer(['report access', 'report restricted']);
        $managerOrg = OrganizationStructure::factory()->create(['user_id' => $manager->id, 'type' => 'NKAG']);

        $subordinate = $this->user('Subordinate');
        OrganizationStructure::factory()->create(['user_id' => $subordinate->id, 'reports_to_id' => $managerOrg->id, 'type' => 'NKAG']);
        $stranger = $this->user('Stranger');
        OrganizationStructure::factory()->create(['user_id' => $stranger->id, 'type' => 'NKAG']);

        $this->visit($subordinate, $this->branch(), 14.5601, 121.0201);
        $this->visit($stranger, $this->branch(), 14.5601, 121.0201);

        $report = $this->actingAs($manager)
            ->get(route('report.map', ['date_from' => self::DAY, 'date_to' => self::DAY]))
            ->assertOk()
            ->viewData('report');

        $this->assertSame(['Subordinate Tester'], array_column($report['visits'], 'user'));
    }

    public function test_the_maps_report_summarises_each_route(): void
    {
        $rep = $this->user('Rep');
        $trail = [];
        for ($i = 0; $i < 60; $i++) {
            $trail[] = ['latitude' => 14.5600 + ($i % 2) * 0.00003, 'longitude' => 121.0200, 'accuracy' => '10.000 m', 'recorded_at' => self::DAY . ' ' . sprintf('09:%02d:00', $i)];
        }
        $trail[] = ['latitude' => 14.2922, 'longitude' => 120.9998, 'accuracy' => '100000.000 m', 'recorded_at' => self::DAY . ' 10:30:00'];

        $this->visit($rep, $this->branch(), 14.5600, 121.0200, self::DAY, $trail);

        $response = $this->actingAs($this->viewer())
            ->get(route('report.locations', ['date_from' => self::DAY, 'date_to' => self::DAY]))
            ->assertOk()
            ->assertSee('Distance and time by user')
            ->assertSee('Tracking gaps');

        $route = $response->viewData('routes')[0];
        $this->assertSame(120, $route['duration_minutes']);
        $this->assertSame(61, $route['tracked_points'], 'Every reading received, the vague one included.');
        $this->assertSame(1, $route['vague_points']);
        $this->assertLessThan(0.05, $route['distance_km']);
        $this->assertCount(1, $route['stops']);
        $this->assertSame(1, $response->viewData('totals')['routes']);
    }

    public function test_route_data_is_escaped_inside_the_page_script(): void
    {
        $rep = User::factory()->create(['firstname' => '</script><b>x', 'lastname' => 'Tester', 'status' => 'active']);
        $trail = [['latitude' => 14.56, 'longitude' => 121.02, 'accuracy' => 10, 'recorded_at' => self::DAY . ' 09:05:00']];
        $this->visit($rep, $this->branch(), 14.5600, 121.0200, self::DAY, $trail);

        $this->actingAs($this->viewer())
            ->get(route('report.locations', ['date_from' => self::DAY, 'date_to' => self::DAY]))
            ->assertOk()
            ->assertDontSee('</script><b>x', false);
    }

    public function test_thresholds_match_the_page_legend(): void
    {
        $this->assertSame(200, VisitMap::ON_SITE_M);
        $this->assertSame(1000, VisitMap::NEAR_M);
    }
}
