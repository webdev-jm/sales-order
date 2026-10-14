<?php

namespace Database\Factories;

use App\Models\AiUsageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiUsageLog>
 */
class AiUsageLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $input  = fake()->numberBetween(1000, 5000);
        $output = fake()->numberBetween(100, 2000);

        return [
            'feature'       => AiUsageLog::FEATURE_PO_SCAN,
            'provider'      => 'gemini',
            'model'         => 'gemini-3.5-flash',
            'reference'     => fake()->word() . '.pdf',
            'input_tokens'  => $input,
            'output_tokens' => $output,
            'total_tokens'  => $input + $output,
            'status'        => AiUsageLog::STATUS_SUCCESS,
            'duration_ms'   => fake()->numberBetween(2000, 30000),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => AiUsageLog::STATUS_FAILED,
            'error'  => 'AI service returned an error.',
        ]);
    }
}
