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

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\PurchaseOrderNumber;

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
        $data_arr = array();
        foreach($data as $key => $row) {
            if($key != 0) {

                $account = $this->account;
                $discount = $account->discount;

                $shipping_address = array();
                if(!empty($row['4']) && !empty($this->shipping_addresses)) {
                    $shipping_address = $this->shipping_addresses->where('address_code', $row[4])
                        ->first();
                }

                $ship_date = $row[2];
                if(is_int($ship_date)) {
                    $ship_date = Date::excelToDateTimeObject($ship_date)->format('Y-m-d');
                }

                $data_arr[$row[0]]['paf_number'] = $row[1];
                $data_arr[$row[0]]['ship_date'] = $ship_date;
                $data_arr[$row[0]]['shipping_instruction'] = $row[3];
                $data_arr[$row[0]]['ship_to_address'] = $row[4];
                $data_arr[$row[0]]['shipping_address'] = $shipping_address;
                $data_arr[$row[0]]['po_value'] = $row[5];
                $data_arr[$row[0]]['discount'] = $discount;
                
                $product = Product::where('stock_code', $row[6])
                    ->first();

                $total_val = array();
                if(!empty($product) && !empty($row['7']) && !empty($row[8])) {
                    $total_val = $this->getProductPrice($product, $account, $row[7], $row[8]);
                }

                $data_arr[$row[0]]['lines'][] = [
                    'sku_code' => $row[6],
                    'product' => $product ?? '',
                    'uom' => $row[7],
                    'quantity' => $row[8],
                    'total' => $total_val['total'] ?? 0,
                    'total_less_discount' => $total_val['discounted'] ?? 0,
                    'line_discount' => $total_val['line_discount']
                ];
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
            $err[] = 'Please add items first';
        }
        if(empty($data['po_value']) || $data['po_value'] <= 0) {
            $err[] = 'PO value is required';
        }
        if(empty($data['ship_date'])) {
            $err[] = 'Ship date is required.';
        }
        if(empty($po_number)) {
            $err[] = 'PO number is required';
        } else {
            // check for duplicates
            $check1 = SalesOrder::where('po_number', $po_number)->withTrashed()->exists();
            $check2 = PurchaseOrderNumber::where('po_number', $po_number)->exists();
            if(!empty($check1) || !empty($check2)) {
                $err[] = 'Po number already exists';
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

            $this->success_data[$po_number] = [
                'message' => 'Sales Order has been created.',
                'control_number' => $control_number,
                'status' => $status
            ];
        } else {
            $this->err_data[$po_number] = $err;
        }
    }

    public function mount($logged_account) {
        $this->logged_account = $logged_account;
        $this->account = $logged_account->account;
        $this->shipping_address = $this->account->shipping_addresses;

        $this->setting = $this->getSettings();
    }

    public function render()
    {
        return view('livewire.sales-order.multiple.upload');
    }
}
