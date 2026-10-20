<?php

namespace App\Services\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class SalesSummary extends SalesTool
{
    public function description(): string
    {
        return 'Totals of the user\'s sales orders in a period: number of orders, quantity, gross and net sales (orders count once finalized and uploaded to SYSPRO), the orders per status, and the accounts with the highest net sales.';
    }

    public function schema(JsonSchema $schema): array
    {
        return $this->periodSchema($schema);
    }

    protected function answer(Request $request): array
    {
        $filters = $this->filters($request);

        return [
            'period'     => [$filters['date_from'], $filters['date_to']],
            'totals'     => $this->figures->summary($filters),
            'by_status'  => $this->figures->byStatus($filters),
            'by_account' => $this->figures->byAccount($filters, 10),
        ];
    }
}
