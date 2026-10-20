<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Product;

/**
 * Turns one reviewed PO into what the Create SO form opens with: the order lines in the session and
 * the header values as flashed old input.
 */
class SalesOrderScanHandoff
{
    /**
     * Session key holding the PDF's scan while it is on the review screen.
     */
    public const REVIEW_SESSION_KEY = 'so_scan_review';

    public function __construct(private SalesOrderService $salesOrderService)
    {
    }

    /**
     * @param array<string, mixed> $order one entry of SalesOrderPdfReader::read()
     * @param array<string, array<string, float>> $items stock code => unit => quantity
     * @return array{order_data: array<string, mixed>, flash: array<string, mixed>}
     */
    public function prepare(array $order, array $items, Account $account, ?string $shipDate = null): array
    {
        $products = Product::whereIn('stock_code', array_keys($items))->get()->keyBy('stock_code');
        $data     = [];

        foreach ($items as $stock_code => $uoms) {
            $product = $products->get($stock_code);

            if ($product === null) {
                continue;
            }

            $data[$product->id] = [
                'product'          => $product,
                'stock_code'       => $stock_code,
                'description'      => $product->description,
                'size'             => $product->size,
                'data'             => collect($uoms)->map(fn ($quantity) => [
                    'quantity'   => $quantity,
                    'total'      => 0,
                    'discount'   => 0,
                    'discounted' => 0,
                ])->all(),
                'product_total'    => 0,
                'product_quantity' => 0,
            ];
        }

        $po_value = $order['po_value'] > 0 ? round($order['po_value'], 2) : '';

        $order_data             = $this->salesOrderService->calculateOrderTotals($data, $account);
        $order_data['po_value'] = $po_value;

        $address = !empty($order['shipping_address_id'])
            ? $account->shipping_addresses()->find($order['shipping_address_id'])
            : null;

        $notices = [];

        if ($po_value === '') {
            $notices[] = 'The PDF shows no line amounts, so enter the PO value before saving.';
        }

        $shipDate ??= $order['ship_date'] ?? '';

        $flash = ($order['ship_date_error'] !== null && $shipDate === ($order['ship_date'] ?? '')) ? ['message_error' => $order['ship_date_error']] : [];

        return [
            'order_data' => $order_data,
            'flash'      => $flash + [
                'po_number'            => $order['po_number'],
                'paf_number'           => '',
                'ship_date'            => $shipDate,
                'shipping_instruction' => $order['shipping_instruction'],
                'shipping_address_id'  => $address->id ?? 'default',
                'po_value'             => $po_value,
                'ship_to_name'         => $address ? $address->address_code . ' - ' . $address->ship_to_name : $account->account_name,
                'ship_to_address1'     => $address ? $address->building : $account->ship_to_address1,
                'ship_to_address2'     => $address ? $address->street : $account->ship_to_address2,
                'ship_to_address3'     => $address ? $address->city : $account->ship_to_address3,
                'postal_code'          => $address ? $address->postal : $account->postal_code,
                'scan_notices'         => $notices,
                'scan_warnings'        => array_merge($order['warnings'], $order_data['skipped_items'] ?? []),
            ],
        ];
    }
}
