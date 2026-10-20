<?php

namespace App\Services;

use App\Exceptions\AiUnavailableException;
use App\Helpers\UploadDateHelper;
use App\Models\Account;
use App\Models\PoScanFormat;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Reads a customer PO PDF with the AI PO scanner and groups its lines into sales order data:
 * one entry per PO number, with items keyed by BEVI stock code and unit.
 *
 * Lines the scanner could not match to a product are returned as warnings, in the same shape
 * as SalesOrderService's skipped items, so the upload screens can list them together.
 *
 * Each PO also carries its header and every line as read, with the AI's rating, and the confidence the
 * app worked out from them (ScanConfidence), for the review screens.
 */
class SalesOrderPdfReader
{
    public function __construct(private PoScanner $scanner)
    {
    }

    /**
     * @return array<string, array{
     *     po_number: string,
     *     ship_date: ?string,
     *     ship_date_error: ?string,
     *     shipping_address_id: ?int,
     *     address_code: string,
     *     po_value: float,
     *     shipping_instruction: string,
     *     items: array<string, array<string, float>>,
     *     warnings: array<int, array{stock_code: string, reason: string}>,
     *     header: array<string, mixed>,
     *     lines: array<int, array<string, mixed>>,
     *     confidence: array<string, mixed>
     * }>
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function read(UploadedFile $file, Account $account, ?User $user): array
    {
        $format = PoScanFormat::active()
            ->where('account_id', $account->id)
            ->orderBy('name')
            ->first();

        $rows = $this->scanner->scan($file->get(), $file->getClientOriginalName(), $account, $format, $user);

        $orders = [];
        $productNames = Product::whereIn('id', array_filter(array_column($rows, 'product_id')))->pluck('description', 'id');

        foreach ($rows as $row) {
            $poNumber = $row['po_number'];

            if (!isset($orders[$poNumber])) {
                $deliveryDate = $row['delivery_date'] !== '' ? $row['delivery_date'] : null;

                $orders[$poNumber] = [
                    'po_number'            => $poNumber,
                    'ship_date'            => UploadDateHelper::parse($deliveryDate),
                    'ship_date_error'      => UploadDateHelper::error($deliveryDate, 'Delivery date', false),
                    'shipping_address_id'  => $row['shipping_address_id'] ?? null,
                    'address_code'         => !empty($row['shipping_address_id']) ? (string) $row['store_code'] : '',
                    'po_value'             => 0.0,
                    'shipping_instruction' => $row['po_remarks'],
                    'items'                => [],
                    'warnings'             => [],
                    'header'               => [
                        'po_date'               => $row['po_date'],
                        'po_date_printed'       => $row['po_date'],
                        'delivery_date_printed' => (string) $row['delivery_date'],
                        'store_code'            => $row['store_code'],
                        'store_name'            => $row['store_name'],
                        'printed_total'         => $row['po_printed_total'] ?? '',
                        'confidence'            => $row['po_confidence'] ?? [],
                        'unclear'               => $row['po_unclear'] ?? '',
                        'page'                  => $row['po_page'] ?? null,
                    ],
                    'lines'                => [],
                ];

                if (empty($row['shipping_address_id']) && ($row['store_code'] !== '' || $row['store_name'] !== '')) {
                    $orders[$poNumber]['warnings'][] = [
                        'stock_code' => trim($row['store_code'] . ' ' . $row['store_name']),
                        'reason'     => 'Store not matched to a shipping address; the account address is used.',
                    ];
                }
            }

            $orders[$poNumber]['lines'][] = [
                'sku'           => $row['raw_sku'],
                'description'   => $row['description'],
                'quantity'      => (float) $row['qty'],
                'uom'           => $row['uom'],
                'so_uom'        => $row['so_uom'],
                'unit_price'    => $row['list_price'],
                'amount'        => $row['amount'],
                'product_id'    => $row['product_id'] ?? null,
                'stock_code'    => !empty($row['product_id']) ? $row['internal_sku'] : null,
                'product'       => !empty($row['product_id']) ? (string) ($productNames[$row['product_id']] ?? '') : '',
                'page'          => $row['page'] ?? null,
                'ai_confidence' => $row['ai_confidence'] ?? null,
                'ai_unclear'    => $row['ai_unclear'] ?? '',
            ];

            if (is_numeric($row['amount'])) {
                $orders[$poNumber]['po_value'] += (float) $row['amount'];
            }

            if (empty($row['product_id'])) {
                $orders[$poNumber]['warnings'][] = [
                    'stock_code' => $row['raw_sku'],
                    'reason'     => 'Not found in the product catalog' . ($row['description'] !== '' ? ' (' . $row['description'] . ')' : '') . '.',
                ];

                continue;
            }

            $stockCode = $row['internal_sku'];
            $uom       = $row['so_uom'];

            $orders[$poNumber]['items'][$stockCode][$uom] = ($orders[$poNumber]['items'][$stockCode][$uom] ?? 0) + (float) $row['qty'];
        }

        foreach ($orders as $poNumber => $order) {
            $orders[$poNumber]['confidence'] = ScanConfidence::assess($order, $account->company_id);
        }

        return $orders;
    }

    /**
     * Order items from the lines the user kept on the review screen, with the quantities they set.
     *
     * @param array<int, array<string, mixed>> $lines
     * @param array<int, bool> $included
     * @param array<int, mixed> $quantities
     * @return array<string, array<string, float>> stock code => unit => quantity
     */
    public static function itemsFrom(array $lines, array $included = [], array $quantities = []): array
    {
        $items = [];

        foreach ($lines as $index => $line) {
            $quantity = is_numeric($quantities[$index] ?? null) ? (float) $quantities[$index] : (float) $line['quantity'];

            if (($included[$index] ?? true) === false || empty($line['product_id']) || $quantity <= 0) {
                continue;
            }

            $items[$line['stock_code']][$line['so_uom']] = ($items[$line['stock_code']][$line['so_uom']] ?? 0) + $quantity;
        }

        return $items;
    }
}
