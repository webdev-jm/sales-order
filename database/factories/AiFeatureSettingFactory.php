<?php

namespace Database\Factories;

use App\Models\AiUsageLog;
use App\Services\Ai\Providers\GeminiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiFeatureSetting>
 */
class AiFeatureSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feature'   => AiUsageLog::FEATURE_INSIGHTS,
            'providers' => [
                ['name' => GeminiProvider::NAME, 'model' => 'gemini-3.5-flash-lite', 'input_price' => null, 'output_price' => null],
            ],
        ];
    }

    public function forFeature(string $feature): static
    {
        return $this->state(fn () => ['feature' => $feature]);
    }

    /**
     * @param array<int, array{0: string, 1?: string|null, 2?: float|null, 3?: float|null}> $steps provider, model, input price, output price
     */
    public function steps(array $steps): static
    {
        return $this->state(fn () => ['providers' => array_map(fn (array $step): array => [
            'name'         => $step[0],
            'model'        => $step[1] ?? null,
            'input_price'  => $step[2] ?? null,
            'output_price' => $step[3] ?? null,
        ], $steps)]);
    }
}
