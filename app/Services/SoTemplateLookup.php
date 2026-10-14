<?php

namespace App\Services;

use App\Models\AccountProductReference;
use App\Models\AccountShipAddressMapping;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\DB;

/**
 * Matches a PO line to a product and a shipping address for the sales order template, whether
 * the line came from an uploaded CSV or from a scanned PDF.
 */
class SoTemplateLookup
{
    /**
     * @return array{total: int, ok: int, sku_not_found: int, address_not_found: int, both_not_found: int}
     */
    public static function emptySummary(): array
    {
        return [
            'total'             => 0,
            'ok'                => 0,
            'sku_not_found'     => 0,
            'address_not_found' => 0,
            'both_not_found'    => 0,
        ];
    }

    /**
     * @param array<int, array{lookup_status: string}> $rows
     * @return array{total: int, ok: int, sku_not_found: int, address_not_found: int, both_not_found: int}
     */
    public static function summarize(array $rows): array
    {
        $summary = self::emptySummary();

        foreach ($rows as $row) {
            $summary['total']++;
            $summary[$row['lookup_status']]++;
        }

        return $summary;
    }

    /**
     * @param array{product_id: int|null} $productData
     * @param array{shipping_address_id: int|null} $addressData
     */
    public function status(array $productData, array $addressData): string
    {
        $skuMissing  = empty($productData['product_id']);
        $addrMissing = empty($addressData['shipping_address_id']);

        if ($skuMissing && $addrMissing) {
            return 'both_not_found';
        }

        if ($skuMissing) {
            return 'sku_not_found';
        }

        if ($addrMissing) {
            return 'address_not_found';
        }

        return 'ok';
    }

    /**
     * Finds the product by stock code, then by the accounts' own product references, then by
     * barcode when one is given.
     *
     * @param array<int, int> $accountIds
     * @return array{product_id: int|null, internal_sku: string|null, product_name: string|null}
     */
    public function product(string $rawSku, string $skuCode, array $accountIds, string $barcode = ''): array
    {
        $empty = ['product_id' => null, 'internal_sku' => null, 'product_name' => null];

        if (empty($rawSku) && empty($skuCode) && empty($barcode)) {
            return $empty;
        }

        $product = null;

        if (!empty($rawSku) || !empty($skuCode)) {
            // 1. Direct match on stripped SKU
            $product = Product::where('stock_code', $skuCode)->first();

            // 2. Direct match on raw SKU
            if (empty($product)) {
                $product = Product::where('stock_code', $rawSku)->first();
            }

            // 3. AccountProductReference fallback across all selected accounts
            if (empty($product) && !empty($accountIds)) {
                $ref = AccountProductReference::whereIn('account_id', $accountIds)
                    ->where(function ($q) use ($rawSku, $skuCode) {
                        $q->where('account_reference', $rawSku)
                          ->orWhere('account_reference', $skuCode)
                          ->orWhere(DB::raw('CAST(account_reference AS UNSIGNED)'), $rawSku)
                          ->orWhere(DB::raw('CAST(account_reference AS UNSIGNED)'), $skuCode);
                    })
                    ->first();

                if (!empty($ref)) {
                    $product = $ref->product;
                }
            }
        }

        // 4. Barcode printed on the PO
        if (empty($product) && !empty($barcode)) {
            $product = Product::where('bar_code', $barcode)->first();
        }

        if (empty($product)) {
            return $empty;
        }

        return [
            'product_id'   => $product->id,
            'internal_sku' => $product->stock_code,
            'product_name' => trim($product->description . ' ' . $product->size),
        ];
    }

    /**
     * @param array<int, int> $accountIds
     * @param array<string, string> $storeMap Puregold store code => BEVI address code
     * @return array{account_id: int|null, shipping_address_id: int|null, shipping_name: string|null, shipping_address: string|null, store_code?: string}
     */
    public function shippingAddress(string $storeCode, string $storeName, array $accountIds, array $storeMap = []): array
    {
        $empty = ['account_id' => null, 'shipping_address_id' => null, 'shipping_name' => null, 'shipping_address' => null];

        if ((empty($storeCode) && empty($storeName)) || empty($accountIds)) {
            return $empty;
        }

        // 1. Puregold → BEVI code mapping (from DB)
        $lookupCode = $storeMap[$storeCode] ?? $storeCode;

        // 2. Exact address_code match using mapped code
        $address = ShippingAddress::whereIn('account_id', $accountIds)
            ->where('address_code', $lookupCode)
            ->first();

        // 3. Cast match (handles any remaining leading-zero differences)
        if (empty($address) && is_numeric($lookupCode)) {
            $address = ShippingAddress::whereIn('account_id', $accountIds)
                ->where(DB::raw('CAST(address_code AS UNSIGNED)'), (int) $lookupCode)
                ->first();
        }

        // 4. AccountShipAddressMapping fallback
        if (empty($address)) {
            $mapping = AccountShipAddressMapping::whereIn('account_id', $accountIds)
                ->where(function ($q) use ($storeCode, $storeName) {
                    $q->where('reference1', $storeCode)
                      ->orWhere('reference2', $storeCode)
                      ->orWhere('reference3', $storeCode)
                      ->orWhere(DB::raw('LOWER(REPLACE(reference1," ",""))'), strtolower(str_replace(' ', '', $storeName)))
                      ->orWhere(DB::raw('LOWER(REPLACE(reference2," ",""))'), strtolower(str_replace(' ', '', $storeName)))
                      ->orWhere(DB::raw('LOWER(REPLACE(reference3," ",""))'), strtolower(str_replace(' ', '', $storeName)));
                })
                ->first();

            if (!empty($mapping)) {
                $address = $mapping->shipping_address;
            }
        }

        if (empty($address)) {
            return $empty;
        }

        $fullAddress = implode(', ', array_filter([
            $address->building,
            $address->street,
            $address->city,
        ]));

        return [
            'account_id'          => $address->account_id,
            'store_code'          => $address->address_code,
            'shipping_address_id' => $address->id,
            'shipping_name'       => $address->ship_to_name,
            'shipping_address'    => $fullAddress,
        ];
    }
}
