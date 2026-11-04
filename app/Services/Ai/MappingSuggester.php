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
use Illuminate\Support\Facades\Cache;
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

    /** Longest words of a description that are searched for. */
    private const SEARCH_WORDS = 6;

    /** Words that say nothing about which product a line is. */
    private const STOP_WORDS = ['the', 'and', 'for', 'with', 'pcs', 'pack', 'box', 'case', 'new', 'per'];

    /** @var array<int, Account|null> Accounts looked up while building one request */
    private array $accounts = [];

    /** @var array<int, string> Keys of the unmatched SKUs and stores sent in this request */
    private array $asked = [];

    /** @var array{products: int, stores: int} Unmatched SKUs and stores over MAX_ITEMS, left for the next request */
    private array $leftOut = ['products' => 0, 'stores' => 0];

    /** Suggestions for the same unmatched lines and candidates are reused for this long. */
    private const CACHE_HOURS = 24;

    public function __construct(private AgentRunner $runner, private AiUsageMeter $meter)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $rows The template rows held in the session
     * @param array<int, int> $allowedAccountIds Accounts the user may add mappings for
     * @param array<int, string> $alreadyAsked Keys returned as "asked" by earlier requests, which are not sent again
     * @return array{products: array<int, array<string, mixed>>, stores: array<int, array<string, mixed>>, asked: array<int, string>, left_out: array{products: int, stores: int}}
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function suggest(array $rows, ?int $fallbackAccountId, array $allowedAccountIds, User $user, array $alreadyAsked = []): array
    {
        $this->asked = [];
        $products = $this->unmatchedProducts($rows, $fallbackAccountId, $allowedAccountIds, $alreadyAsked);
        $stores = $this->unmatchedStores($rows, $fallbackAccountId, $allowedAccountIds, $alreadyAsked);
        $progress = ['asked' => $this->asked, 'left_out' => $this->leftOut];

        if ($products === [] && $stores === []) {
            return ['products' => [], 'stores' => []] + $progress;
        }

        $prompt = "Match these unmatched upload lines:\n" . json_encode([
            'products' => array_map(fn (array $item): array => array_intersect_key($item, array_flip(['item', 'account', 'sku', 'description', 'candidates'])), $products),
            'stores'   => array_map(fn (array $item): array => array_intersect_key($item, array_flip(['item', 'account', 'store_code', 'store_name', 'candidates'])), $stores),
        ], JSON_UNESCAPED_UNICODE);
        $reference = 'SO Template: ' . \count($products) . ' SKUs, ' . \count($stores) . ' stores';

        // Asking again for the same lines and candidates (another click, the same upload re-checked) reuses the
        // earlier answer. A product or store added since changes the candidates, so it asks again.
        $cacheKey = 'ai-mapping:' . hash('sha256', (new MappingHelper)->instructions() . $prompt);

        if (\is_array($saved = Cache::get($cacheKey))) {
            $this->meter->record(['user_id' => $user->id, 'account_id' => $fallbackAccountId, 'feature' => AiUsageLog::FEATURE_MAPPING, 'reference' => $reference, 'status' => AiUsageLog::STATUS_CACHED],
                new AiResponse('', (string) ($saved['provider'] ?? ''), (string) ($saved['model'] ?? ''), 0, 0, 0, 0));
            $answer = $saved['answer'];
        } else {
            $response = $this->runner->prompt(new MappingHelper, $prompt, AiUsageLog::FEATURE_MAPPING, $user, $fallbackAccountId, $reference);
            $answer = ['products' => (array) ($response['products'] ?? []), 'stores' => (array) ($response['stores'] ?? [])];

            Cache::put($cacheKey, [
                'answer'   => $answer,
                'provider' => Str::after((string) $response->meta->provider, AiClient::LARAVEL_AI_PREFIX),
                'model'    => $response->meta->model,
            ], now()->addHours(self::CACHE_HOURS));
        }

        return [
            'products' => $this->accept($products, $answer['products'], 'stock_code'),
            'stores'   => $this->accept($stores, $answer['stores'], 'address_code'),
        ] + $progress;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $allowedAccountIds
     * @param array<int, string> $alreadyAsked
     * @return array<int, array<string, mixed>>
     */
    public function unmatchedProducts(array $rows, ?int $fallbackAccountId, array $allowedAccountIds, array $alreadyAsked = []): array
    {
        $items = [];
        $seen = [];
        $this->leftOut['products'] = 0;

        foreach ($rows as $row) {
            $sku = trim((string) ($row['raw_sku'] ?? ''));
            $account = $this->accountFor($row, $fallbackAccountId, $allowedAccountIds);

            if (filled($row['product_id'] ?? null) || $sku === '' || $account === null) {
                continue;
            }

            $key = 'product|' . $account->id . '|' . $sku;

            if (isset($seen[$key]) || \in_array($key, $alreadyAsked, true)) {
                continue;
            }

            $seen[$key] = true;

            if (\count($items) >= self::MAX_ITEMS) {
                $this->leftOut['products']++;

                continue;
            }

            $description = trim((string) ($row['description'] ?? ''));
            $this->asked[] = $key;

            $items[$key] = [
                'item'        => \count($items) + 1,
                'account_id'  => $account->id,
                'account'     => $account->account_code . ' ' . $account->short_name,
                'sku'         => $sku,
                'description' => $description,
                'candidates'  => $this->productCandidates($description, $account),
            ];
        }

        return array_values(array_filter($items, fn (array $item): bool => $item['candidates'] !== []));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $allowedAccountIds
     * @param array<int, string> $alreadyAsked
     * @return array<int, array<string, mixed>>
     */
    public function unmatchedStores(array $rows, ?int $fallbackAccountId, array $allowedAccountIds, array $alreadyAsked = []): array
    {
        $items = [];
        $seen = [];
        $this->leftOut['stores'] = 0;

        foreach ($rows as $row) {
            $code = trim((string) ($row['store_code'] ?? ''));
            $name = trim((string) ($row['store_name'] ?? ''));
            $account = $this->accountFor($row, $fallbackAccountId, $allowedAccountIds);

            if (filled($row['shipping_address_id'] ?? null) || ($code === '' && $name === '') || $account === null) {
                continue;
            }

            $key = 'store|' . $account->id . '|' . $code . '|' . $name;

            if (isset($seen[$key]) || \in_array($key, $alreadyAsked, true)) {
                continue;
            }

            $seen[$key] = true;

            if (\count($items) >= self::MAX_ITEMS) {
                $this->leftOut['stores']++;

                continue;
            }

            $this->asked[] = $key;

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

        return $this->accounts[(int) $id] ??= Account::find($id, ['id', 'account_code', 'short_name', 'company_id']);
    }

    /**
     * Products whose brand, description and size share the most words with the line, ranked by the
     * database so the best ones are found however many products share a common word. When the
     * account belongs to a company, only products with a price code in that company are offered.
     *
     * @return array<int, array{code: string, name: string}>
     */
    private function productCandidates(string $description, Account $account): array
    {
        $words = \array_slice($this->words($description), 0, self::SEARCH_WORDS);

        if ($words === []) {
            return [];
        }

        $text = "LOWER(CONCAT_WS(' ', products.brand, products.description, products.size))";
        $score = implode(' + ', array_map(fn (string $word): string => "(CASE WHEN {$text} LIKE ? THEN " . \strlen($word) . ' ELSE 0 END)', $words));
        $patterns = array_map(fn (string $word): string => '%' . $word . '%', $words);

        return Product::query()
            ->select(['products.stock_code', 'products.description', 'products.size', 'products.brand'])
            ->selectRaw("({$score}) as match_score", $patterns)
            ->where(function ($query) use ($text, $patterns) {
                foreach ($patterns as $pattern) {
                    $query->orWhereRaw("{$text} LIKE ?", [$pattern]);
                }
            })
            ->when($account->company_id !== null, fn ($query) => $query->whereHas('price_codes', fn ($prices) => $prices->where('company_id', $account->company_id)))
            ->orderByDesc('match_score')
            ->orderBy('products.stock_code')
            ->limit(self::CANDIDATES)
            ->get()
            ->map(fn (Product $product): array => [
                'code' => (string) $product->stock_code,
                'name' => trim($product->brand . ' ' . $product->description . ' ' . $product->size),
            ])
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
