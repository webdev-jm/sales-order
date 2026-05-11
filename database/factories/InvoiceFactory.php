<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoiceDate = fake()->dateTimeBetween('-6 months', 'now');
        $invoiceTs = $invoiceDate->getTimestamp();
        $poDate = (clone $invoiceDate)->modify('-'.fake()->numberBetween(0, 7).' days');
        $orderDate = (clone $invoiceDate)->modify('-'.fake()->numberBetween(0, 14).' days');

        return [
            'company' => fake()->randomElement(['BEVI', 'BEVA', 'BIGI']),
            'account_code' => fake()->numerify('ACC#####'),
            'invoice' => fake()->unique()->numerify('INV-#######'),
            'sales_order' => fake()->numerify('SO-#######'),
            'po_number' => fake()->numerify('PO-#######'),
            'order_date' => $orderDate->format('Y-m-d'),
            'invoice_date' => $invoiceDate->format('Y-m-d'),
            'po_date' => $poDate->format('Y-m-d'),
            'currency_value' => fake()->randomFloat(2, 500, 100000),
        ];
    }
}
