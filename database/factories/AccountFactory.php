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
    public function definition(): array
    {
        return [
            'account_code' => fake()->unique()->numerify('ACC#####'),
            'account_name' => fake()->company(),
            'short_name' => strtoupper(fake()->lexify('???')),
            'line_discount_code' => fake()->optional()->randomElement(['LD01', 'LD02', 'LD03']),
            'price_code' => fake()->optional()->randomElement(['P1', 'P2', 'P3', 'P4']),
            'ship_to_address1' => fake()->buildingNumber().' '.fake()->streetName(),
            'ship_to_address2' => fake()->secondaryAddress(),
            'ship_to_address3' => fake()->city(),
            'postal_code' => fake()->numerify('####'),
            'tax_number' => fake()->optional()->numerify('###-###-###-###'),
            'on_hold' => false,
            'sales_order_uom' => fake()->randomElement(['CTN', 'CS', 'BOX']),
            'po_process_date' => fake()->numberBetween(1, 28),
        ];
    }
}
