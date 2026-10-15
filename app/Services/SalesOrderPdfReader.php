<?php

namespace App\Services;

use App\Exceptions\AiUnavailableException;
use App\Helpers\UploadDateHelper;
use App\Models\Account;
use App\Models\PoScanFormat;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Reads a customer PO PDF with the AI PO scanner and groups its lines into sales order data:
 * one entry per PO number, with items keyed by BEVI stock code and unit.
 *
 * Lines the scanner could not match to a product are returned as warnings, in the same shape
 * as SalesOrderService's skipped items, so the upload screens can list them together.
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
     *     warnings: array<int, array{stock_code: string, reason: string}>
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
                ];

                if (empty($row['shipping_address_id']) && ($row['store_code'] !== '' || $row['store_name'] !== '')) {
                    $orders[$poNumber]['warnings'][] = [
                        'stock_code' => trim($row['store_code'] . ' ' . $row['store_name']),
                        'reason'     => 'Store not matched to a shipping address; the account address is used.',
                    ];
                }
            }

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

        return $orders;
    }
}
