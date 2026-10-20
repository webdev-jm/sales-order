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
use App\Services\Ai\AiResponse;
use App\Services\Ai\AiUsageMeter;
use Illuminate\Support\Facades\Cache;

/**
 * Reads an account's PO PDF with AI and turns it into sales order template rows, the same rows
 * an uploaded CSV produces. Scanned PDFs work too: the model reads the page images itself.
 *
 * The account's scan format, when it has one, tells the AI how that account lays out its POs.
 * The AI client fails over to the next provider when one cannot answer. Every call, including
 * each failed provider, is logged with its tokens, and no call is made once the monthly limit or
 * the user's daily limit is used up.
 *
 * What the AI read from a PDF is kept for a while (config ai.po_scan.cache_days), so scanning the
 * same file again for the same account and format costs no tokens. The rows are still built
 * fresh, so products and stores added since then are matched.
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
        - printed_total: the PO grand total as printed, or null when there is none.
        - page: the page number (from 1) the PO header or the line is printed on.
        - confidence: for each line, and for the header fields in the PO's confidence object, a
          number from 0 to 100 for how sure you are that the value matches the print. Lower it for
          blurred, faded, handwritten, overwritten or cut-off print, or when a column is ambiguous.
          Do not lower it only because a field is empty on the document.
        - unclear: a few words on what was hard to read, or an empty string when nothing was.
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
     * @param bool $useSaved false reads the PDF with AI again even when it was read before
     * @return array<int, array<string, mixed>>
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function scan(string $pdfContents, string $fileName, Account $account, ?PoScanFormat $format, ?User $user, bool $useSaved = true): array
    {
        if (($problem = $this->ai->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        $log = [
            'user_id'    => $user?->id,
            'account_id' => $account->id,
            'feature'    => AiUsageLog::FEATURE_PO_SCAN,
            'reference'  => $fileName,
        ];

        $cacheKey = $this->cacheKey($pdfContents, $account, $format);

        if ($useSaved && ($rows = $this->savedRows($cacheKey, $account, $format, $fileName, $log)) !== []) {
            return $rows;
        }

        if (($problem = $this->meter->budgetProblem($user)) !== null) {
            throw new AiUnavailableException($problem);
        }

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
            } elseif (($days = (int) config('ai.po_scan.cache_days')) > 0) {
                Cache::put($cacheKey, ['data' => $data, 'provider' => $response->provider, 'model' => $response->model], now()->addDays($days));
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
        $rating = ['type' => ['number', 'null']];

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
                            'printed_total'     => $number,
                            'page'              => $number,
                            'confidence'        => [
                                'type'       => 'object',
                                'properties' => [
                                    'po_number'     => $rating,
                                    'po_date'       => $rating,
                                    'delivery_date' => $rating,
                                    'store'         => $rating,
                                    'total'         => $rating,
                                ],
                            ],
                            'unclear'           => $text,
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
                                        'page'        => $number,
                                        'confidence'  => $rating,
                                        'unclear'     => $text,
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

    /**
     * Rows from an earlier read of the same file, logged as a call that used no tokens, or an
     * empty array when there is none.
     *
     * @param array<string, mixed> $log
     * @return array<int, array<string, mixed>>
     */
    private function savedRows(string $cacheKey, Account $account, ?PoScanFormat $format, string $fileName, array $log): array
    {
        if ((int) config('ai.po_scan.cache_days') <= 0 || ! \is_array($saved = Cache::get($cacheKey))) {
            return [];
        }

        $rows = $this->rowsFrom((array) ($saved['data'] ?? []), $account, $format, $fileName);

        if ($rows !== []) {
            $this->meter->record($log + ['status' => AiUsageLog::STATUS_CACHED], new AiResponse(
                text: '',
                provider: (string) ($saved['provider'] ?? ''),
                model: (string) ($saved['model'] ?? ''),
                inputTokens: 0,
                outputTokens: 0,
                totalTokens: 0,
                durationMs: 0,
            ));
        }

        return $rows;
    }

    /**
     * The same file read for the same account, with the same format notes and instructions, in
     * the same database. Editing the format makes the next scan read the file again.
     */
    private function cacheKey(string $pdfContents, Account $account, ?PoScanFormat $format): string
    {
        return 'ai-po-scan:' . hash('sha256', implode('|', [
            hash('sha256', $pdfContents),
            $account->getConnectionName(),
            $account->id,
            $format?->id,
            $format?->updated_at?->getTimestamp(),
            hash('sha256', self::INSTRUCTIONS . json_encode(self::schema())),
        ]));
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

            $header = [
                'po_page'          => $this->page($order['page'] ?? null),
                'po_confidence'    => array_map(fn ($rating) => $this->rating($rating), array_intersect_key(
                    \is_array($order['confidence'] ?? null) ? $order['confidence'] : [],
                    array_flip(['po_number', 'po_date', 'delivery_date', 'store', 'total'])
                )),
                'po_unclear'       => $this->text($order['unclear'] ?? ''),
                'po_printed_total' => $this->number($order['printed_total'] ?? null),
            ];

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
                ], $productData, $addressData, $header, [
                    'page'          => $this->page($line['page'] ?? null) ?? $header['po_page'],
                    'ai_confidence' => $this->rating($line['confidence'] ?? null),
                    'ai_unclear'    => $this->text($line['unclear'] ?? ''),
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

    /**
     * The AI's own rating of a value, 0 to 100, or null when it gave none.
     */
    private function rating(mixed $value): ?int
    {
        return is_numeric($value) ? (int) round(max(0, min(100, (float) $value))) : null;
    }

    private function page(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value >= 1 ? (int) $value : null;
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
