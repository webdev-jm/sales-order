<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'discount_code' => $this->faker->unique()->randomNumber(3),
            'description' => $this->faker->sentence(3),
            'discount_1' => rand(5, 50),
            'discount_2' => rand(5, 30),
            'discount_3' => rand(5, 15),
        ];
    }
}
