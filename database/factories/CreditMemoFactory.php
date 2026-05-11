<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditMemo>
 */
class CreditMemoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cmDate = fake()->dateTimeBetween('-6 months', 'now');
        $shipDate = fake()->dateTimeBetween($cmDate, '+7 days');

        return [
            'invoice_number' => fake()->unique()->numerify('INV-#######'),
            'po_number' => fake()->numerify('PO-#######'),
            'so_number' => fake()->numerify('SO-#######'),
            'year' => (int) $cmDate->format('Y'),
            'month' => (int) $cmDate->format('m'),
            'cm_date' => $cmDate->format('Y-m-d'),
            'ship_date' => $shipDate->format('Y-m-d'),
            'ship_code' => fake()->optional()->lexify('SH-???'),
            'ship_name' => fake()->optional()->company(),
            'shipping_instruction' => fake()->optional()->sentence(),
            'ship_address1' => fake()->buildingNumber().' '.fake()->streetName(),
            'ship_address2' => fake()->city(),
            'ship_address3' => fake()->optional()->stateAbbr(),
            'ship_address4' => null,
            'ship_address5' => null,
            'status' => fake()->randomElement(['pending', 'approved', 'cancelled']),
        ];
    }
}
