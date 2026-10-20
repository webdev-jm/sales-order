<?php

namespace App\Services\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class ListMyAccounts extends SalesTool
{
    public function description(): string
    {
        return 'Lists the customer accounts assigned to the user, with their account codes. Use it to find the code of an account the user names.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    protected function answer(Request $request): array
    {
        return ['accounts' => \App\Models\Account::query()
            ->whereIn('id', $this->accountIds)
            ->orderBy('account_code')
            ->get(['account_code', 'account_name', 'short_name'])
            ->map(fn ($account): array => ['account_code' => $account->account_code, 'account_name' => $account->account_name, 'short_name' => $account->short_name])
            ->all()];
    }
}
