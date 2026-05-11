<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = fake()->dateTimeBetween('-6 months', '+3 months');

        return [
            'month' => $date->format('m'),
            'year' => (int) $date->format('Y'),
            'objectives' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'submitted', 'approved', 'rejected']),
        ];
    }
}
