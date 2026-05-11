<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrderProduct>
 */
class SalesOrderProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 1, 200);
        $totalSales = fake()->randomFloat(2, 100, 20000);

        return [
            'part' => 1,
            'total_quantity' => $qty,
            'total_sales' => $totalSales,
        ];
    }
}
