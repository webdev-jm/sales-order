<?php

namespace Tests\Feature;

use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Every trip approval step shows the time it happened, not only the day or "2 hours ago".
 */
class TripApprovalTimeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private ActivityPlanDetailTrip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'trip access', 'guard_name' => 'web'], ['module' => 'Trip', 'description' => 'trip access']);

        $this->user = User::factory()->create(['status' => 'active']);
        $this->user->givePermissionTo('trip access');

        $this->trip = ActivityPlanDetailTrip::create([
            'user_id'             => $this->user->id,
            'trip_number'         => 'TRIP-TEST-' . uniqid(),
            'from'                => 'MNL',
            'to'                  => 'CEB',
            'departure'           => '2026-11-20',
            'return'              => '2026-11-22',
            'trip_type'           => 'round_trip',
            'transportation_type' => 'AIR',
            'passenger'           => 1,
            'purpose'             => 'Store visits',
            'status'              => 'approved by imm. superior',
            'source'              => 'trip-add',
        ]);

        $this->approval('submitted', '2026-11-03 14:05:00');
        $this->approval('approved by imm. superior', '2026-11-04 09:30:00');
    }

    private function approval(string $status, string $at): void
    {
        $approval = new ActivityPlanDetailTripApproval([
            'user_id'                      => $this->user->id,
            'activity_plan_detail_trip_id' => $this->trip->id,
            'status'                       => $status,
        ]);
        $approval->created_at = Carbon::parse($at);
        $approval->updated_at = Carbon::parse($at);
        $approval->save();
    }

    public function test_the_trip_page_timeline_shows_the_time_of_each_step(): void
    {
        $this->actingAs($this->user)
            ->get(route('trip.show', $this->trip->id))
            ->assertOk()
            ->assertSeeInOrder(['Nov 4 2026', '9:30 AM', 'Nov 3 2026', '2:05 PM']);
    }

    public function test_the_trip_detail_page_shows_each_signatory_with_a_twelve_hour_time(): void
    {
        $this->actingAs($this->user)
            ->get(route('trip.user-trip', encrypt($this->trip->id)))
            ->assertOk()
            ->assertSeeInOrder(['SUBMITTED BY', 'Nov 3, 2026 2:05 PM', 'SUPERVISOR', 'Nov 4, 2026 9:30 AM'])
            ->assertDontSee('14:05:00 pm');
    }

    public function test_the_trip_pdf_shows_each_signatory_with_a_twelve_hour_time(): void
    {
        $html = view('pages.mcp.trip-detail', [
            'trip'         => $this->trip,
            'bar_code'     => '',
            'status_arr'   => ['approved by imm. superior' => 'primary'],
            'destinations' => collect(),
        ])->render();

        $this->assertStringContainsString('Nov 3, 2026 2:05 PM', $html);
        $this->assertStringContainsString('Nov 4, 2026 9:30 AM', $html);
        $this->assertStringNotContainsString('14:05:00 pm', $html);
    }
}
