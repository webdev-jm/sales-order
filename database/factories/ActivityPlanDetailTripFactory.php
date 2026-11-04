<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityPlanDetailTrip>
 */
class ActivityPlanDetailTripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'trip_number'         => 'TRP-' . $this->faker->unique()->numerify('########'),
            'from'                => strtoupper($this->faker->city()),
            'to'                  => strtoupper($this->faker->city()),
            'departure'           => now()->addWeek()->toDateString(),
            'trip_type'           => 'one_way',
            'transportation_type' => 'AIR',
            'passenger'           => 1,
            'purpose'             => $this->faker->sentence(),
            'status'              => 'submitted',
            'source'              => 'trips',
        ];
    }
}
