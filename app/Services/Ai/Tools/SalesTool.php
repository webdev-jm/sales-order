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

    /**
     * What one tool call read, in words a sales clerk can check, such as
     * "Sales summary · Nov 1–4, 2026 · all your accounts". Shown under the assistant's answer.
     *
     * @param array<string, mixed> $arguments
     */
    public static function describeCall(string $toolName, array $arguments): string
    {
        $label = match ($toolName) {
            'ListMyAccounts'   => 'Your accounts',
            'SalesSummary'     => 'Sales summary',
            'TopProducts'      => 'Top products',
            'SalesByBrand'     => 'Sales by brand',
            'FindSalesOrders'  => 'Order search',
            'SalesOrderDetail' => 'Order details',
            default            => $toolName,
        };

        if ($toolName === 'ListMyAccounts') {
            return $label;
        }

        if ($toolName === 'SalesOrderDetail') {
            return $label . ' · ' . trim((string) ($arguments['number'] ?? ''));
        }

        $parts = [$label];

        foreach (['po_number' => 'PO ', 'control_number' => '', 'status' => ''] as $key => $prefix) {
            if (filled($arguments[$key] ?? null)) {
                $parts[] = $prefix . trim((string) $arguments[$key]);
            }
        }

        $searchesByNumber = filled($arguments['po_number'] ?? null) || filled($arguments['control_number'] ?? null);

        if (! $searchesByNumber || filled($arguments['date_from'] ?? null)) {
            $parts[] = self::periodLabel($arguments['date_from'] ?? null, $arguments['date_to'] ?? null);
        }

        $parts[] = filled($arguments['account_code'] ?? null) ? 'account ' . trim((string) $arguments['account_code']) : 'all your accounts';

        return implode(' · ', $parts);
    }

    private static function periodLabel(mixed $from, mixed $to): string
    {
        try {
            $start = filled($from) ? Carbon::parse((string) $from) : now()->startOfMonth();
            $end = filled($to) ? Carbon::parse((string) $to) : now();
        } catch (Throwable) {
            return 'this month so far';
        }

        if ($start->isSameMonth($end)) {
            return $start->format('M j') . ($start->isSameDay($end) ? '' : '–' . $end->format('j')) . ', ' . $end->format('Y');
        }

        return $start->format('M j, Y') . ' – ' . $end->format('M j, Y');
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
