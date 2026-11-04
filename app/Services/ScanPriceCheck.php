<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Product;

/**
 * What SMS would charge the account for one unit of a scanned product, in each of the product's units,
 * worked out by SalesOrderService the same way the saved order will be. ScanConfidence uses it to flag a
 * line that the order would leave out (no price code) and a PO price that fits another unit or product.
 */
class ScanPriceCheck
{
    public function __construct(private SalesOrderService $salesOrderService)
    {
    }

    /**
     * @param array<int, string> $uoms Units to price, besides the product's own
     * @return array{prices: array<string, float>, problem: ?string}
     */
    public function forProduct(Account $account, Product $product, string $orderUom, array $uoms = []): array
    {
        $uoms = array_values(array_unique(array_filter(array_map(
            fn ($uom): string => strtoupper(trim((string) $uom)),
            [$orderUom, $product->stock_uom, $product->order_uom, $product->other_uom, ...$uoms]
        ))));

        $prices = [];
        $problem = null;

        foreach ($uoms as $uom) {
            $totals = $this->salesOrderService->calculateOrderTotals([
                $product->id => ['product' => $product, 'data' => [$uom => ['quantity' => 1]]],
            ], $account);

            $unitPrice = (float) ($totals['items'][$product->id]['product_total'] ?? 0);

            if ($unitPrice > 0) {
                $prices[$uom] = round($unitPrice, 4);
            } elseif ($uom === strtoupper($orderUom)) {
                $reason = (string) ($totals['skipped_items'][0]['reason'] ?? '');
                $problem = $reason === '' || $reason === 'Calculated total is zero.'
                    ? 'SMS has no price for this product in ' . $uom . ', so the order would leave this line out.'
                    : rtrim($reason, '.') . '. The order would leave this line out.';
            }
        }

        return ['prices' => $prices, 'problem' => $problem];
    }
}
