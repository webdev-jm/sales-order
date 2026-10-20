<?php

namespace App\Services\Ai\Tools;

use App\Models\Account;
use App\Services\Ai\SalesFigures;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Throwable;

/**
 * Base for the sales assistant's tools. Every tool only sees the accounts assigned to the user,
 * whatever the model asks for, and answers with JSON.
 */
abstract class SalesTool implements Tool
{
    /**
     * @param array<int, int> $accountIds The user's assigned accounts
     */
    public function __construct(protected SalesFigures $figures, protected array $accountIds)
    {
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function answer(Request $request): array;

    public function handle(Request $request): string
    {
        try {
            return (string) json_encode($this->answer($request), JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            report($e);

            return (string) json_encode(['error' => 'The figures could not be read. Tell the user to try again later.']);
        }
    }

    /**
     * The date range and account filters every sales tool takes.
     *
     * @return array<string, mixed>
     */
    protected function periodSchema(JsonSchema $schema): array
    {
        return [
            'date_from'    => $schema->string()->description('First order date, YYYY-MM-DD. Defaults to the first day of this month.'),
            'date_to'      => $schema->string()->description('Last order date, YYYY-MM-DD. Defaults to today.'),
            'account_code' => $schema->string()->description('Limit to one of the user\'s accounts, by account code. Leave out for all of them.'),
        ];
    }

    /**
     * @return array{date_from: string, date_to: string, account_ids: array<int, int>}
     */
    protected function filters(Request $request): array
    {
        return [
            'date_from'   => $this->date($request['date_from'] ?? null) ?? now()->startOfMonth()->toDateString(),
            'date_to'     => $this->date($request['date_to'] ?? null) ?? now()->toDateString(),
            'account_ids' => $this->accountsFor($request['account_code'] ?? null),
        ];
    }

    /**
     * The user's accounts, or the one asked for when it is theirs. An account that is not theirs
     * gives no accounts, so nothing outside their accounts can be read.
     *
     * @return array<int, int>
     */
    protected function accountsFor(?string $accountCode): array
    {
        if (blank($accountCode)) {
            return $this->accountIds;
        }

        $id = Account::query()->whereIn('id', $this->accountIds)->where('account_code', trim($accountCode))->value('id');

        return $id === null ? [] : [(int) $id];
    }

    protected function limit(mixed $value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    private function date(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
