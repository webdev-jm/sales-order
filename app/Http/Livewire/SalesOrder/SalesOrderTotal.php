<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\Product;
use App\Models\PriceCode;
use App\Models\Discount;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use Livewire\WithPagination;

class SalesOrderTotal extends Component
{
    use WithPagination;

    public $logged_account,
    $account,
    $discount;

    public $orders = [];

    public $total = '0.00';
    public $grand_total = '0.00';

    protected $listeners = ['getTotal', 'getTotal'];
    protected $rules = [
        'po_number' => [
            'required', 'min:3'
        ]
    ];

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

                    // apply line discount
                    $line_discount = Discount::where('discount_code', $this->account->line_discount_code)
                    ->where('company_id', $this->account->company_id)->first();
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
                        $orders[$product_id]['data'][$uom] = [
                            'quantity' => $quantity,
                            'total' => $uom_total,
                            'discount' => $line_discount->description ?? '0',
                            'discounted' => $uom_discounted
                        ];
                    }
                    $product_total += $uom_discounted;
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
