<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\Product;
use App\Models\PurchaseOrder;

use App\Http\Traits\GlobalTrait;

class Create extends Component
{
    use GlobalTrait;

    public $setting;

    public $selectedPO;
    public $logged_account;
    public $po_value;
    public $checked;

    public $alerts;

    protected $listeners = [
        'setSelectedAddress' => 'setSelectedAddress'
    ];

    public function setSelectedAddress($address, $po_id) {
        $this->selectedPO[$po_id]['selected_address'] = $address;
    }

    public function saveAll($status) {
        foreach($this->selectedPO as $po_id => $data) {
            $this->saveSO($status, $po_id);
        }
    }

    public function saveSO($status, $po_id) {
        unset($this->alerts[$po_id]);

        // validate data
        $so_data = $this->selectedPO[$po_id];
        $validator = Validator::make($so_data, [
            'po_number' => [
                'required',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                Rule::unique((new SalesOrder)->getTable()),
                Rule::unique('purchase_order_numbers')->where('company_id', $this->logged_account->account->company_id),
                'max:30'
            ],
            'ship_date' => [
                'required',
                // function ($attribute, $value, $fail) {
                    
                //     if (!empty($this->logged_account->account->po_process_date)) {
                //         // Check if the ship date is at least 3 days from the order date
                //         $leadDate = Carbon::parse(date('Y-m-d'))->addWeekdays($this->logged_account->account->po_process_date)->startOfDay();
                //         $shipDate = Carbon::parse($value)->startOfDay();
                        
                //         if($shipDate < $leadDate) {
                //             $fail('The ship date must be at least '.$this->logged_account->account->po_process_date.' day/s from the order date excluding saturday and sunday.');
                //         }
                //     } else {
                //         $leadDate = Carbon::parse(date('Y-m-d'))->addWeekdays(1)->startOfDay();
                //         $shipDate = Carbon::parse($value)->startOfDay();

                //         if($shipDate < $leadDate) {
                //             $fail('The ship date must be at least 1 day from the order date.');
                //         }
                //     }
                // },
            ],
            'shipping_instruction' => [
                'max:1000'
            ],
        ]);

        if($validator->fails()) {
            $this->alerts[$po_id]['error'] = 'Theres an error encountered.';
        } else {
            $ship_to_name = $this->logged_account->account->ship_to_address1;
            $ship_to_building = $this->logged_account->account->ship_to_address2;
            $ship_to_street = $this->logged_account->account->ship_to_address3;
            $ship_to_city = $this->logged_account->account->postal_code;
            $ship_to_postal = $this->logged_account->account->tax_number;
            if(!empty($so_data['selected_address'])) {
                $ship_to_name = $so_data['selected_address']['ship_to_name'];
                $ship_to_building = $so_data['selected_address']['building'];
                $ship_to_street = $so_data['selected_address']['street'];
                $ship_to_city = $so_data['selected_address']['city'];
                $ship_to_postal = $so_data['selected_address']['tin'];
            }
            
            $sales_order = new SalesOrder([
                'account_login_id' => $this->logged_account->id,
                'shipping_address_id' => $so_data['selected_address']['id'] ?? NULL,
                'control_number' => $this->generateControlNumber(),
                'po_number' => $so_data['po_number'],
                'order_date' => date('Y-m-d'),
                'ship_date' => $so_data['ship_date'],
                'shipping_instruction' => $so_data['shipping_instruction'],
                'ship_to_name' => $ship_to_name,
                'ship_to_building' => $ship_to_building,
                'ship_to_street' => $ship_to_street,
                'ship_to_city' => $ship_to_city,
                'ship_to_postal' => $ship_to_postal,
                'status' => $status
            ]);
            $sales_order->save();
    
            $total_quantity = 0;
            $total_sales = 0;
            $grand_total = 0;
    
            $num = 0;
            $part = 1;
            $limit = $this->logged_account->account->order_limit ?? $this->setting->sales_order_limit;
            // CUSTOM LIMIT FOR WATSON
            if($this->logged_account->account->short_name == 'WATSONS') {
                $curr_limit = 23;
            } else {
                $curr_limit = $limit;
            }
    
            // get CRISTALINO [CSP10001, CSP10002, CSP10003]
            $cristalino_prod_ids = Product::whereIn('stock_code', ['CSP10001', 'CSP10002', 'CSP10003'])->get()->pluck('id')->toArray();
            // get KS01046
            $ks_1046 = Product::where('stock_code', 'KS01046')->first();
    
            $cristalino_data = array();
            $ks_1046_data = array();
            
            foreach($so_data['products'] as $product) {
                if($this->checked[$po_id][$product['product_id']]) {
                    // uom conversion
                    $uom = $product['unit_of_measure'];
                    switch(strtoupper($uom)) {
                        case 'CASE':
                            $uom = 'CS';
                            break;
                        case 'CAS':
                            $uom = 'CS';
                            break;
                        case 'PC': 
                            $uom = 'PCS';
                            break;
                    }
    
                    // separate cristalino
                    if(in_array($product['product_id'], $cristalino_prod_ids)) {
                        $cristalino_data[$product['product_id']] = $product;
                    } else if($product['product_id'] == $ks_1046->id) { // separate ks KS01046
                        $ks_1046_data[$product['product_id']] = $product;
                    } else {
                        $num++;
                        // divide by parts
                        if($num > $curr_limit) {
                            $curr_limit += $limit;
                            $part++;
                        }

                        $total_quantity += $product['quantity'];
                        $total_sales += $product['total'];
                        $grand_total += $product['total_less_discount'];
        
                        $sales_order_product = new SalesOrderProduct([
                            'sales_order_id' => $sales_order->id,
                            'product_id' => $product['product_id'],
                            'quantity' => $product['quantity'],
                            'part' => $part,
                            'total_quantity' => $product['quantity'],
                            'total_sales' => $product['total'],
                        ]);
                        $sales_order_product->save();
        
                        $sales_order_product_uom = new SalesOrderProductUom([
                            'sales_order_product_id' => $sales_order_product->id,
                            'uom' => $uom,
                            'quantity' => $product['quantity'],
                            'uom_total' => $product['total'],
                            'uom_total_less_disc' => $product['total_less_discount']
                        ]);
                        $sales_order_product_uom->save();
                    }
                }
            }
    
            // save cristalino in other parts
            if(!empty($cristalino_data)) {
                $part = $part + 1;
                foreach($cristalino_data as $product_id => $items) {

                    $total_quantity += $items['quantity'];
                    $total_sales += $items['total'];
                    $grand_total += $items['total_less_discount'];

                    $sales_order_product = new SalesOrderProduct([
                        'sales_order_id' => $sales_order->id,
                        'product_id' => $product_id,
                        'part' => $part,
                        'total_quantity' => $items['quantity'],
                        'total_sales' => $items['total'],
                    ]);
                    $sales_order_product->save();
                    
                    $sales_order_product_uom = new SalesOrderProductUom([
                        'sales_order_product_id' => $sales_order_product->id,
                        'uom' => $uom,
                        'quantity' => $items['quantity'],
                        'uom_total' => $items['total'],
                        'uom_total_less_disc' => $items['total_less_discount']
                    ]);
                    $sales_order_product_uom->save();
                }
            }
    
            // save KS01046
            if(!empty($ks_1046_data)) {
                $part = $part + 1;
                foreach($ks_1046_data as $product_id => $items) {
                    $total_quantity += $items['quantity'];
                    $total_sales += $items['total'];
                    $grand_total += $items['total_less_discount'];

                    $sales_order_product = new SalesOrderProduct([
                        'sales_order_id' => $sales_order->id,
                        'product_id' => $product_id,
                        'part' => $part,
                        'total_quantity' => $items['quantity'],
                        'total_sales' => $items['total'],
                    ]);
                    $sales_order_product->save();
        
                    $sales_order_product_uom = new SalesOrderProductUom([
                        'sales_order_product_id' => $sales_order_product->id,
                        'uom' => $uom,
                        'quantity' => $items['quantity'],
                        'uom_total' => $items['total'],
                        'uom_total_less_disc' => $items['total_less_discount']
                    ]);
                    $sales_order_product_uom->save();
                }
            }

            $discount = $this->logged_account->account->discount;
            if(!empty($discount->discount_1)) {
                $grand_total = $grand_total * ((100 - $discount->discount_1) / 100);
            }
            if(!empty($discount->discount_2)) {
                $grand_total = $grand_total * ((100 - $discount->discount_2) / 100);
            }
            if(!empty($discount->discount_3)) {
                $grand_total = $grand_total * ((100 - $discount->discount_3) / 100);
            }

            $sales_order->update([
                'total_quantity' => $total_quantity,
                'total_sales' => $total_sales,
                'grand_total' => $grand_total,
                'po_value' => $grand_total
            ]);

            $this->selectedPO[$po_id]['control_number'] = $sales_order->control_number;

            // update po status
            $po = PurchaseOrder::find($po_id);
            $po->update([
                'status' => $sales_order->control_number
            ]);
    
            // logs
            activity('create')
                ->performedOn($sales_order)
                ->log(':causer.firstname :causer.lastname has created sales order :subject.control_number :subject.po_number');
            
            $this->alerts[$po_id]['success'] = 'Sales Order has been created successfully.';
        }

    }

    public function generateControlNumber() {
        $control_number = null;
        do {
            $date_code = date('Ymd');
            $sales_order = SalesOrder::withTrashed()->orderBy('control_number', 'DESC')->first();
            if (!empty($sales_order)) {
                // increment control number
                $control_number_arr = explode('-', $sales_order->control_number);
                $last = end($control_number_arr);
                array_pop($control_number_arr);
                $prev_date = end($control_number_arr);
                array_pop($control_number_arr);
                if ($date_code == $prev_date) { // same day increment number
                    $number = (int)$last + 1;
                } else { // reset on different day
                    $number = 1;
                }
                $number = str_pad($number, 3, '0', STR_PAD_LEFT); // ensure the number is 3 digits
                array_push($control_number_arr, $date_code);
                array_push($control_number_arr, $number);
                $control_number = implode('-', $control_number_arr);
            } else {
                // First control number for the day
                $control_number = $date_code . '-001';
            }
        } while (SalesOrder::withTrashed()->where('control_number', $control_number)->exists());

        return $control_number;
    }

    public function clearAddress($po_id) {
        unset($this->selectedPO[$po_id]['selected_address']);
    }

    public function mount($selectedPO) {
        $this->setting = $this->getSettings();
        $this->selectedPO = $selectedPO;
        $this->logged_account = Session::get('logged_account');

        foreach($this->selectedPO as $po_id => $order) {
            $total = 0;
            $total_net = 0;
            foreach($order['products'] as $product) {
                $total += $product['total'];
                $total_net += $product['total_less_discount'];
                if(!empty(trim($product['product_name']))) {
                    $this->checked[$po_id][$product['product_id']] = true;
                }
            }

            $discount = $this->logged_account->account->discount;
            if(!empty($discount)) {
                if(!empty($discount->discount_1)) {
                    $total_net = $total_net * ((100 - $discount->discount_1) / 100);
                }
                if(!empty($discount->discount_2)) {
                    $total_net = $total_net * ((100 - $discount->discount_2) / 100);
                }
                if(!empty($discount->discount_3)) {
                    $total_net = $total_net * ((100 - $discount->discount_3) / 100);
                }
            }

            $this->po_value[$po_id] = [
                'total' => $total,
                'total_net' => $total_net
            ];
            $this->selectedPO[$po_id]['total_sales'] = $total;
            $this->selectedPO[$po_id]['grand_total'] = $total_net;
            $this->selectedPO[$po_id]['po_value'] = $total_net;
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.create');
    }
}
