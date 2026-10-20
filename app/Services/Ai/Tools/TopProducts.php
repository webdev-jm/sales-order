<?php

namespace App\Services\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class TopProducts extends SalesTool
{
    public function description(): string
    {
        return 'The best-selling products in the user\'s finalized, uploaded sales orders for a period, ranked by quantity or by gross sales.';
    }

    public function schema(JsonSchema $schema): array
    {
        return $this->periodSchema($schema) + [
            'rank_by' => $schema->string()->enum(['quantity', 'sales'])->description('Rank by quantity (default) or by gross sales.'),
            'limit'   => $schema->integer()->min(1)->max(25)->description('How many products, 10 by default.'),
        ];
    }

    protected function answer(Request $request): array
    {
        $filters = $this->filters($request);

        return [
            'period'   => [$filters['date_from'], $filters['date_to']],
            'products' => $this->figures->topProducts($filters, $this->limit($request['limit'] ?? null, 10), (string) ($request['rank_by'] ?? 'quantity')),
        ];
    }
}
