<?php

namespace App\Http\Livewire\SalesOrder\Multiple;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Fluent;

use App\Exceptions\AiUnavailableException;
use App\Models\Product;
use App\Models\SalesOrderAttachment;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\PurchaseOrderNumber;
use App\Models\ShippingAddress;
use App\Jobs\GenerateSalesOrderXml;

use App\Services\Ai\AiClient;
use App\Services\SalesOrderAttachmentService;
use App\Services\SalesOrderPdfReader;
use App\Services\SalesOrderService;
use App\Helpers\UploadDateHelper;
use Illuminate\Support\Facades\Validator;

class Upload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';


    public $logged_account;
    public $account;
    public $shipping_addresses;
    public $setting;
    public $so_files = [];
    public $so_data;
    public $err_data;
    public $success_data;

    /** @var array<string, \Livewire\TemporaryUploadedFile> so_data entry key => its PO attachment */
    public $attachments = [];

    /** @var array<string, string> PDF file name => why it could not be read */
    public array $scanErrors = [];

    protected $listeners = [
        'finalizeAll' => 'saveAll'
    ];

    protected $salesOrderService;

    public function boot(SalesOrderService $salesOrderService)
    {
        $this->salesOrderService = $salesOrderService;
    }

    public function mount($logged_account) {
        $this->logged_account = $logged_account;
        $this->account = $logged_account->account;

        $shipping_addresses = ShippingAddress::where('account_id', $this->account->id)
            ->orderBy('address_code', 'ASC')
            ->get();

        $this->shipping_addresses = $shipping_addresses->map(function ($address) {
            return array_map('trim', $address->toArray());
        });

    }

    public function checkFileData() {
        $this->validate([
            'so_files'   => 'required|array|min:1|max:' . config('ai.po_scan.max_files'),
            'so_files.*' => 'file|mimes:xlsx,xls,pdf|max:' . config('ai.po_scan.max_file_kb'),
        ], [
            'so_files.required' => 'Please choose the Excel file or the PO PDFs to upload.',
            'so_files.max'      => 'Upload at most :max PDFs at a time.',
            'so_files.*.mimes'  => 'Upload the Excel format (.xlsx, .xls) or PO PDFs.',
            'so_files.*.max'    => 'Each file must be smaller than :max KB.',
        ]);

        $sheets = collect($this->so_files)->reject(fn ($file) => $this->isPdf($file));

        if ($sheets->isEmpty()) {
            $this->readPdfs();
            return;
        }

        if (count($this->so_files) > 1) {
            $this->addError('so_files', 'Upload one Excel file, or one or more PO PDFs.');
            return;
        }

        $so_file = $sheets->first();

        $path1 = $so_file->storeAs('multiple-so', $so_file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowResults = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowResults[] = $cell->getCalculatedValue();
            }
            $data[] = $rowResults;
        }

        $this->processData($data);
    }

    /**
     * Reads each PO PDF with AI. Every purchase order found becomes one sales order, with the
     * PDF it came from as its attachment.
     */
    private function readPdfs(): void {
        $this->reset(['so_data', 'err_data', 'success_data', 'attachments', 'scanErrors']);

        if (!auth()->user()->can('po scan access')) {
            $this->addError('so_files', 'You do not have permission to scan PO PDFs. Upload the Excel format instead.');
            return;
        }

        set_time_limit(30 + count($this->so_files) * app(AiClient::class)->maxDurationSeconds());

        $reader = app(SalesOrderPdfReader::class);
        $grouped_data = [];
        $source_files = [];

        foreach ($this->so_files as $file) {
            $file_name = $file->getClientOriginalName();

            try {
                $orders = $reader->read($file, $this->account, auth()->user());
            } catch (AiUnavailableException $e) {
                $this->scanErrors[$file_name] = $e->getMessage();
                continue;
            }

            foreach ($orders as $po_raw => $order) {
                if ($po_raw === '') {
                    $this->scanErrors[$file_name] = 'A purchase order in this PDF shows no PO number, so it was not loaded.';
                    continue;
                }

                $po_number = !empty($this->account->po_prefix) ? $this->account->po_prefix . $po_raw : $po_raw;

                if (isset($grouped_data[$po_number])) {
                    $this->scanErrors[$file_name] = 'PO ' . $po_raw . ' was already read from ' . $source_files[$po_number]->getClientOriginalName() . ', so this copy was skipped.';
                    continue;
                }

                $grouped_data[$po_number] = [
                    'meta' => [
                        'ship_date' => $order['ship_date'],
                        'ship_date_error' => $order['ship_date_error'],
                        'ship_to_address_code' => $order['address_code'],
                        'po_value' => round($order['po_value'], 2),
                        'paf_number' => '',
                        'shipping_instruction' => $order['shipping_instruction'],
                        'warnings' => $order['warnings'],
                        'scanned_from' => $file_name,
                    ],
                    'items' => collect($order['items'])->map(fn ($uoms) => ['product' => null, 'uoms' => $uoms])->all(),
                ];
                $source_files[$po_number] = $file;
            }
        }

        $this->buildSoData($grouped_data);

        foreach ($this->so_data as $po_number => $data) {
            $this->attachments[$data['key']] = $source_files[$po_number];
        }
    }

    private function isPdf($file): bool {
        return strtolower($file->getClientOriginalExtension()) === 'pdf';
    }

    private function processData($data) {
        $this->reset(['so_data', 'err_data', 'success_data', 'attachments', 'scanErrors']);

        $grouped_data = [];

        // 1. Group Raw Data by PO Number
        foreach($data as $key => $row) {
            if(!empty(trim($row[0])) && $key != 0) {

                $po_raw = trim($row[0]);
                $po_number = !empty($this->account->po_prefix) ? $this->account->po_prefix . $po_raw : $po_raw;

                // Parse Dates - the cell may hold an Excel serial or text in any format.
                $ship_date_raw = $row[1] ?? null;
                $ship_date = UploadDateHelper::parse($ship_date_raw);
                $ship_date_error = UploadDateHelper::error($ship_date_raw, 'Ship date');

                // Initialize PO Group if not exists
                if (!isset($grouped_data[$po_number])) {
                    $grouped_data[$po_number] = [
                        'meta' => [
                            'ship_date' => $ship_date,
                            'ship_date_error' => $ship_date_error,
                            'ship_to_address_code' => trim($row[2]),
                            'po_value' => 0,
                            'paf_number' => trim($row[7]),
                            'shipping_instruction' => trim($row[8]),
                        ],
                        'items' => []
                    ];
                }

                // Aggregate PO Value
                $grouped_data[$po_number]['meta']['po_value'] += (float)trim($row[6]);

                // Aggregate Items (Handle duplicate SKUs in same PO by summing quantities)
                $sku = trim($row[3]);
                $uom = strtoupper(trim($row[5]));
                $qty = (int)trim($row[4]);

                if (!isset($grouped_data[$po_number]['items'][$sku])) {
                    $grouped_data[$po_number]['items'][$sku] = ['product' => null, 'uoms' => []];
                }

                if (!isset($grouped_data[$po_number]['items'][$sku]['uoms'][$uom])) {
                    $grouped_data[$po_number]['items'][$sku]['uoms'][$uom] = 0;
                }

                $grouped_data[$po_number]['items'][$sku]['uoms'][$uom] += $qty;
            }
        }

        $this->buildSoData($grouped_data);
    }

    /**
     * Prices each PO group and builds the cards shown for review.
     *
     * @param array<string, array{meta: array<string, mixed>, items: array<string, array{uoms: array<string, float|int>}>}> $grouped_data
     */
    private function buildSoData(array $grouped_data): void {
        $final_data = [];
        $index = 0;

        // 2. Process each PO Group using SalesOrderService
        foreach ($grouped_data as $po_number => $group) {

            // Prepare Shipping Address
            $ship_to_code = $group['meta']['ship_to_address_code'];
            $shipping_address = [];
            if(!empty($ship_to_code) && !empty($this->shipping_addresses)) {
                $shipping_address = $this->shipping_addresses->filter(function ($address) use ($ship_to_code) {
                    return $address['address_code'] === $ship_to_code;
                })->first();
            }

            // Prepare Items for Service
            $service_items = [];
            $warnings = $group['meta']['warnings'] ?? [];

            foreach ($group['items'] as $sku => $item_data) {
                $product = Product::where('stock_code', $sku)->first();

                if ($product) {
                    $uom_data = [];
                    foreach ($item_data['uoms'] as $uom => $qty) {
                        $uom_data[$uom] = [
                            'quantity' => $qty,
                            'paf_rows' => []
                        ];
                    }

                    $service_items[$product->id] = [
                        'product' => $product,
                        'data' => $uom_data
                    ];
                } else {
                    $warnings[] = [
                        'stock_code' => $sku,
                        'reason'     => 'SKU not found in the product catalog.',
                    ];
                }
            }

            $calculated_orders = $this->salesOrderService->calculateOrderTotals($service_items, $this->account);

            $warnings = array_merge($warnings, $calculated_orders['skipped_items'] ?? []);

            // Prepare Data (Lines)
            $display_lines = [];
            if (isset($calculated_orders['items'])) {
                foreach ($calculated_orders['items'] as $prod_id => $details) {

                    if (isset($details['data'])) {
                        foreach ($details['data'] as $uom => $val) {
                            $display_lines[] = [
                                'sku_code' => $details['stock_code'],
                                'product' => Product::find($prod_id), // Or optimize this
                                'uom' => $uom,
                                'quantity' => $val['quantity'],
                                'total' => $val['total'],
                                'total_less_discount' => $val['discounted'],
                                'line_discount' => $val['discount']
                            ];
                        }
                    }
                }
            }

            // Populate Final Array
            $final_data[$po_number] = [
                'key' => 'so' . $index++,
                'scanned_from' => $group['meta']['scanned_from'] ?? null,
                'ship_to_address' => $ship_to_code,
                'shipping_address' => !empty($shipping_address) ? $shipping_address : [],
                'ship_date' => $group['meta']['ship_date'],
                'ship_date_error' => $group['meta']['ship_date_error'],
                'po_value' => $group['meta']['po_value'],
                'paf_number' => $group['meta']['paf_number'],
                'shipping_instruction' => $group['meta']['shipping_instruction'],
                'discount' => $this->account->discount,
                'lines' => $display_lines,
                'warnings' => $warnings,
                'calculated_data' => $calculated_orders,
                'service_items_input' => $service_items // Store input just in case we need to recalculate
            ];
        }

        $this->so_data = $final_data;

        /** Flag unreadable dates straight away so the user fixes the sheet before saving. */
        foreach($final_data as $po_number => $data) {
            if(!empty($data['ship_date_error'])) {
                $this->err_data[$po_number]['ship_date'] = $data['ship_date_error'];
            }
        }
    }

    public function saveSalesOrder($status, $po_number) {
        $data = $this->so_data[$po_number];
        $err = [];

        if(empty($data['lines'])) {
            $err['lines'] = 'Please add items first';
        }
        if(empty($data['po_value']) || $data['po_value'] <= 0) {
            $err['po_value'] = 'PO value is required';
        }

        // Date Validation
        if(!empty($data['ship_date_error'])) {
            $err['ship_date'] = $data['ship_date_error'];
        } elseif(empty($data['ship_date'])) {
            $err['ship_date'] = 'Ship date is required.';
        } else {
            $poProcessDays = (int) ($this->account->po_process_date ?? 1);
            $poProcessDays = $poProcessDays > 0 ? $poProcessDays : 1;
            $orderDate = \Carbon\Carbon::now()->startOfDay();
            $leadDate = $orderDate->copy()->addWeekdays($poProcessDays)->startOfDay();

            try {
                $shipDate = \Carbon\Carbon::parse($data['ship_date'])->startOfDay();
                if ($shipDate->lt($leadDate)) {
                    $err['ship_date'] = 'The ship date must be at least '.$poProcessDays.' day(s) from the order date excluding weekends.';
                }
            } catch (\Exception $e) {
                $err['ship_date'] = 'Invalid ship date format.';
            }
        }

        // PO Number Validation
        if(empty($po_number)) {
            $err['po_number'] = 'PO number is required';
        } else {
            $check1 = SalesOrder::where('po_number', $po_number)->withTrashed()->exists();
            $check2 = PurchaseOrderNumber::where('po_number', $po_number)->exists();
            if($check1 || $check2) {
                $err['po_number'] = 'PO number already exists';
            }
        }

        if(!empty($data['shipping_instruction']) && strlen($data['shipping_instruction']) > 50) {
            $err['shipping_instruction'] = 'Shipping instruction must not exceed 50 characters';
        }

        $attachment = $this->attachments[$data['key'] ?? ''] ?? null;

        // --- PROCESSING ---
        if(empty($err)) {
            try {
                // 1. RE-HYDRATE PRODUCTS
                $service_input = $data['service_items_input'];
                $product_ids = array_keys($service_input);
                // Fetch fresh Product models
                $products = Product::whereIn('id', $product_ids)->get()->keyBy('id');

                foreach($service_input as $pid => &$item) {
                    if(isset($products[$pid])) {
                        $item['product'] = $products[$pid]; // Replace array with Model
                    }
                }
                unset($item); // Break reference

                // 2. Prepare Data Object
                $orderDataObj = new Fluent([
                    'shipping_address_id' => $data['shipping_address']['id'] ?? 'default',
                    // Note: PO Prefix handling
                    'po_number' => $po_number,
                    'paf_number' => $data['paf_number'],
                    'order_date' => date('Y-m-d'),
                    'ship_date' => $data['ship_date'],
                    'shipping_instruction' => $data['shipping_instruction'],
                    'ship_to_name' => !empty($data['shipping_address'])
                        ? $data['shipping_address']['address_code'].' - '.$data['shipping_address']['ship_to_name']
                        : $this->account->account_name,
                    'ship_to_address1' => $data['shipping_address']['building'] ?? $this->account->ship_to_address1,
                    'ship_to_address2' => $data['shipping_address']['street'] ?? $this->account->ship_to_address2,
                    'ship_to_address3' => $data['shipping_address']['city'] ?? $this->account->ship_to_address3,
                    'postal_code' => $data['shipping_address']['postal'] ?? $this->account->postal_code,
                    'status' => $status,
                ]);

                // Strip prefix if necessary (Service will add it back)
                if (!empty($this->account->po_prefix) && str_starts_with($po_number, $this->account->po_prefix)) {
                    $orderDataObj->po_number = substr($po_number, strlen($this->account->po_prefix));
                }

                // 3. Recalculate with Valid Objects
                $calculated_orders = $this->salesOrderService->calculateOrderTotals(
                    $service_input,
                    $this->account
                );

                $calculated_orders['po_value'] = $data['po_value'];

                // 4. Create Order
                $sales_order = $this->salesOrderService->createOrder(
                    $orderDataObj,
                    $this->account,
                    $calculated_orders
                );

                if (!empty($attachment)) {
                    $source = $attachment->getClientOriginalName() === ($data['scanned_from'] ?? null)
                        ? SalesOrderAttachment::SOURCE_SCAN
                        : SalesOrderAttachment::SOURCE_UPLOAD;

                    app(SalesOrderAttachmentService::class)->attach($sales_order, $attachment, $source);
                }

                // Logs
                activity('create')
                    ->performedOn($sales_order)
                    ->log(':causer.firstname :causer.lastname has created sales order :subject.control_number [ :subject.po_number ]');

                if($status == 'finalized' && config('sales-order.xml_generation_enabled')) {
                    GenerateSalesOrderXml::dispatch($sales_order);
                }

                $this->success_data[$po_number] = [
                    'message' => 'Sales Order '.$sales_order->control_number.' has been created.',
                    'control_number' => $sales_order->control_number,
                    'status' => $status
                ];

            } catch (\Exception $e) {
                $err['general'] = 'Error creating order: ' . $e->getMessage();
                $this->err_data[$po_number] = $err;
            }

        } else {
            $this->err_data[$po_number] = $err;
        }
    }

    /**
     * Checks a PO attachment as soon as it is chosen for a card.
     */
    public function updatedAttachments($file, $key) {
        $validator = Validator::make(['file' => $file], ['file' => SalesOrderAttachmentService::rules()], [
            'file.mimes' => 'The PO attachment must be one of these file types: :values.',
            'file.max'   => 'The PO attachment must not be larger than :max KB.',
        ]);

        if ($validator->fails()) {
            unset($this->attachments[$key]);
            $this->addError('attachments.' . $key, $validator->errors()->first('file'));
        }
    }

    public function removeAttachment($key) {
        unset($this->attachments[$key]);
    }

    /**
     * Lets the user fill in a ship date or PO value the sheet or PDF did not give, before saving.
     */
    public function updateOrderField($key, $field, $value) {
        $po_number = collect($this->so_data ?? [])->search(fn ($data) => ($data['key'] ?? null) === $key);

        if ($po_number === false || !empty($this->success_data[$po_number]) || !in_array($field, ['ship_date', 'po_value'], true)) {
            return;
        }

        if ($field === 'ship_date') {
            $this->so_data[$po_number]['ship_date'] = UploadDateHelper::parse($value);
            $this->so_data[$po_number]['ship_date_error'] = UploadDateHelper::error($value, 'Ship date');
        } else {
            $this->so_data[$po_number]['po_value'] = (float) str_replace(',', '', (string) $value);
        }

        unset($this->err_data[$po_number][$field]);
    }

    public function saveAll($status) {
        $this->reset(['success_data', 'err_data']);
        foreach($this->so_data as $po_number => $data) {
            $this->saveSalesOrder($status, $po_number);
        }
    }

    public function setSoData() {
        $this->emit('setSummary', $this->so_data, $this->logged_account);
    }

    public function render() {
        return view('livewire.sales-order.multiple.upload');
    }
}
