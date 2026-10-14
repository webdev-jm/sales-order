<?php

namespace App\Services;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Models\Account;
use App\Models\AiUsageLog;
use App\Models\PoScanFormat;
use App\Models\PuregoldStoreMap;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiUsageMeter;

/**
 * Reads an account's PO PDF with AI and turns it into sales order template rows, the same rows
 * an uploaded CSV produces. Scanned PDFs work too: the model reads the page images itself.
 *
 * The account's scan format, when it has one, tells the AI how that account lays out its POs.
 * The AI client fails over to the next provider when one cannot answer. Every call, including
 * each failed provider, is logged with its tokens, and no call is made once the monthly limit
 * is used up.
 */
class PoScanner
{
    public const UOMS = ['CS', 'IN', 'PCS'];

    private const INSTRUCTIONS = <<<'INSTRUCTIONS'
        You read purchase orders (POs) that customer accounts send to a consumer goods supplier,
        and copy what they say into JSON exactly as printed.

        Rules:
        - Copy codes, numbers and names exactly as printed. Never guess or invent a value. Use an
          empty string, or null for a number, when the document does not show it.
        - One entry in purchase_orders per PO. A PDF can hold several POs, for example one per
          store. Keep each one separate.
        - One entry in lines per ordered item. Skip subtotal, total, tax, discount and blank rows.
        - sku: the item code the account printed for the line (article, item, SKU or material
          code). barcode: the EAN or UPC barcode, only when the line shows one.
        - quantity: the ordered quantity as a number.
        - uom: CS for cases, IN for inner boxes, PCS for pieces. Follow the account notes when
          they say which unit the quantity is in. Use an empty string when it is not clear.
        - unit_price and amount: as numbers, without currency symbols or thousands separators.
        - Dates as YYYY-MM-DD. Philippine documents usually print dates month first
          (MM/DD/YYYY) unless the account notes say otherwise.
        - store_code and store_name: the store or warehouse the goods are delivered to (ship-to
          or deliver-to), not the account's head office.
        - remarks: delivery instructions or notes printed on the PO, on one line.
        - The account notes describe this account's layout. They take priority over these rules.
        INSTRUCTIONS;

    public function __construct(
        private AiClient $ai,
        private AiUsageMeter $meter,
        private SoTemplateLookup $lookup,
    ) {
    }

    /**
     * Reads one PDF and returns its template rows.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function scan(string $pdfContents, string $fileName, Account $account, ?PoScanFormat $format, ?User $user): array
    {
        if (($problem = $this->ai->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        if (! $this->meter->hasBudget()) {
            throw new AiUnavailableException('This month\'s AI token limit has been reached. Ask an administrator to raise it on the Settings page.');
        }

        $log = [
            'user_id'    => $user?->id,
            'account_id' => $account->id,
            'feature'    => AiUsageLog::FEATURE_PO_SCAN,
            'reference'  => $fileName,
        ];

        $response = $this->ai->generateJson(
            self::INSTRUCTIONS,
            $this->promptFor($account, $format),
            [AiDocument::pdf($pdfContents, $fileName)],
            self::schema(),
            (int) config('ai.po_scan.max_output_tokens'),
            fn (AiProviderInterface $provider, AiUnavailableException $e) => $this->meter->record(
                $log + ['status' => AiUsageLog::STATUS_FAILED, 'error' => $e->getMessage()],
                $provider
            ),
        );

        $error = null;
        $rows  = [];

        if ($response->wasRefused()) {
            $error = 'The AI declined to read this PDF. Check that it is a purchase order.';
        } elseif ($response->wasCutOff()) {
            $error = 'The PDF has too many lines to read in one go. Split it into smaller files and scan again.';
        } elseif (($data = $response->json()) === null) {
            $error = 'The AI reply could not be read. Try scanning the PDF again.';
        } else {
            $rows = $this->rowsFrom($data, $account, $format, $fileName);

            if ($rows === []) {
                $error = 'No PO lines were found in this PDF. Check that it is a purchase order from the selected account.';
            }
        }

        $this->meter->record($log + [
            'status' => $error === null ? AiUsageLog::STATUS_SUCCESS : AiUsageLog::STATUS_FAILED,
            'error'  => $error,
        ], $response);

        if ($error !== null) {
            throw new AiUnavailableException($error);
        }

        return $rows;
    }

    /**
     * The JSON Schema the AI reply must follow.
     *
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        $text   = ['type' => 'string'];
        $number = ['type' => ['number', 'null']];

        return [
            'type'       => 'object',
            'properties' => [
                'purchase_orders' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'po_number'         => $text,
                            'po_date'           => $text,
                            'delivery_date'     => $text,
                            'cancellation_date' => $text,
                            'store_code'        => $text,
                            'store_name'        => $text,
                            'delivery_location' => $text,
                            'remarks'           => $text,
                            'lines'             => [
                                'type'  => 'array',
                                'items' => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'sku'         => $text,
                                        'barcode'     => $text,
                                        'description' => $text,
                                        'quantity'    => $number,
                                        'uom'         => ['type' => 'string', 'enum' => [...self::UOMS, '']],
                                        'unit_price'  => $number,
                                        'amount'      => $number,
                                    ],
                                    'required' => ['sku', 'description', 'quantity'],
                                ],
                            ],
                        ],
                        'required' => ['po_number', 'lines'],
                    ],
                ],
            ],
            'required' => ['purchase_orders'],
        ];
    }

    private function promptFor(Account $account, ?PoScanFormat $format): string
    {
        $notes = trim((string) $format?->instructions);

        return implode("\n", [
            'Read the attached purchase order.',
            'Account: [' . $account->account_code . '] ' . $account->account_name,
            'Account notes: ' . ($notes !== '' ? $notes : 'none'),
            'Unit to use when the PO does not say: ' . $this->defaultUom($format),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function rowsFrom(array $data, Account $account, ?PoScanFormat $format, string $fileName): array
    {
        $accountIds = [$account->id];
        $storeMap   = PuregoldStoreMap::pluck('bevi_code', 'store_code')->all();
        $rows       = [];

        foreach ($data['purchase_orders'] ?? [] as $order) {
            $storeCode = $this->text($order['store_code'] ?? '');
            $storeName = $this->text($order['store_name'] ?? '');

            $addressData = $this->lookup->shippingAddress($storeCode, $storeName, $accountIds, $storeMap);

            foreach ($order['lines'] ?? [] as $line) {
                $sku     = $this->text($line['sku'] ?? '');
                $barcode = $this->text($line['barcode'] ?? '');
                $qty     = $this->number($line['quantity'] ?? null);

                if (($sku === '' && $barcode === '') || $qty === '') {
                    continue;
                }

                $productData = $this->lookup->product($sku, $sku, $accountIds, $barcode);

                $rows[] = array_merge([
                    'po_date'           => $this->date($order['po_date'] ?? ''),
                    'po_number'         => $this->text($order['po_number'] ?? ''),
                    'store_name'        => $storeName,
                    'store_code'        => $storeCode,
                    'delivery_date'     => $this->date($order['delivery_date'] ?? ''),
                    'cancellation_date' => $this->date($order['cancellation_date'] ?? ''),
                    'depot'             => '',
                    'del_loc'           => $this->text($order['delivery_location'] ?? ''),
                    'po_remarks'        => $this->text($order['remarks'] ?? ''),
                    'raw_sku'           => $sku !== '' ? $sku : $barcode,
                    'sku_code'          => $sku !== '' ? $sku : $barcode,
                    'description'       => $this->text($line['description'] ?? ''),
                    'uom'               => $this->text($line['uom'] ?? ''),
                    'qty'               => $qty,
                    'list_price'        => $this->number($line['unit_price'] ?? null),
                    'amount'            => $this->number($line['amount'] ?? null),
                ], $productData, $addressData, [
                    'lookup_status' => $this->lookup->status($productData, $addressData),
                    'account_code'  => $account->account_code,
                    'account_name'  => $account->account_name,
                    'so_uom'        => $this->uom($line['uom'] ?? '', $format),
                    'source_file'   => $fileName,
                ]);
            }
        }

        return $rows;
    }

    private function defaultUom(?PoScanFormat $format): string
    {
        $uom = strtoupper((string) $format?->default_uom);

        return \in_array($uom, self::UOMS, true) ? $uom : 'CS';
    }

    private function uom(mixed $value, ?PoScanFormat $format): string
    {
        $uom = strtoupper(trim((string) $value));

        return \in_array($uom, self::UOMS, true) ? $uom : $this->defaultUom($format);
    }

    private function text(mixed $value): string
    {
        return \is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * Whole numbers lose their decimals so the template reads "12", not "12.0".
     */
    private function number(mixed $value): string
    {
        if (\is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if (! is_numeric($value)) {
            return '';
        }

        $number = (float) $value;

        return floor($number) == $number ? (string) (int) $number : (string) round($number, 4);
    }

    private function date(mixed $value): string
    {
        $value = $this->text($value);

        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : $value;
    }
}
