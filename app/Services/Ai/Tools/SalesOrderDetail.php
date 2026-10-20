<?php

namespace App\Services\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class SalesOrderDetail extends SalesTool
{
    public function description(): string
    {
        return 'One of the user\'s sales orders with its product lines, looked up by control number or PO number.';
    }

    public function schema(JsonSchema $schema): array
    {
        return ['number' => $schema->string()->description('The control number or PO number.')->required()];
    }

    protected function answer(Request $request): array
    {
        $order = $this->figures->orderDetail(trim((string) ($request['number'] ?? '')), ['account_ids' => $this->accountIds]);

        return $order ?? ['error' => 'No sales order with that number was found in the user\'s accounts.'];
    }
}
