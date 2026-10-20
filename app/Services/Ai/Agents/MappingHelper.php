<?php

namespace App\Services\Ai\Agents;

use App\Services\Ai\Concerns\UsesAiSettings;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

/**
 * Picks, for each unmatched upload line, the catalogue product or ship-to address it most likely
 * means, from a short list of candidates.
 */
class MappingHelper implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;
    use UsesAiSettings;

    public function instructions(): string
    {
        return implode("\n", [
            'You match purchase-order lines from retail accounts to BEVI\'s own records.',
            'For each product item you get the account\'s SKU and description and a list of BEVI products (code and name).',
            'For each store item you get the account\'s store code and name and a list of that account\'s ship-to addresses (code and name).',
            '',
            'Rules:',
            '- Choose only a code from the item\'s own candidates. If none of them is clearly the same product or store, give an empty code.',
            '- For products, the brand, variant, size and pack must agree. A different size or flavour is not a match.',
            '- confidence: high when the names agree on everything that matters, medium when one detail is unclear, low otherwise.',
            '- reason: one short sentence a sales clerk can check, such as "Same brand, scent and 400 ml size".',
            '- Answer every item, using its item number.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        $answer = fn (string $codeKey) => $schema->array()->items($schema->object([
            'item'       => $schema->integer()->required(),
            $codeKey     => $schema->string()->required(),
            'confidence' => $schema->string()->enum(['high', 'medium', 'low'])->required(),
            'reason'     => $schema->string()->required(),
        ])->withoutAdditionalProperties())->required();

        return [
            'products' => $answer('stock_code'),
            'stores'   => $answer('address_code'),
        ];
    }

    public function maxTokens(): int
    {
        return 3000;
    }
}
