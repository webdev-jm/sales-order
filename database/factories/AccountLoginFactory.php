<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccountLoginFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $timeIn = fake()->dateTimeBetween('-30 days', 'now');
        $timeOut = fake()->optional(0.7)->dateTimeBetween($timeIn, '+6 hours');

        return [
            'longitude' => fake()->longitude(116, 126),
            'latitude' => fake()->latitude(5, 20),
            'accuracy' => (string) fake()->randomFloat(2, 5, 50),
            'activities' => fake()->optional()->sentence(),
            'time_in' => $timeIn->format('Y-m-d H:i:s'),
            'time_out' => $timeOut ? $timeOut->format('Y-m-d H:i:s') : null,
        ];
    }
}
