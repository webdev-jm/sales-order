<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyActivityReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $dateFrom = fake()->dateTimeBetween('-3 months', 'now');
        $dateTo = (clone $dateFrom)->modify('+6 days');
        $dateSubmitted = fake()->optional(0.8)->dateTimeBetween($dateFrom, '+2 days');

        return [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'accounts_visited' => fake()->optional()->numberBetween(1, 15),
            'week_number' => (int) $dateFrom->format('W'),
            'date_submitted' => $dateSubmitted ? $dateSubmitted->format('Y-m-d') : null,
            'objectives' => fake()->paragraph(),
            'highlights' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['draft', 'submitted', 'approved', 'rejected']),
        ];
    }
}
