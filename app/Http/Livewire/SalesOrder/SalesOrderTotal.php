<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\Product;
use App\Models\PriceCode;
use App\Models\Discount;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Facades\Session;

use Livewire\WithPagination;

class SalesOrderTotal extends Component
{
    use WithPagination;

    public $logged_account,
    $account,
    $discount,
    $po_value = 0;

    public $po_message = '';

    public $orders = [];

    public $total = '0.00';
    public $grand_total = '0.00';

    protected $listeners = ['getTotal', 'getTotal'];
    protected $rules = [
        'po_number' => [
            'required', 'min:3'
        ]
    ];

    public function change_po_value() {
        // compare with total
        if($this->po_value != '') {
            if($this->po_value == str_replace(',', '', $this->total)) {
                $this->po_message = '';
            } else {
                $this->po_message = 'PO value does not match the total.';
            }
        } else {
            $this->po_message = 'Required';
        }

        $this->orders['po_value'] = $this->po_value;
    }

    public function getTotal($product_details) {
        $this->processDetails($product_details);
        $this->emit('saveData');
    }

    private function quantityConversion($quantity, $conversion, $operator, $reverse = false) {
        if($operator == 'M') { // mutiply
            if($reverse) {
                return $quantity / $conversion;
            } else {
                return $quantity * $conversion;
            }
        } elseif($operator == 'D') { // divide
            if($reverse) {
                return $quantity * $conversion;
            } else {
                return $quantity / $conversion;
            }
        }

        return $quantity;
    }

    private function uomQuantityAllocation($quantity, $product_id, $uom) {
        $data = [];

        // $product = Product::findOrFail($product_id);

        // // Identify which UOM is 'CS'
        // $cs_uom = null;
        // if ($product->stock_uom == 'CS') {
        //     $cs_uom = 'stock_uom';
        // } elseif ($product->order_uom == 'CS') {
        //     $cs_uom = 'order_uom';
        // } elseif ($product->other_uom == 'CS') {
        //     $cs_uom = 'other_uom';
        // }

        // if($uom !== 'CS' && !empty($cs_uom)) {
        //     $cs_quantity = $this->quantityConversion($quantity, $product->{$cs_uom.'_conversion'}, $product->{$cs_uom.'_operator'}, true);

        //     // Extract decimal part if exists
        //     $decimal_quantity = $cs_quantity - floor($cs_quantity);
        //     if ($decimal_quantity > 0) {
        //         $qty = $this->quantityConversion(
        //             $decimal_quantity, 
        //             $product->{$cs_uom . '_conversion'}, 
        //             $product->{$cs_uom . '_operator'}, 
        //             false
        //         );
        //         $data[$uom] = (int) floor($qty);
        //     }

        //     $data['CS'] = (int) floor($cs_quantity);
        // } else {
        //     $data[$uom] = $quantity;
        // }

        $data[$uom] = $quantity;

        return $data;
    }

    public function processDetails($product_details) {
        $orders = [];
        $total = 0;
        $total_quantity = 0;
        if(!empty($product_details)) {

            $line_discount = Discount::where('discount_code', $this->account->line_discount_code)
                ->where('company_id', $this->account->company_id)->first();

            foreach($product_details as $product_id => $details) {
                // get product details
                $product = Product::findOrFail($product_id);
                $orders['items'][$product_id] = [
                    'stock_code' => $product->stock_code,
                    'description' => $product->description,
                    'size' => $product->size,
                ];
                
                $product_total = 0;
                $product_quantity = 0;
                foreach($details as $c_uom => $c_quantity) {
                    // uomQuantityAllocation(quantity, product_id, uom)
                    $uom_data = $this->uomQuantityAllocation($c_quantity, $product->id, $c_uom);

                    foreach($uom_data as $uom => $quantity) {
                        // check price code of product
                        if($product->special_product) {
                            $special_product = $this->account->products()->where('product_id', $product->id)
                            ->first();
    
                            $code = $special_product->pivot->price_code ?? $this->account->price_code;
    
                            $price_code = PriceCode::where('company_id', $this->account->company_id)
                            ->where('product_id', $product->id)
                            ->where('code', $code)
                            ->first();
                        } else {
                            $price_code = PriceCode::where('company_id', $this->account->company_id)
                            ->where('product_id', $product->id)
                            ->where('code', $this->account->price_code)
                            ->first();
                        }
    
                        // get price
                        $selling_price = $price_code->selling_price;
                        $price_basis = $price_code->price_basis;
                        
                        // convert selling price to stock uom price
                        if($price_basis == 'A') {
                            if($product->order_uom_operator == 'M') { // multiply
                                $selling_price = $selling_price / $product->order_uom_conversion;
                            }
                            if($product->order_uom_operator == 'D') { // Divide
                                $selling_price = $selling_price * $product->order_uom_conversion;
                            }
                        } else if($price_basis == 'O') {
                            // check operation
                            if($product->other_uom_operator == 'M') { // multiply
                                $selling_price = $selling_price / $product->other_uom_conversion;
                            }
                            if($product->other_uom_operator == 'D') { // Divide
                                $selling_price = $selling_price * $product->other_uom_conversion;
                            }
                        }
    
                        $quantity = (float)$quantity;
    
                        // check account sales order UOM
                        if(!empty($this->account->sales_order_uom) && $uom != $this->account->sales_order_uom) {
                            if($product->order_uom == $this->account->sales_order_uom && $uom != $product->order_uom) {
                                if($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                } elseif($uom == $product->other_uom) {
                                    // check operation
                                    if($product->other_uom_operator == 'M') { // Multiply
                                        // convert to stock uom first
                                        $quantity = $quantity * $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    } elseif($product->other_uom_operator == 'D') { // Divide
                                        // convert to stock uom first
                                        $quantity = $quantity / $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->order_uom;
                            } else if($product->other_uom == $this->account->sales_order_uom && $uom != $product->other_uom) {
                                if($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                } else if($uom == $product->order_uom) {
                                    if($product->order_uom_operator == 'M') {
                                        // convert to stock uom
                                        $quantity = $quantity * $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    } elseif($product->order_uom_operator == 'D') {
                                        $quantity = $quantity / $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->other_uom;
                            } else if($product->stock_uom == $this->account->sales_order_uom && $uom != $product->stock_uom) {
                                if($uom == $product->order_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = false);
                                } else if($uom == $product->other_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = false);
                                }
    
                                $uom = $product->stock_uom;
                            }
                        }
        
                        $uom_total = 0;
                        // check if stock UOM
                        if($uom == $product->stock_uom) {
                            $uom_total += $quantity * $selling_price;
                        } else if($uom == $product->order_uom) { // order UOM
                            // check operation
                            if($product->order_uom_operator == 'M') { // multiply
                                $uom_total += ($quantity * $product->order_uom_conversion) * $selling_price;
                            }
                            if($product->order_uom_operator == 'D') { // Divide
                                $uom_total += ($quantity / $product->order_uom_conversion) * $selling_price;
                            }
                        } else if($uom == $product->other_uom) { // Other UOM
                            // check operation
                            if($product->other_uom_operator == 'M') { // multiply
                                $uom_total += ($quantity * $product->other_uom_conversion) * $selling_price;
                            }
                            if($product->other_uom_operator == 'D') { // Divide
                                $uom_total += ($quantity / $product->other_uom_conversion) * $selling_price;
                            }
                        }
    
                        // apply line discount
                        $uom_discounted = $uom_total;
                        if(!empty($line_discount)) {
                            $discounted = $total;
                            if($line_discount->discount_1 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_1) / 100);
                            }
                            if($line_discount->discount_2 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_2) / 100);
                            }
                            if($line_discount->discount_3 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_3) / 100);
                            }
                        }
    
                        if($uom_total > 0) {
                            $orders['items'][$product_id]['data'][$uom] = [
                                'quantity' => $quantity,
                                'total' => $uom_total,
                                'discount' => $line_discount->description ?? '0',
                                'discounted' => $uom_discounted,
                                'warehouse' => $product->order_uom !== $uom ? $product->warehouse : NULL,
                            ];
                        }
                        $product_total += $uom_discounted;
                        $product_quantity += $quantity;
                    }
                }

                if($product_total > 0) {
                    $orders['items'][$product_id]['product_total'] = $product_total;
                    $orders['items'][$product_id]['product_quantity'] = $product_quantity;
                } else {
                    unset($orders['items'][$product_id]);
                }

                $total += $product_total;
                $total_quantity += $product_quantity;
            }
        }

        $this->total = number_format($total, 2);

        // apply inventory discount
        $discounted = $total;
        if(!empty($this->discount)) {
            if($this->discount->discount_1 > 0) {
                $discounted = $discounted * ((100 - $this->discount->discount_1) / 100);
            }
            if($this->discount->discount_2 > 0) {
                $discounted = $discounted * ((100 - $this->discount->discount_2) / 100);
            }
            if($this->discount->discount_3 > 0) {
                $discounted = $discounted * ((100 - $this->discount->discount_3) / 100);
            }
        }

        $orders['total_quantity'] = $total_quantity;
        $orders['total'] = $total;
        $orders['discount_id'] = $this->discount->id ?? null;
        $orders['grand_total'] = $discounted;
        $orders['po_value'] = $this->po_value;

        $this->grand_total = number_format($discounted, 2);
        $this->orders = $orders;
    }

    public function mount() {
        $this->logged_account = Session::get('logged_account');
        $this->account = $this->logged_account->account;
        $this->discount = $this->account->discount;

        $order_data = Session::get('order_data');
        if(empty($this->orders) && !empty($order_data)) {
            $this->orders = $order_data;
        }

        if(!empty($order_data['total'])) {
            $this->total = number_format($order_data['total'], 2);
            $this->grand_total = number_format($order_data['grand_total'], 2);
            $this->po_value = $order_data['po_value'];
        }
    }

    public function render()
    {
        if(!empty($this->orders)) {
            Session::put('order_data', $this->orders);
        }
        return view('livewire.sales-order.sales-order-total');
    }
}
