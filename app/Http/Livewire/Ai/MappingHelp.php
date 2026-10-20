<?php

namespace App\Http\Livewire\Ai;

use App\Exceptions\AiUnavailableException;
use App\Models\AccountProductReference;
use App\Models\AccountShipAddressMapping;
use App\Models\Product;
use App\Models\PuregoldStoreMap;
use App\Models\ShippingAddress;
use App\Services\Ai\MappingSuggester;
use App\Services\SoTemplateLookup;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

/**
 * AI suggestions for the SKUs and store codes an SO Template upload could not match. Nothing is
 * saved until the user adds a suggestion, which creates the same product or address mapping the
 * Product Mapping tab and the ship-address mappings create, and re-checks the rows.
 */
class MappingHelp extends Component
{
    private const ROWS_KEY = 'so_template_rows';

    /** @var array<int, int|string> The accounts chosen for the upload */
    public $accountIds = [];

    /** @var array{products: array<int, array<string, mixed>>, stores: array<int, array<string, mixed>>}|null */
    public $suggestions;

    public $error = '';

    public $notice = '';

    public function suggest(MappingSuggester $suggester): void
    {
        $this->authorizeHelp();
        $this->reset('error', 'notice');

        try {
            $suggestions = $suggester->suggest($this->rows(), $this->fallbackAccountId(), $this->allowedAccountIds(), auth()->user());
        } catch (AiUnavailableException $e) {
            $this->error = $e->getMessage();

            return;
        }

        if ($suggestions['products'] === [] && $suggestions['stores'] === []) {
            $this->suggestions = null;
            $this->notice = 'No confident matches were found. Add the missing SKUs and stores on the mapping tabs.';

            return;
        }

        $this->suggestions = $suggestions;
    }

    public function addProduct(int $index, SoTemplateLookup $lookup): void
    {
        $this->authorizeHelp();
        $suggestion = $this->suggestions['products'][$index] ?? null;

        if ($suggestion === null || !empty($suggestion['added']) || !\in_array((int) $suggestion['account_id'], $this->allowedAccountIds(), true)) {
            return;
        }

        $product = Product::where('stock_code', $suggestion['code'])->first();

        if ($product === null) {
            $this->error = 'Product ' . $suggestion['code'] . ' no longer exists.';

            return;
        }

        AccountProductReference::firstOrCreate(
            ['account_id' => $suggestion['account_id'], 'account_reference' => $suggestion['sku']],
            ['product_id' => $product->id, 'description' => $suggestion['description'], 'active' => 1]
        );

        $this->recheckRows(fn (array $row): bool => (string) ($row['raw_sku'] ?? '') === (string) $suggestion['sku'], (int) $suggestion['account_id'], $lookup);
        $this->suggestions['products'][$index]['added'] = true;
    }

    public function addStore(int $index, SoTemplateLookup $lookup): void
    {
        $this->authorizeHelp();
        $suggestion = $this->suggestions['stores'][$index] ?? null;

        if ($suggestion === null || !empty($suggestion['added']) || !\in_array((int) $suggestion['account_id'], $this->allowedAccountIds(), true)) {
            return;
        }

        $address = ShippingAddress::where('account_id', $suggestion['account_id'])->where('address_code', $suggestion['code'])->first();

        if ($address === null) {
            $this->error = 'Ship-to address ' . $suggestion['code'] . ' no longer exists.';

            return;
        }

        AccountShipAddressMapping::firstOrCreate(
            ['account_id' => $suggestion['account_id'], 'shipping_address_id' => $address->id, 'reference1' => $suggestion['store_code'] !== '' ? $suggestion['store_code'] : $suggestion['store_name']],
            ['reference2' => $suggestion['store_name']]
        );

        $this->recheckRows(
            fn (array $row): bool => (string) ($row['store_code'] ?? '') === (string) $suggestion['store_code'] && (string) ($row['store_name'] ?? '') === (string) $suggestion['store_name'],
            (int) $suggestion['account_id'],
            $lookup
        );
        $this->suggestions['stores'][$index]['added'] = true;
    }

    public function dismiss(): void
    {
        $this->reset('suggestions', 'error', 'notice');
    }

    public function render(): View
    {
        $rows = $this->rows();

        return view('livewire.ai.mapping-help', [
            'unmatched' => collect($rows)->where('lookup_status', '!=', 'ok')->count(),
        ]);
    }

    /**
     * Looks the matching rows up again, the way the upload did, and tells the page to refresh.
     *
     * @param callable(array<string, mixed>): bool $matches
     */
    private function recheckRows(callable $matches, int $accountId, SoTemplateLookup $lookup): void
    {
        $accountIds = array_values(array_unique([$accountId, ...array_map('intval', $this->accountIds)]));
        $storeMap = PuregoldStoreMap::pluck('bevi_code', 'store_code')->all();
        $rows = $this->rows();

        foreach ($rows as $i => $row) {
            if (!$matches($row)) {
                continue;
            }

            $product = $lookup->product((string) ($row['raw_sku'] ?? ''), (string) ($row['sku_code'] ?? ''), $accountIds, (string) ($row['barcode'] ?? ''));
            $address = filled($row['shipping_address_id'] ?? null)
                ? array_intersect_key($row, array_flip(['account_id', 'store_code', 'shipping_address_id', 'shipping_name', 'shipping_address']))
                : $lookup->shippingAddress((string) ($row['store_code'] ?? ''), (string) ($row['store_name'] ?? ''), $accountIds, $storeMap);

            $rows[$i] = array_merge($row, filled($row['product_id'] ?? null) ? [] : $product, $address);

            if (blank($rows[$i]['account_code'] ?? null) && filled($rows[$i]['account_id'] ?? null)) {
                $account = \App\Models\Account::find($rows[$i]['account_id'], ['account_code', 'account_name']);
                $rows[$i]['account_code'] = $account?->account_code;
                $rows[$i]['account_name'] = $account?->account_name;
            }
            $rows[$i]['lookup_status'] = $lookup->status(
                ['product_id' => $rows[$i]['product_id'] ?? null],
                ['shipping_address_id' => $rows[$i]['shipping_address_id'] ?? null]
            );
        }

        Session::put(self::ROWS_KEY, $rows);
        $this->dispatch('templateRowsChanged');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return (array) Session::get(self::ROWS_KEY, []);
    }

    /**
     * With a single account chosen, rows whose address did not match still belong to it.
     */
    private function fallbackAccountId(): ?int
    {
        return \count($this->accountIds) === 1 ? (int) reset($this->accountIds) : null;
    }

    /**
     * The accounts of this upload the user may add mappings for: the accounts chosen for a CSV,
     * or the account a PDF scan was read for.
     *
     * @return array<int, int>
     */
    private function allowedAccountIds(): array
    {
        $user = auth()->user();
        $accountIds = array_map('intval', $this->accountIds);

        if ($accountIds === []) {
            $codes = collect($this->rows())->pluck('account_code')->filter()->unique()->all();
            $accountIds = \App\Models\Account::whereIn('account_code', $codes)->pluck('id')->map(fn ($id): int => (int) $id)->all();
        }

        if ($user->hasRole('superadmin')) {
            return $accountIds;
        }

        return array_values(array_intersect($accountIds, $user->accounts()->pluck('accounts.id')->map(fn ($id): int => (int) $id)->all()));
    }

    private function authorizeHelp(): void
    {
        abort_unless(auth()->user()->can('ai mapping help'), 403);
    }
}
