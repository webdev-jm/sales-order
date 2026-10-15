<?php

namespace Database\Factories;

use App\Services\Ai\Providers\GeminiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiProviderSetting>
 */
class AiProviderSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'       => GeminiProvider::NAME,
            'is_enabled' => true,
            'priority'   => 1,
            'model'      => 'gemini-3.5-flash',
            'effort'     => 'low',
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['is_enabled' => false]);
    }

    /**
     * @param float $input US dollars per million input tokens
     * @param float $output US dollars per million output tokens
     */
    public function priced(float $input, float $output): static
    {
        return $this->state(fn () => ['input_price' => $input, 'output_price' => $output]);
    }
}
