<?php

namespace App\Services\Ai;

use App\Exceptions\AiUnavailableException;
use App\Models\Account;
use App\Models\AiUsageLog;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Services\Ai\Agents\MappingHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Suggests the catalogue product for SKUs, and the ship-to address for store codes, that an SO
 * Template upload could not match. Candidates are picked from the database first; the AI only
 * chooses among them, so every suggestion is a real product or address of the right account.
 */
class MappingSuggester
{
    /** Most unmatched SKUs and stores sent in one request. */
    public const MAX_ITEMS = 15;

    private const CANDIDATES = 8;

    /** Words that say nothing about which product a line is. */
    private const STOP_WORDS = ['the', 'and', 'for', 'with', 'pcs', 'pack', 'box', 'case', 'new', 'per'];

    /** @var array<int, Account|null> Accounts looked up while building one request */
    private array $accounts = [];

    public function __construct(private AgentRunner $runner)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $rows The template rows held in the session
     * @param array<int, int> $allowedAccountIds Accounts the user may add mappings for
     * @return array{products: array<int, array<string, mixed>>, stores: array<int, array<string, mixed>>}
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function suggest(array $rows, ?int $fallbackAccountId, array $allowedAccountIds, User $user): array
    {
        $products = $this->unmatchedProducts($rows, $fallbackAccountId, $allowedAccountIds);
        $stores = $this->unmatchedStores($rows, $fallbackAccountId, $allowedAccountIds);

        if ($products === [] && $stores === []) {
            return ['products' => [], 'stores' => []];
        }

        $response = $this->runner->prompt(
            new MappingHelper,
            "Match these unmatched upload lines:\n" . json_encode([
                'products' => array_map(fn (array $item): array => array_intersect_key($item, array_flip(['item', 'account', 'sku', 'description', 'candidates'])), $products),
                'stores'   => array_map(fn (array $item): array => array_intersect_key($item, array_flip(['item', 'account', 'store_code', 'store_name', 'candidates'])), $stores),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            AiUsageLog::FEATURE_MAPPING,
            $user,
            $fallbackAccountId,
            'SO Template: ' . \count($products) . ' SKUs, ' . \count($stores) . ' stores',
        );

        return [
            'products' => $this->accept($products, (array) ($response['products'] ?? []), 'stock_code'),
            'stores'   => $this->accept($stores, (array) ($response['stores'] ?? []), 'address_code'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $allowedAccountIds
     * @return array<int, array<string, mixed>>
     */
    public function unmatchedProducts(array $rows, ?int $fallbackAccountId, array $allowedAccountIds): array
    {
        $items = [];

        foreach ($rows as $row) {
            $sku = trim((string) ($row['raw_sku'] ?? ''));
            $account = $this->accountFor($row, $fallbackAccountId, $allowedAccountIds);

            if (filled($row['product_id'] ?? null) || $sku === '' || $account === null) {
                continue;
            }

            $key = $account->id . '|' . $sku;

            if (isset($items[$key]) || \count($items) >= self::MAX_ITEMS) {
                continue;
            }

            $description = trim((string) ($row['description'] ?? ''));

            $items[$key] = [
                'item'        => \count($items) + 1,
                'account_id'  => $account->id,
                'account'     => $account->account_code . ' ' . $account->short_name,
                'sku'         => $sku,
                'description' => $description,
                'candidates'  => $this->productCandidates($description),
            ];
        }

        return array_values(array_filter($items, fn (array $item): bool => $item['candidates'] !== []));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $allowedAccountIds
     * @return array<int, array<string, mixed>>
     */
    public function unmatchedStores(array $rows, ?int $fallbackAccountId, array $allowedAccountIds): array
    {
        $items = [];

        foreach ($rows as $row) {
            $code = trim((string) ($row['store_code'] ?? ''));
            $name = trim((string) ($row['store_name'] ?? ''));
            $account = $this->accountFor($row, $fallbackAccountId, $allowedAccountIds);

            if (filled($row['shipping_address_id'] ?? null) || ($code === '' && $name === '') || $account === null) {
                continue;
            }

            $key = $account->id . '|' . $code . '|' . $name;

            if (isset($items[$key]) || \count($items) >= self::MAX_ITEMS) {
                continue;
            }

            $items[$key] = [
                'item'       => \count($items) + 1,
                'account_id' => $account->id,
                'account'    => $account->account_code . ' ' . $account->short_name,
                'store_code' => $code,
                'store_name' => $name,
                'candidates' => $this->addressCandidates($account->id, $name !== '' ? $name : $code),
            ];
        }

        return array_values(array_filter($items, fn (array $item): bool => $item['candidates'] !== []));
    }

    /**
     * The row's account: the one its address matched, the one a PDF scan was read for, or the
     * single account chosen for the upload. Only accounts the user may map are returned.
     *
     * @param array<string, mixed> $row
     * @param array<int, int> $allowedAccountIds
     */
    private function accountFor(array $row, ?int $fallbackAccountId, array $allowedAccountIds): ?Account
    {
        $id = filled($row['account_id'] ?? null) ? (int) $row['account_id'] : null;

        if ($id === null && filled($row['account_code'] ?? null)) {
            $id = Account::where('account_code', $row['account_code'])->value('id');
        }

        $id ??= $fallbackAccountId;

        if ($id === null || !\in_array((int) $id, $allowedAccountIds, true)) {
            return null;
        }

        return $this->accounts[(int) $id] ??= Account::find($id, ['id', 'account_code', 'short_name']);
    }

    /**
     * Products whose description shares the most words with the line.
     *
     * @return array<int, array{code: string, name: string}>
     */
    private function productCandidates(string $description): array
    {
        $words = $this->words($description);

        if ($words === []) {
            return [];
        }

        return Product::query()
            ->where(function ($query) use ($words) {
                foreach (\array_slice($words, 0, 4) as $word) {
                    $query->orWhere('description', 'like', '%' . $word . '%');
                }
            })
            ->limit(80)
            ->get(['stock_code', 'description', 'size', 'brand'])
            ->map(fn (Product $product): array => [
                'code'  => (string) $product->stock_code,
                'name'  => trim($product->brand . ' ' . $product->description . ' ' . $product->size),
                'score' => $this->score($words, $product->brand . ' ' . $product->description . ' ' . $product->size),
            ])
            ->sortByDesc('score')
            ->take(self::CANDIDATES)
            ->map(fn (array $candidate): array => ['code' => $candidate['code'], 'name' => $candidate['name']])
            ->values()
            ->all();
    }

    /**
     * The account's addresses whose name shares the most words with the store.
     *
     * @return array<int, array{code: string, name: string}>
     */
    private function addressCandidates(int $accountId, string $storeName): array
    {
        $words = $this->words($storeName);

        return ShippingAddress::query()
            ->where('account_id', $accountId)
            ->limit(500)
            ->get(['address_code', 'ship_to_name', 'building', 'street', 'city'])
            ->map(fn (ShippingAddress $address): array => [
                'code'  => (string) $address->address_code,
                'name'  => trim($address->ship_to_name . ', ' . implode(', ', array_filter([$address->building, $address->street, $address->city])), ', '),
                'score' => $this->score($words, $address->ship_to_name . ' ' . $address->building . ' ' . $address->city),
            ])
            ->filter(fn (array $candidate): bool => $candidate['score'] > 0)
            ->sortByDesc('score')
            ->take(self::CANDIDATES)
            ->map(fn (array $candidate): array => ['code' => $candidate['code'], 'name' => $candidate['name']])
            ->values()
            ->all();
    }

    /**
     * Keeps only the AI's choices that are one of the item's candidates.
     *
     * @param array<int, array<string, mixed>> $items
     * @param array<int, mixed> $choices
     * @return array<int, array<string, mixed>>
     */
    private function accept(array $items, array $choices, string $codeKey): array
    {
        $byItem = collect($choices)->filter(fn ($choice): bool => \is_array($choice))->keyBy(fn (array $choice): int => (int) ($choice['item'] ?? 0));
        $accepted = [];

        foreach ($items as $item) {
            $choice = $byItem->get($item['item']);
            $code = trim((string) ($choice[$codeKey] ?? ''));
            $candidate = collect($item['candidates'])->firstWhere('code', $code);

            if ($candidate === null) {
                continue;
            }

            $accepted[] = Arr::except($item, ['candidates', 'item']) + [
                'code'       => $candidate['code'],
                'name'       => $candidate['name'],
                'confidence' => \in_array($choice['confidence'] ?? '', ['high', 'medium', 'low'], true) ? $choice['confidence'] : 'low',
                'reason'     => Str::limit(trim((string) ($choice['reason'] ?? '')), 200),
            ];
        }

        return $accepted;
    }

    /**
     * @return array<int, string>
     */
    private function words(string $text): array
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_filter($words, fn (string $word): bool => \strlen($word) >= 3 && !\in_array($word, self::STOP_WORDS, true));

        usort($words, fn (string $a, string $b): int => \strlen($b) <=> \strlen($a));

        return array_values(array_unique($words));
    }

    /**
     * @param array<int, string> $words
     */
    private function score(array $words, string $text): int
    {
        $haystack = strtolower($text);

        return (new Collection($words))->sum(fn (string $word): int => str_contains($haystack, $word) ? \strlen($word) : 0);
    }
}
