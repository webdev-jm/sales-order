<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PPUFormItem>
 */
class PPUFormItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 1, 100);

        return [
            'rtv_number' => fake()->unique()->numerify('RTV-#######'),
            'rtv_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'branch_name' => fake()->company().' - '.fake()->city(),
            'total_quantity' => $qty,
            'total_amount' => fake()->randomFloat(2, 100, 50000),
            'remarks' => fake()->sentence(),
        ];
    }
}
