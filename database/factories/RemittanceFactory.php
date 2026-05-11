<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Remittance>
 */
class RemittanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => fake()->boolean(70) ? fake()->numerify('REF-########') : null,
            'payment_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'payment_type' => fake()->randomElement(['CASH', 'CHECK', 'ONLINE TRANSFER', 'PDC']),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
