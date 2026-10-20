<?php

namespace App\Services\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class SalesByBrand extends SalesTool
{
    public function description(): string
    {
        return 'Quantity and gross sales per product brand in the user\'s finalized, uploaded sales orders for a period.';
    }

    public function schema(JsonSchema $schema): array
    {
        return $this->periodSchema($schema);
    }

    protected function answer(Request $request): array
    {
        $filters = $this->filters($request);

        return ['period' => [$filters['date_from'], $filters['date_to']], 'brands' => $this->figures->byBrand($filters, 15)];
    }
}
