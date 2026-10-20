<?php

namespace App\Services\Ai\Agents;

use App\Services\Ai\Concerns\UsesAiSettings;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

/**
 * Writes a short reading of the sales figures shown on a report: what stands out and what to
 * watch. It only sees the figures it is given.
 */
class ReportInsights implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;
    use UsesAiSettings;

    public function instructions(): string
    {
        return implode("\n", [
            'You are a sales analyst at BEVI, a consumer goods supplier in the Philippines, reading figures from its Sales Management System.',
            'You get one report\'s figures as JSON: the period, totals, the same totals for the period before it, and breakdowns by account, product and brand.',
            'Write for a sales manager who has a minute to read it.',
            '',
            'Rules:',
            '- Use only the numbers in the JSON. Never invent figures, causes or targets; if something cannot be told from the data, leave it out.',
            '- Compare with the previous period when it has orders; give changes as percentages rounded to whole numbers.',
            '- Amounts are in Philippine pesos; write them like PHP 1,234,567. Round large amounts to the peso.',
            '- headline: one sentence with the main result.',
            '- highlights: 2 to 5 short points, most important first.',
            '- watch: 0 to 3 short points on drops, concentration on few accounts or products, or anything worth checking. Leave it empty when nothing stands out.',
            '- Plain sentences, no markdown.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'headline'   => $schema->string()->required(),
            'highlights' => $schema->array()->items($schema->string())->required(),
            'watch'      => $schema->array()->items($schema->string())->required(),
        ];
    }

    public function maxTokens(): int
    {
        return 1500;
    }
}
