<?php

namespace App\Http\Livewire\SalesOrder\Multiple;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Fluent;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\PurchaseOrderNumber;
use App\Models\ShippingAddress;

use App\Services\SalesOrderService;

class Upload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';


    public $logged_account;
    public $account;
    public $shipping_addresses;
    public $setting;
    public $so_file;
    public $so_data;
    public $err_data;
    public $success_data;

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
            'so_file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->so_file->storeAs('multiple-so', $this->so_file->getClientOriginalName());
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

    private function processData($data) {
        $this->reset(['so_data', 'err_data', 'success_data']);

        $grouped_data = [];

        // 1. Group Raw Data by PO Number
        foreach($data as $key => $row) {
            if(!empty(trim($row[0])) && $key != 0) {

                $po_raw = trim($row[0]);
                $po_number = !empty($this->account->po_prefix) ? $this->account->po_prefix . $po_raw : $po_raw;

                // Parse Dates
                $ship_date = $row[1];
                if(is_int($ship_date)) {
                    $ship_date = Date::excelToDateTimeObject($ship_date)->format('Y-m-d');
                } else {
                    $dateTime = \DateTime::createFromFormat('m-d-Y', $ship_date);
                    $ship_date = ($dateTime !== false) ? $dateTime->format('Y-m-d') : $ship_date;
                }

                // Initialize PO Group if not exists
                if (!isset($grouped_data[$po_number])) {
                    $grouped_data[$po_number] = [
                        'meta' => [
                            'ship_date' => $ship_date,
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

        $final_data = [];

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
                }
            }

            $calculated_orders = $this->salesOrderService->calculateOrderTotals($service_items, $this->account);

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
                'ship_to_address' => $ship_to_code,
                'shipping_address' => !empty($shipping_address) ? $shipping_address : [],
                'ship_date' => $group['meta']['ship_date'],
                'po_value' => $group['meta']['po_value'],
                'paf_number' => $group['meta']['paf_number'],
                'shipping_instruction' => $group['meta']['shipping_instruction'],
                'discount' => $this->account->discount,
                'lines' => $display_lines,
                'calculated_data' => $calculated_orders,
                'service_items_input' => $service_items // Store input just in case we need to recalculate
            ];
        }

        $this->so_data = $final_data;
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
        if(empty($data['ship_date'])) {
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
                    'ship_to_address2' => $data['shipping_address']['street'] ?? $this->account->ship_to_address1,
                    'ship_to_address3' => $data['shipping_address']['city'] ?? $this->account->ship_to_address1,
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

                // Logs
                activity('create')
                    ->performedOn($sales_order)
                    ->log(':causer.firstname :causer.lastname has created sales order :subject.control_number [ :subject.po_number ]');

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
