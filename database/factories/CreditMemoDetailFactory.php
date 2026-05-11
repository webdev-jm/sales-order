<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditMemoDetail>
 */
class CreditMemoDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uoms = ['CTN', 'CS', 'BOX', 'PCS', 'EA'];
        $orderUom = fake()->randomElement($uoms);
        $qty = fake()->numberBetween(1, 100);
        $price = fake()->randomFloat(2, 50, 5000);

        return [
            'credit_note_number' => fake()->optional()->numerify('CN-#######'),
            'warehouse' => fake()->optional()->randomElement(['WH01', 'WH02', 'WH03']),
            'order_quantity' => $qty,
            'order_uom' => $orderUom,
            'price' => $price,
            'price_uom' => $orderUom,
            'unit_cost' => fake()->randomFloat(2, 10, $price),
            'ship_quantity' => $qty,
            'stock_quantity_to_ship' => $qty,
            'line_ship_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+14 days')->format('Y-m-d') : null,
            'stocking_uom' => 'EA',
        ];
    }
}
