<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\Product;
use App\Models\PriceCode;

class SalesOrderTotal extends Component
{

    public $logged_account, $account, $discount, $orders = [];
    public $total = '0.00';
    public $grand_total = '0.00';

    protected $listeners = ['getTotal', 'getTotal'];

    public function getTotal($product_details) {
        $orders = [];
        $total = 0;
        if(!empty($product_details)) {
            foreach($product_details as $product_id => $details) {
                // get product details
                $product = Product::findOrFail($product_id);
                $orders[$product_id] = [
                    'stock_code' => $product->stock_code,
                    'description' => $product->description,
                    'size' => $product->size,
                ];
                
                $product_total = 0;
                foreach($details as $uom => $quantity) {
                    // check price code of product
                    $price_code = PriceCode::where('company_id', $this->account->company_id)
                    ->where('product_id', $product->id)
                    ->where('code', $this->account->price_code)
                    ->first();
                    // get price
                    $selling_price = $price_code->selling_price;
    
                    $uom_total = 0;
                    // check if stock UOM
                    if($uom == $product->stock_uom) {
                        $uom_total += (int)$quantity * $selling_price;
                    } else if($uom == $product->order_uom) { // order UOM
                        // check operation
                        if($product->order_uom_operator == 'M') { // multiply
                            $uom_total += ((int)$quantity * $product->order_uom_conversion) * $selling_price;
                        }
                        if($product->order_uom_operator == 'D') { // Divide
                            $uom_total += ((int)$quantity / $product->order_uom_conversion) * $selling_price;
                        }
                    } else if($uom == $product->other_uom) { // Other UOM
                        // check operation
                        if($product->other_uom_operator == 'M') { // multiply
                            $uom_total += ((int)$quantity * $product->other_uom_conversion) * $selling_price;
                        }
                        if($product->other_uom_operator == 'D') { // Divide
                            $uom_total += ((int)$quantity / $product->other_uom_conversion) * $selling_price;
                        }
                    }

                    if($uom_total > 0) {
                        $orders[$product_id]['data'][$uom] = [
                            'quantity' => number_format($quantity),
                            'total' => number_format($uom_total, 2)
                        ];
                    }
                    $product_total += $uom_total;
                }
                if($product_total > 0) {
                    $orders[$product_id]['product_total'] = $product_total;
                } else {
                    unset($orders[$product_id]);
                }
                $total += $product_total;
            }
        }

        $this->total = number_format($total, 2);

        $discounted = $total;
        if($this->discount->discount_1 > 0) {
            $discounted = $discounted * ((100 - $this->discount->discount_1) / 100);
        }
        if($this->discount->discount_2 > 0) {
            $discounted = $discounted * ((100 - $this->discount->discount_2) / 100);
        }
        if($this->discount->discount_3 > 0) {
            $discounted = $discounted * ((100 - $this->discount->discount_3) / 100);
        }

        $this->grand_total = number_format($discounted, 2);
        $this->orders = $orders;
    }

    public function mount() {
        $this->logged_account = auth()->user()->logged_account();
        $this->account = $this->logged_account->account;
        $this->discount = $this->account->discount;
    }

    public function render()
    {
        return view('livewire.sales-order.sales-order-total');
    }
}
