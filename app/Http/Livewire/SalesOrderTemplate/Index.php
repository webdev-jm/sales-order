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

        // 1. Exact address_code match across all selected accounts
        $address = ShippingAddress::whereIn('account_id', $accountIds)
            ->where('address_code', $storeCode)
            ->first();

        // 2. Cast match (handles leading zeros)
        if (empty($address) && is_numeric($storeCode)) {
            $address = ShippingAddress::whereIn('account_id', $accountIds)
                ->where(DB::raw('CAST(address_code AS UNSIGNED)'), (int) $storeCode)
                ->first();
        }

        // 3. AccountShipAddressMapping fallback
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
