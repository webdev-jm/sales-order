<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_code' => $this->faker->unique()->randomNumber(7),
            'account_name' => $this->faker->sentence(3),
            'short_name' => $this->faker->text(10)
        ];
    }
}
