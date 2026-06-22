<?php

namespace App\Http\Livewire\SalesOrderTemplate;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use App\Models\Account;
use App\Models\Product;
use App\Models\PuregoldStoreMap;
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
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $file;
    public array $account_ids = [];
    public $accountSearch = '';
    public $step = 1;
    public $summary = [];

    public string $activeTab    = 'upload';

    // Store Map
    public string $storeMapSearch = '';
    public string $newStoreCode   = '';
    public string $newBeviCode    = '';
    public ?int   $editingId      = null;
    public string $editStoreCode  = '';
    public string $editBeviCode   = '';

    // Product Map — filters
    public string $productMapSearch    = '';
    public string $productMapAccountId = '';

    // Product Map — add form
    public string $newPmAccountId     = '';
    public string $newPmAccountRef    = '';
    public string $newPmProductId     = '';
    public string $newPmDescription   = '';
    public string $newPmProductSearch = '';

    // Product Map — inline edit
    public ?int   $pmEditingId        = null;
    public string $pmEditAccountRef   = '';
    public string $pmEditProductId    = '';
    public string $pmEditDescription  = '';
    public string $pmEditProductSearch = '';

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

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatingStoreMapSearch(): void
    {
        $this->resetPage('sm_page');
    }

    public function updatingProductMapSearch(): void
    {
        $this->resetPage('pm_page');
    }

    public function updatingProductMapAccountId(): void
    {
        $this->resetPage('pm_page');
    }

    public function addStoreMap(): void
    {
        $this->validate([
            'newStoreCode' => 'required|string|unique:puregold_store_maps,store_code',
            'newBeviCode'  => 'required|string',
        ], [
            'newStoreCode.unique' => 'That store code already exists.',
        ]);

        PuregoldStoreMap::create([
            'store_code' => trim($this->newStoreCode),
            'bevi_code'  => trim($this->newBeviCode),
        ]);

        $this->reset('newStoreCode', 'newBeviCode');
    }

    public function startEdit(int $id): void
    {
        $map = PuregoldStoreMap::findOrFail($id);
        $this->editingId    = $map->id;
        $this->editStoreCode = $map->store_code;
        $this->editBeviCode  = $map->bevi_code;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editStoreCode' => 'required|string|unique:puregold_store_maps,store_code,' . $this->editingId,
            'editBeviCode'  => 'required|string',
        ], [
            'editStoreCode.unique' => 'That store code already exists.',
        ]);

        PuregoldStoreMap::findOrFail($this->editingId)->update([
            'store_code' => trim($this->editStoreCode),
            'bevi_code'  => trim($this->editBeviCode),
        ]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editStoreCode', 'editBeviCode');
    }

    public function deleteStoreMap(int $id): void
    {
        PuregoldStoreMap::findOrFail($id)->delete();
    }

    public function addProductMap(): void
    {
        $this->validate([
            'newPmAccountId'  => 'required|exists:accounts,id',
            'newPmAccountRef' => 'required|string',
            'newPmProductId'  => 'required|exists:products,id',
        ]);

        AccountProductReference::create([
            'account_id'        => $this->newPmAccountId,
            'product_id'        => $this->newPmProductId,
            'account_reference' => trim($this->newPmAccountRef),
            'description'       => trim($this->newPmDescription),
            'active'            => 1,
        ]);

        $this->reset('newPmAccountId', 'newPmAccountRef', 'newPmProductId', 'newPmDescription', 'newPmProductSearch');
    }

    public function startEditPm(int $id): void
    {
        $ref = AccountProductReference::findOrFail($id);
        $this->pmEditingId         = $ref->id;
        $this->pmEditAccountRef    = $ref->account_reference;
        $this->pmEditProductId     = (string) $ref->product_id;
        $this->pmEditDescription   = (string) $ref->description;
        $this->pmEditProductSearch = '';
    }

    public function saveEditPm(): void
    {
        $this->validate([
            'pmEditAccountRef' => 'required|string',
            'pmEditProductId'  => 'required|exists:products,id',
        ]);

        AccountProductReference::findOrFail($this->pmEditingId)->update([
            'account_reference' => trim($this->pmEditAccountRef),
            'product_id'        => $this->pmEditProductId,
            'description'       => trim($this->pmEditDescription),
        ]);

        $this->cancelEditPm();
    }

    public function cancelEditPm(): void
    {
        $this->reset('pmEditingId', 'pmEditAccountRef', 'pmEditProductId', 'pmEditDescription', 'pmEditProductSearch');
    }

    public function deleteProductMap(int $id): void
    {
        AccountProductReference::findOrFail($id)->delete();
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
            'shipping_name', 'shipping_address', 'account_code', 'account_name', 'lookup_status',
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
        $grouped      = collect($rows)->groupBy(fn($row) => $row['account_code'] ?? 'unknown');

        if ($grouped->count() === 1) {
            $accountCode = $grouped->keys()->first();
            $tempPath    = $this->buildTemplateFile($templatePath, $grouped->first()->all());
            $filename    = 'so_template_' . $accountCode . '_' . date('Ymd_His') . '.xlsx';

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        $zipPath   = tempnam(sys_get_temp_dir(), 'so_templates_') . '.zip';
        $zip       = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $tempFiles = [];

        foreach ($grouped as $accountCode => $accountRows) {
            $xlsxPath    = $this->buildTemplateFile($templatePath, $accountRows->all());
            $tempFiles[] = $xlsxPath;
            $zip->addFile($xlsxPath, "so_template_{$accountCode}.xlsx");
        }

        $zip->close();

        foreach ($tempFiles as $f) {
            @unlink($f);
        }

        return response()->download($zipPath, 'so_upload_templates_' . date('Ymd_His') . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function buildTemplateFile(string $templatePath, array $rows): string
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $startRow = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$startRow}", $row['po_number']     ?? '');
            $sheet->setCellValue("B{$startRow}", $row['delivery_date'] ?? '');
            $sheet->setCellValue("C{$startRow}", $row['store_code']    ?? '');
            $sheet->setCellValue("D{$startRow}", $row['internal_sku']  ?? '');
            $sheet->setCellValue("E{$startRow}", $row['qty']           ?? '');
            $sheet->setCellValue("F{$startRow}", 'CS');
            $sheet->setCellValue("G{$startRow}", $row['amount']        ?? '');
            $sheet->setCellValue("H{$startRow}", '');
            $sheet->setCellValue("I{$startRow}", $row['po_remarks']    ?? '');
            $startRow++;
        }

        $writer   = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempPath = tempnam(sys_get_temp_dir(), 'so_template_') . '.xlsx';
        $writer->save($tempPath);

        return $tempPath;
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
        $handle       = fopen($path, 'r');
        $rows         = [];
        $accountIds   = array_map('intval', $this->account_ids);
        $accountsById = Account::whereIn('id', $accountIds)
            ->get(['id', 'account_code', 'account_name'])
            ->keyBy('id');
        $storeMap     = PuregoldStoreMap::pluck('bevi_code', 'store_code')->all();

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
                $accountIds,
                $storeMap
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

            $accountId   = $addressData['account_id'] ?? null;
            $account     = $accountId ? ($accountsById[$accountId] ?? null) : null;

            $rows[] = array_merge($parsed, $productData, $addressData, [
                'lookup_status' => $status,
                'account_code'  => $account?->account_code,
                'account_name'  => $account?->account_name,
            ]);

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

    private function lookupShippingAddress(string $storeCode, string $storeName, array $accountIds, array $storeMap = []): array
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

        $storeMaps = PuregoldStoreMap::query()
            ->when($this->storeMapSearch, fn($q) => $q
                ->where('store_code', 'like', "%{$this->storeMapSearch}%")
                ->orWhere('bevi_code', 'like', "%{$this->storeMapSearch}%"))
            ->orderBy('store_code')
            ->paginate(20, ['*'], 'sm_page');

        $productMaps = AccountProductReference::with([
                'account:id,account_code,account_name',
                'product:id,stock_code,description,size',
            ])
            ->when($this->productMapAccountId, fn($q) => $q->where('account_id', $this->productMapAccountId))
            ->when($this->productMapSearch, fn($q) => $q
                ->where('account_reference', 'like', "%{$this->productMapSearch}%")
                ->orWhereHas('product', fn($pq) => $pq
                    ->where('stock_code', 'like', "%{$this->productMapSearch}%")
                    ->orWhere('description', 'like', "%{$this->productMapSearch}%")))
            ->orderBy('account_id')->orderBy('account_reference')
            ->paginate(20, ['*'], 'pm_page');

        $pmProducts = Product::query()
            ->when($this->newPmProductSearch, fn($q) => $q
                ->where('stock_code', 'like', "%{$this->newPmProductSearch}%")
                ->orWhere('description', 'like', "%{$this->newPmProductSearch}%"))
            ->orderBy('stock_code')
            ->limit(30)
            ->get(['id', 'stock_code', 'description', 'size']);

        $pmEditProducts = Product::query()
            ->when($this->pmEditProductSearch, fn($q) => $q
                ->where('stock_code', 'like', "%{$this->pmEditProductSearch}%")
                ->orWhere('description', 'like', "%{$this->pmEditProductSearch}%"))
            ->orderBy('stock_code')
            ->limit(30)
            ->get(['id', 'stock_code', 'description', 'size']);

        return view('livewire.sales-order-template.index', [
            'rows'           => $rows,
            'summary'        => $this->summary,
            'accounts'       => $accounts,
            'storeMaps'      => $storeMaps,
            'productMaps'    => $productMaps,
            'pmProducts'     => $pmProducts,
            'pmEditProducts' => $pmEditProducts,
        ]);
    }
}
