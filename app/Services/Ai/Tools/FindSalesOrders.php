<?php

namespace App\Services\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class FindSalesOrders extends SalesTool
{
    public function description(): string
    {
        return 'Finds the user\'s sales orders of any status by PO number, control number, status or period, newest first. Each order says whether it was uploaded to SYSPRO.';
    }

    public function schema(JsonSchema $schema): array
    {
        return $this->periodSchema($schema) + [
            'po_number'      => $schema->string()->description('Part of a PO number.'),
            'control_number' => $schema->string()->description('Part of a sales order control number.'),
            'status'         => $schema->string()->enum(['draft', 'finalized', 'cancelled'])->description('Only orders with this status.'),
            'limit'          => $schema->integer()->min(1)->max(25)->description('How many orders, 10 by default.'),
        ];
    }

    protected function answer(Request $request): array
    {
        $filters = $this->filters($request);

        if (filled($request['po_number'] ?? null) || filled($request['control_number'] ?? null)) {
            $filters['date_from'] = filled($request['date_from'] ?? null) ? $filters['date_from'] : null;
        }

        return ['orders' => $this->figures->findOrders($filters + [
            'po_number'      => $request['po_number'] ?? null,
            'control_number' => $request['control_number'] ?? null,
            'status'         => $request['status'] ?? null,
        ], $this->limit($request['limit'] ?? null, 10))];
    }
}
