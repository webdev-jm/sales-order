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

    public function test_trip_approvals_show_their_date_and_time(): void
    {
        $trip = ActivityPlanDetailTrip::factory()->create();
        $approval = ActivityPlanDetailTripApproval::factory()->create([
            'activity_plan_detail_trip_id' => $trip->id,
            'user_id'                      => $trip->user_id,
            'status'                       => 'submitted',
        ]);
        $approval->forceFill(['created_at' => Carbon::parse('2026-11-03 14:05:09')])->save();

        $this->actingAs($this->viewer())
            ->get(route('trip.show', $trip->id))
            ->assertOk()
            ->assertSee('2026-11-03 14:05:09 pm')
            ->assertDontSee($approval->fresh()->created_at->diffForHumans());
    }
}
