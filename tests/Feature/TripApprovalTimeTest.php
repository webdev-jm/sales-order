<?php

namespace Tests\Feature;

use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * Every trip approval step shows the time it happened, not only the day or "2 hours ago".
 */
class TripApprovalTimeTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedSettings();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function viewer(): User
    {
        Permission::firstOrCreate(['name' => 'trip access', 'guard_name' => 'web'], ['module' => 'Test', 'description' => 'trip access']);

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo('trip access');

        return $user;
    }

    private function approval(ActivityPlanDetailTrip $trip, string $status, string $at): ActivityPlanDetailTripApproval
    {
        $approval = ActivityPlanDetailTripApproval::factory()->create([
            'activity_plan_detail_trip_id' => $trip->id,
            'user_id'                      => $trip->user_id,
            'status'                       => $status,
        ]);
        $approval->forceFill(['created_at' => Carbon::parse($at), 'updated_at' => Carbon::parse($at)])->save();

        return $approval;
    }

    public function test_trip_approvals_show_their_date_and_time(): void
    {
        $trip = ActivityPlanDetailTrip::factory()->create();
        $approval = $this->approval($trip, 'submitted', '2026-11-03 14:05:09');

        $this->actingAs($this->viewer())
            ->get(route('trip.show', $trip->id))
            ->assertOk()
            ->assertSee('2026-11-03 14:05:09 pm')
            ->assertDontSee($approval->fresh()->created_at->diffForHumans());
    }

    public function test_the_trip_detail_page_shows_each_signatory_with_a_twelve_hour_time(): void
    {
        $trip = ActivityPlanDetailTrip::factory()->create(['status' => 'approved by imm. superior']);
        $this->approval($trip, 'submitted', '2026-11-03 14:05:00');
        $this->approval($trip, 'approved by imm. superior', '2026-11-04 09:30:00');

        $this->actingAs($this->viewer())
            ->get(route('trip.user-trip', encrypt($trip->id)))
            ->assertOk()
            ->assertSeeInOrder(['SUBMITTED BY', 'Nov 3, 2026 2:05 PM', 'SUPERVISOR', 'Nov 4, 2026 9:30 AM'])
            ->assertDontSee('14:05:00 pm');
    }

    public function test_the_trip_pdf_shows_each_signatory_with_a_twelve_hour_time(): void
    {
        $trip = ActivityPlanDetailTrip::factory()->create(['status' => 'approved by imm. superior']);
        $this->approval($trip, 'submitted', '2026-11-03 14:05:00');
        $this->approval($trip, 'approved by imm. superior', '2026-11-04 09:30:00');

        $html = view('pages.mcp.trip-detail', [
            'trip'         => $trip->fresh(),
            'bar_code'     => '',
            'status_arr'   => ['approved by imm. superior' => 'primary'],
            'destinations' => collect(),
        ])->render();

        $this->assertStringContainsString('Nov 3, 2026 2:05 PM', $html);
        $this->assertStringContainsString('Nov 4, 2026 9:30 AM', $html);
        $this->assertStringNotContainsString('14:05:00 pm', $html);
    }
}
