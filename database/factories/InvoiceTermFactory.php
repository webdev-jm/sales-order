<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceTermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'term_code' => $this->faker->unique()->randomNumber(3),
            'description' => $this->faker->sentence(3),
            'discount' => rand(5, 50),
            'discount_days' => rand(7, 30),
            'due_days' => rand(10, 30)
        ];
    }
}
