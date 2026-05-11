<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PPUForm>
 */
class PPUFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $datePrepared = fake()->dateTimeBetween('-3 months', '-7 days');
        $dateSubmitted = fake()->dateTimeBetween($datePrepared, '+3 days');
        $pickupDate = fake()->dateTimeBetween($dateSubmitted, '+14 days');
        $qty = fake()->numberBetween(5, 200);

        return [
            'control_number' => fake()->unique()->numerify('PPU-#######'),
            'date_prepared' => $datePrepared->format('Y-m-d'),
            'date_submitted' => $dateSubmitted->format('Y-m-d'),
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'submitted', 'approved', 'cancelled']),
            'total_quantity' => $qty,
            'total_amount' => fake()->randomFloat(2, 1000, 100000),
            'upload_status' => null,
        ];
    }
}
