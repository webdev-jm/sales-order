<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PriceCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'code' => fake()->randomElement(['P1', 'P2', 'P3', 'P4']),
            'selling_price' => fake()->randomFloat(3, 50, 5000),
            'price_basis' => fake()->randomElement(['S', 'A', 'O']),
        ];
    }
}
