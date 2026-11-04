<?php

namespace Database\Factories;

use App\Models\ActivityPlanDetailTrip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityPlanDetailTripApproval>
 */
class ActivityPlanDetailTripApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'                      => User::factory(),
            'activity_plan_detail_trip_id' => ActivityPlanDetailTrip::factory(),
            'status'                       => 'submitted',
            'remarks'                      => null,
        ];
    }
}
