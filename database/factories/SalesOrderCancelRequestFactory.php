<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\SalesOrderCancelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrderCancelRequest>
 */
class SalesOrderCancelRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => 'CR-' . now()->format('Ym') . '-' . fake()->unique()->numerify('9###'),
            'sales_order_id' => SalesOrder::factory()->state([
                'status'        => 'finalized',
                'upload_status' => 1,
                'reference'     => fake()->numerify('######'),
            ]),
            'user_id'        => User::factory(),
            'reason'         => fake()->sentence(),
            'status'         => SalesOrderCancelRequest::STATUS_PENDING,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status'         => SalesOrderCancelRequest::STATUS_IN_PROGRESS,
            'processed_by'   => User::factory(),
            'in_progress_at' => now(),
        ]);
    }
}
