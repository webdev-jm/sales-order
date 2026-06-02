<?php

namespace App\Http\Livewire\SalesOrderTemplate;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\Account;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\AccountProductReference;
use App\Models\AccountShipAddressMapping;

use App\Exports\SalesOrderTemplateExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithFileUploads;

    public $file;
    public array $account_ids = [];
    public $accountSearch = '';
    public $step = 1;
    public $summary = [];

    protected $rules = [
        'account_ids'   => 'required|array|min:1',
        'account_ids.*' => 'exists:accounts,id',
        'file'          => 'required|file|mimes:csv,txt',
    ];

    protected $messages = [
        'account_ids.required' => 'Please select at least one account.',
        'account_ids.min'      => 'Please select at least one account.',
        'account_ids.*.exists' => 'One or more selected accounts are invalid.',
        'file.required'        => 'Please upload a CSV file.',
        'file.mimes'           => 'The file must be a CSV.',
    ];

    public function mount(): void
    {
        // accounts loaded reactively in render()
    }

    public function upload(): void
    {
        $this->validate();

        if (!auth()->user()->hasRole('superadmin')) {
            $allowedIds = auth()->user()->accounts()->pluck('accounts.id')->toArray();
            foreach ($this->account_ids as $id) {
                if (!\in_array((int) $id, $allowedIds)) {
                    $this->addError('account_ids', 'You do not have access to one or more selected accounts.');
                    return;
                }
            }
        }

        $path = $this->file->store('so-template-uploads');
        $fullPath = storage_path('app/' . $path);

        $this->parseFile($fullPath);
        $this->step = 2;
    }

    public function resetUpload(): void
    {
        Session::forget('so_template_rows');
        $this->reset('file', 'step', 'summary', 'account_ids');
        $this->step = 1;
    }

    public function export()
    {
        $rows = Session::get('so_template_rows', []);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="so_template_' . date('Ymd_His') . '.csv"',
        ];

        $columns = [
            'po_date', 'po_number', 'store_name', 'store_code', 'delivery_date',
            'cancellation_date', 'depot', 'del_loc', 'po_remarks', 'raw_sku',
            'sku_code', 'description', 'qty', 'list_price', 'amount',
            'internal_sku', 'product_name',
            'shipping_name', 'shipping_address', 'lookup_status',
        ];

        return response()->streamDownload(function () use ($rows, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn($col) => $row[$col] ?? '', $columns));
            }
            fclose($handle);
        }, 'so_template_' . date('Ymd_His') . '.csv', $headers);
    }

    public function exportTemplate()
    {
        $rows         = Session::get('so_template_rows', []);
        $templatePath = public_path('assets/SMS Multiple PO upload Format.xlsx');
        $filename     = 'so_upload_template_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $startRow = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$startRow}", $row['po_number']     ?? '');
            $sheet->setCellValue("B{$startRow}", $row['delivery_date'] ?? '');
            $sheet->setCellValue("C{$startRow}", $row['store_code'] ?? '');
            $sheet->setCellValue("D{$startRow}", $row['internal_sku']  ?? '');
            $sheet->setCellValue("E{$startRow}", $row['qty']           ?? '');
            $sheet->setCellValue("F{$startRow}", $row['uom']           ?? '');
            $sheet->setCellValue("G{$startRow}", $row['amount']        ?? '');
            $sheet->setCellValue("H{$startRow}", '');
            $sheet->setCellValue("I{$startRow}", $row['po_remarks']    ?? '');
            $startRow++;
        }

        $writer   = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempPath = tempnam(sys_get_temp_dir(), 'so_template_') . '.xlsx';
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportExcel()
    {
        $rows     = Session::get('so_template_rows', []);
        $filename = 'so_template_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new SalesOrderTemplateExport($rows), $filename);
    }

    // -------------------------------------------------------------------------
    // Parsing
    // -------------------------------------------------------------------------

    private function parseFile(string $path): void
    {
        $handle     = fopen($path, 'r');
        $rows       = [];
        $accountIds = array_map('intval', $this->account_ids);

        $summary = [
            'total'            => 0,
            'ok'               => 0,
            'sku_not_found'    => 0,
            'address_not_found'=> 0,
            'both_not_found'   => 0,
        ];

        while (($raw = fgetcsv($handle)) !== false) {
            if (empty(array_filter($raw))) {
                continue;
            }

            $parsed = $this->parseRow($raw);

            $productData = $this->lookupProduct(
                $parsed['raw_sku'],
                $parsed['sku_code'],
                $accountIds
            );

            $addressData = $this->lookupShippingAddress(
                $parsed['store_code'],
                $parsed['store_name'],
                $accountIds
            );

            $skuMissing  = empty($productData['product_id']);
            $addrMissing = empty($addressData['shipping_address_id']);

            if ($skuMissing && $addrMissing) {
                $status = 'both_not_found';
            } elseif ($skuMissing) {
                $status = 'sku_not_found';
            } elseif ($addrMissing) {
                $status = 'address_not_found';
            } else {
                $status = 'ok';
            }

            $rows[] = array_merge($parsed, $productData, $addressData, ['lookup_status' => $status]);

            $summary['total']++;
            $summary[$status]++;
        }

        fclose($handle);

        Session::put('so_template_rows', $rows);
        $this->summary = $summary;
    }

    private function parseRow(array $row): array
    {
        $isLuzon = empty($row[0]);

        if ($isLuzon) {
            return $this->parseLuzonRow($row);
        }

        return $this->parseSimpleRow($row);
    }

    private function parseLuzonRow(array $row): array
    {
        $rawSku    = trim($row[55] ?? '');
        $storeCode = ltrim(trim($row[36] ?? ''), '0');

        return [
            'po_date'           => $this->parseDate($row[1] ?? ''),
            'po_number'         => trim($row[4] ?? ''),
            'store_name'        => trim($row[6] ?? ''),
            'store_code'        => $storeCode,
            'delivery_date'     => $this->parseDate($row[41] ?? ''),
            'cancellation_date' => $this->parseDate($row[47] ?? ''),
            'depot'             => $this->extractSegment($row[30] ?? '', 'name'),
            'del_loc'           => $this->extractSegment($row[34] ?? '', 'dlvLocation'),
            'po_remarks'        => $this->extractSegment($row[51] ?? '', 'notes'),
            'raw_sku'           => $rawSku,
            'sku_code'          => $this->stripSkuSuffix($rawSku),
            'description'       => trim($row[56] ?? ''),
            'uom'               => 'CS',
            'qty'               => trim($row[58] ?? ''),
            'list_price'        => trim($row[60] ?? ''),
            'amount'            => trim($row[64] ?? ''),
        ];
    }

    private function parseSimpleRow(array $row): array
    {
        $combined  = $row[16] ?? '';
        $skuField  = $row[19] ?? '';

        $dlvFull   = $this->extractSegment($combined, 'dlvLocation');
        $storeCode = $this->extractStoreCode($dlvFull);
        $storeName = $this->extractStoreName($dlvFull);

        $rawSkuRaw = ltrim($this->extractSegment($skuField, 'sku'), '0');
        $rawSku    = $rawSkuRaw;

        return [
            'po_date'           => $this->parseDate($row[0] ?? ''),
            'po_number'         => trim($row[1] ?? ''),
            'store_name'        => $storeName,
            'store_code'        => $storeCode,
            'delivery_date'     => $this->parseDate($row[10] ?? ''),
            'cancellation_date' => $this->parseDate($row[14] ?? ''),
            'depot'             => '',
            'del_loc'           => $dlvFull,
            'po_remarks'        => $this->extractSegmentToEnd($combined, 'notes'),
            'raw_sku'           => $rawSku,
            'sku_code'          => $this->stripSkuSuffix($rawSku),
            'description'       => trim($this->extractSegment($skuField, 'description')),
            'uom'               => trim($this->extractSegment($skuField, 'buyUM')),
            'qty'               => trim($row[20] ?? ''),
            'list_price'        => trim($this->extractSegment($skuField, 'buyCost')),
            'amount'            => trim($row[25] ?? ''),
        ];
    }

    // -------------------------------------------------------------------------
    // DB Lookups
    // -------------------------------------------------------------------------

    private function lookupProduct(string $rawSku, string $skuCode, array $accountIds): array
    {
        $empty = ['product_id' => null, 'internal_sku' => null, 'product_name' => null];

        if (empty($rawSku) && empty($skuCode)) {
            return $empty;
        }

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

        if (empty($product)) {
            return $empty;
        }

        return [
            'product_id'   => $product->id,
            'internal_sku' => $product->stock_code,
            'product_name' => trim($product->description . ' ' . $product->size),
        ];
    }

    private function lookupShippingAddress(string $storeCode, string $storeName, array $accountIds): array
    {
        $empty = ['shipping_address_id' => null, 'shipping_name' => null, 'shipping_address' => null];

        if ((empty($storeCode) && empty($storeName)) || empty($accountIds)) {
            return $empty;
        }

        // 1. Static Puregold → BEVI code mapping
        $lookupCode = self::PUREGOLD_STORE_MAP[$storeCode] ?? $storeCode;

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
            'store_code'          => $address->address_code,
            'shipping_address_id' => $address->id,
            'shipping_name'       => $address->ship_to_name,
            'shipping_address'    => $fullAddress,
        ];
    }

    // -------------------------------------------------------------------------
    // Static Puregold → BEVI address_code mapping
    // -------------------------------------------------------------------------

    private const PUREGOLD_STORE_MAP = [
        '1034' => '1034',
        '1031' => '01031',
        '1032' => '1032',
        '1021' => '01021',
        '1033' => '01033',
        '1026' => '01026',
        '1029' => '1029',
        '1035' => '1035',
        '1025' => '01025',
        '1028' => '1028',
        '1027' => '1027',
        '1037' => '01037',
        '1036' => '01036',
        '1039' => '01039',
        '1038' => '01038',
        '1041' => '01041',
        '1042' => '01042',
        '1043' => '01043',
        '1044' => '1044',
        '1052' => '1052',
        '1053' => '1053',
        '1051' => '1051',
        '1050' => '1050',
        '1049' => '1049',
        '1057' => '1057',
        '1059' => '1059',
        '1060' => '1060',
    ];

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function extractSegment(string $str, string $key): string
    {
        if (preg_match('/:' . preg_quote($key, '/') . ':([^:]+)/', $str, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * Extract a segment that may run to end of string (e.g. :notes: value).
     */
    private function extractSegmentToEnd(string $str, string $key): string
    {
        if (preg_match('/:' . preg_quote($key, '/') . ':(.*)$/i', $str, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function stripSkuSuffix(string $raw): string
    {
        return preg_replace('/-\d+$/', '', $raw);
    }

    private function parseDate(string $value): string
    {
        if (empty(trim($value))) {
            return '';
        }

        $ts = strtotime($value);

        return $ts !== false ? date('Y-m-d', $ts) : trim($value);
    }

    /**
     * Extract the leading numeric store code from a :dlvLocation: value.
     * e.g. "00778 PG-SAN JOSE DE BUENAVISTA" → "778"
     */
    private function extractStoreCode(string $dlvLocation): string
    {
        if (preg_match('/^(\d+)/', trim($dlvLocation), $matches)) {
            return ltrim($matches[1], '0');
        }

        return '';
    }

    /**
     * Extract the store name (part after the leading code) from a :dlvLocation: value.
     */
    private function extractStoreName(string $dlvLocation): string
    {
        return trim(preg_replace('/^\d+\s*/', '', trim($dlvLocation)));
    }

    // -------------------------------------------------------------------------

    public function render()
    {
        $rows = Session::get('so_template_rows', []);

        $accounts = Account::orderBy('account_name')
            ->when(!auth()->user()->hasRole('superadmin'), function ($q) {
                $q->whereHas('users', function ($query) {
                    $query->where('users.id', auth()->id());
                });
            })
            ->when(trim($this->accountSearch) !== '', function ($q) {
                $search = '%' . trim($this->accountSearch) . '%';
                $q->where('account_name', 'like', $search)
                  ->orWhere('account_code', 'like', $search);
            })
            ->limit(50)
            ->get(['id', 'account_code', 'account_name']);

        return view('livewire.sales-order-template.index', [
            'rows'     => $rows,
            'summary'  => $this->summary,
            'accounts' => $accounts,
        ]);
    }
}
