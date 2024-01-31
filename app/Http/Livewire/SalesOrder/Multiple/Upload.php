<?php

namespace App\Http\Livewire\SalesOrder\Multiple;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\PurchaseOrderNumber;
use App\Models\ShippingAddress;

use App\Http\Traits\SoProductPriceTrait;
use App\Http\Traits\GlobalTrait;

class Upload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    use SoProductPriceTrait;
    use GlobalTrait;

    public $logged_account;
    public $account;
    public $shipping_addresses;
    public $setting;
    public $so_file;
    public $so_data;
    public $err_data;
    public $success_data;

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
            $rowResults = []; // Array to store results for this row
            foreach ($row->getCellIterator() as $cell) {
                $rowResults[] = $cell->getCalculatedValue(); // Store the result of the formula
            }
            $data[] = $rowResults; // Store the results for this row in the main results array
        }

        $this->processData($data);
    }

    private function processData($data) {
        $this->reset([
            'so_data',
            'err_data',
            'success_data'
        ]);

        $data_arr = array();
        foreach($data as $key => $row) {
            if(!empty(trim($row[0]))) {
                if($key != 0) {
    
                    $account = $this->account;
                    $discount = $account->discount;
    
                    $po_number = trim($row[0]);
                    $ship_date = $row[1];
                    $ship_to_address = trim($row[2]);
                    $sku_code = trim($row[3]);
                    $quantity = (int)trim($row[4]);
                    $uom = strtoupper(trim($row[5]));
                    $po_value = (float)trim($row[6]);
                    $paf_number = trim($row[7]);
                    $shipping_instruction = trim($row[8]);
    
                    $shipping_address = array();
                    if(!empty($ship_to_address) && !empty($this->shipping_addresses)) {
                        $shipping_address = $this->shipping_addresses->filter(function ($address) use ($ship_to_address) {
                            return $address['address_code'] === $ship_to_address;
                        })->first();
                    }
    
                    if(is_int($ship_date)) {
                        $ship_date = Date::excelToDateTimeObject($ship_date)->format('Y-m-d');
                    }
    
                    $data_arr[$po_number]['ship_to_address'] = $ship_to_address;
                    $data_arr[$po_number]['shipping_address'] = !empty($shipping_address) ? $shipping_address : $data_arr[$po_number]['shipping_address'] ?? [];
                    $data_arr[$po_number]['ship_date'] = $ship_date;
                    $data_arr[$po_number]['po_value'] = !empty($data_arr[$po_number]['po_value']) ? $data_arr[$po_number]['po_value'] + $po_value ?? 0 : $po_value ?? 0;
                    $data_arr[$po_number]['paf_number'] = !empty($paf_number) ? $paf_number : $data_arr[$po_number]['paf_number'] ?? '';
                    $data_arr[$po_number]['shipping_instruction'] = !empty($shipping_instruction) ? $shipping_instruction : $data_arr[$po_number]['shipping_instruction'] ?? '';
                    $data_arr[$po_number]['discount'] = $discount;
                    
                    $product = Product::where('stock_code', $sku_code)
                        ->first();
    
                    $total_val = array();
                    if(!empty($product) && !empty($quantity) && !empty($uom)) {
                        $total_val = $this->getProductPrice($product, $account, $uom, $quantity);
                    }
    
                    $data_arr[$po_number]['lines'][] = [
                        'sku_code' => $sku_code,
                        'product' => $product ?? '',
                        'uom' => $uom,
                        'quantity' => $quantity,
                        'total' => $total_val['total'] ?? 0,
                        'total_less_discount' => $total_val['discounted'] ?? 0,
                        'line_discount' => $total_val['line_discount'] ?? NULL
                    ];
                }
            }
        }

        $this->so_data = $data_arr;

    }

    private function generateControlNumber() {
        $date_code = date('Ymd');

        do {
            $control_number = 'SO-'.$date_code.'-001';
            // get the most recent sales order
            $sales_order = SalesOrder::withTrashed()->orderBy('control_number', 'DESC')
                ->first();
            if(!empty($sales_order)) {
                $latest_control_number = $sales_order->control_number;
                list(, $prev_date, $last_number) = explode('-', $latest_control_number);

                // Increment the number based on the date
                $number = ($date_code == $prev_date) ? ((int)$last_number + 1) : 1;

                // Format the number with leading zeros
                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                // Construct the new control number
                $control_number = "SO-$date_code-$formatted_number";
            }

        } while(SalesOrder::withTrashed()->where('control_number', $control_number)->exists());

        return $control_number;
    }

    public function saveSalesOrder($status, $po_number) {
        // validate
        $data = $this->so_data[$po_number];
        $err = array();
        if(empty($data['lines'])) {
            $err['lines'] = 'Please add items first';
        }
        if(empty($data['po_value']) || $data['po_value'] <= 0) {
            $err['po_value'] = 'PO value is required';
        }
        if(empty($data['ship_date'])) {
            $err['ship_date'] = 'Ship date is required.';
        }
        if(empty($po_number)) {
            $err['po_number'] = 'PO number is required';
        } else {
            // check for duplicates
            $check1 = SalesOrder::where('po_number', $po_number)->withTrashed()->exists();
            $check2 = PurchaseOrderNumber::where('po_number', $po_number)->exists();
            if(!empty($check1) || !empty($check2)) {
                $err['po_number'] = 'Po number already exists';
            }
        }

        if(empty($err)) {
            // create sales order
            $control_number = $this->generateControlNumber();

            $ship_to_name = $this->account->account_name;
            $ship_to_building = $this->account->ship_to_address1;
            $ship_to_street = $this->account->ship_to_address1;
            $ship_to_city = $this->account->ship_to_address1;
            $postal = $this->account->postal_code;
            if(!empty($data['shipping_address'])) {
                $ship_to_name = $data['shipping_address']['ship_to_name'];
                $ship_to_building = $data['shipping_address']['building'];
                $ship_to_street = $data['shipping_address']['street'];
                $ship_to_city = $data['shipping_address']['city'];
                $postal = $data['shipping_address']['postal'];
            }

            $sales_order = new SalesOrder([
                'account_login_id' => $this->logged_account->id,
                'shipping_address_id' => $data['shipping_address']['id'] ?? NULL,
                'control_number' => $control_number,
                'po_number' => $po_number,
                'paf_number' => $data['paf_number'],
                'reference' => NULL,
                'order_date' => date('Y-m-d'),
                'ship_date' => $data['ship_date'],
                'shipping_instruction' => $data['shipping_instruction'],
                'ship_to_name' => $ship_to_name,
                'ship_to_building' => $ship_to_building,
                'ship_to_street' => $ship_to_street,
                'ship_to_city' => $ship_to_city,
                'ship_to_postal' => $postal,
                'status' => $status,
                'total_quantity' => 0,
                'total_sales' => 0,
                'grand_total' => 0,
                'po_value' => $data['po_value'],
            ]);
            $sales_order->save();

            $num = 0;
            $limit = $this->account->company->order_limit ?? $this->setting->sales_order_limit;
            // CUSTOM LIMIT FOR WATSON
            if($this->account->short_name == 'WATSONS') {
                $curr_limit = 23;
            } else {
                $curr_limit = $limit;
            }

            $total_quantity = 0;
            $total_sales = 0;
            $part = 1;
            foreach($data['lines'] as $item) {
                $num++;

                // divide by parts
                if($num > $curr_limit) {
                    $curr_limit += $limit;
                    $part++;
                }

                $sales_order_product = new SalesOrderProduct([
                    'sales_order_id' => $sales_order->id,
                    'product_id' => $item['product']['id'],
                    'part' => $part,
                    'total_quantity' => $item['quantity'],
                    'total_sales' => $item['total'],
                ]);
                $sales_order_product->save();

                $sales_order_product_uom = new SalesOrderProductUom([
                    'sales_order_product_id' => $sales_order_product->id,
                    'uom' => $item['uom'],
                    'quantity' => $item['quantity'],
                    'uom_total' => $item['total'],
                    'uom_total_less_disc' => $item['total_less_discount'],
                ]);
                $sales_order_product_uom->save();

                $total_quantity += $item['quantity'];
                $total_sales += $item['total'];
            }

            // apply discount
            $grand_total = $total_sales;
            if(!empty($data['discount'])) {
                $discounts = [$data['discount']['discount_1'], $data['discount']['discount_2'], $data['discount']['discount_3']];

                foreach ($discounts as $discountValue) {
                    if ($discountValue > 0) {
                        $grand_total = $grand_total * ((100 - $discountValue) / 100);
                    }
                }
            }

            $sales_order->update([
                'total_quantity' => $total_quantity,
                'total_sales' => $total_sales,
                'grand_total' => $grand_total
            ]);

            // logs
            activity('create')
                ->performedOn($sales_order)
                ->log(':causer.firstname :causer.lastname has created sales order :subject.control_number [ :subject.po_number ]');

            $this->success_data[$po_number] = [
                'message' => 'Sales Order '.$control_number.' has been created.',
                'control_number' => $control_number,
                'status' => $status
            ];
        } else {
            $this->err_data[$po_number] = $err;
        }
    }

    public function saveAll($status) {
        $this->reset([
            'success_data',
            'err_data',
        ]);

        foreach($this->so_data as $po_number => $data) {
            $this->saveSalesOrder($status, $po_number);
        }
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

        $this->setting = $this->getSettings();
    }

    public function render() {
        return view('livewire.sales-order.multiple.upload');
    }
}
