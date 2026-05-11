<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $orderDate = fake()->dateTimeBetween('-6 months', 'now');
        $shipDate = fake()->dateTimeBetween($orderDate, '+30 days');
        $qty = fake()->numberBetween(10, 500);
        $totalSales = fake()->randomFloat(2, 500, 50000);

        return [
            'control_number' => fake()->unique()->numerify('SO-#######'),
            'po_number' => fake()->numerify('PO-#######'),
            'paf_number' => fake()->numerify('PAF-#####'),
            'order_date' => $orderDate->format('Y-m-d'),
            'ship_date' => $shipDate->format('Y-m-d'),
            'ship_to_name' => fake()->company(),
            'ship_to_building' => fake()->buildingNumber(),
            'ship_to_street' => fake()->streetName(),
            'ship_to_city' => fake()->city(),
            'ship_to_postal' => fake()->numerify('####'),
            'shipping_instruction' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['draft', 'pending', 'approved', 'cancelled']),
            'total_quantity' => $qty,
            'total_sales' => $totalSales,
            'grand_total' => $totalSales,
            'po_value' => $totalSales,
            'upload_status' => null,
        ];
    }
}
