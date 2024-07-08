<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Product;
use App\Models\AccountProductReference;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\SoProductPriceTrait;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    use SoProductPriceTrait;

    public $logged_account;
    public $selected;
    public $checkedAll = 0;

    public function createSO() {
        if(!empty($this->selected)) {
            foreach($this->selected as $po_id => $po_data) {
                // get details
                $details = PurchaseOrderDetail::where('purchase_order_id', $po_id)->get();
                // process details
                $detail_data = array();
                foreach($details as $detail) {
                    // get product
                    $product = array();
                    $price_data = array();
                    if(!empty($detail->product_id)) {
                        $product = Product::find($detail->product_id);
                        $price_data = $this->getProductPrice($product, $this->logged_account->account, $detail->unit_of_measure, $detail->quantity);
                    } else {
                        $account_product = AccountProductReference::where('account_id', $this->logged_account->account_id)
                            ->where(function($query) use($detail) {
                                $query->where('account_reference', $detail->sku_code)
                                    ->orWhere('account_reference', $detail->sku_code_other)
                                    ->orWhere(DB::raw('CAST(account_reference AS UNSIGNED)'), $detail->sku_code)
                                    ->orWhere(DB::raw('CAST(account_reference AS UNSIGNED)'), $detail->sku_code_other);
                            })
                            ->first();
                        if(!empty($account_product)) {
                            $product = $account_product->product;
                            $price_data = $this->getProductPrice($product, $this->logged_account->account, $detail->unit_of_measure, $detail->quantity);
                        }
                    }

                    $detail_data[] = [
                        'product_id' => !empty($detail->product_id) ? $detail->product_id : null,
                        'sku_code' => $product->stock_code ?? 'product not found',
                        'sku_code_other' => $detail->sku_code,
                        'product_name' => ($product->description  ?? '').' '.($product->size ?? ''),
                        'quantity' => $detail->quantity,
                        'unit_of_measure' => $detail->unit_of_measure,
                        'total' => $price_data['total'] ?? 0,
                        'total_less_discount' => $price_data['discounted'] ?? 0,
                    ];
                }
                $this->selected[$po_id]['products'] = $detail_data;
            }

            Session::put('selectedPO', $this->selected);

            return redirect()->route('purchase-order.create');
        }
    }

    public function checkAll() {
        if($this->checkedAll == 0) {
            $purchase_orders = PurchaseOrder::where('sms_account_id', $this->logged_account->account_id)
                ->get();
            foreach($purchase_orders as $order) {
                $this->selected[$order->id] = $order;
            }

            $this->checkedAll = 1;
        } else {
            $this->reset('selected');
            $this->checkedAll = 0;
        }
    }

    public function check($po_id) {
        $purchase_order = PurchaseOrder::find($po_id);
        if(isset($this->selected) && !empty($this->selected[$purchase_order->id])) {
            unset($this->selected[$purchase_order->id]);
        } else {
            $this->selected[$purchase_order->id] = $purchase_order;
        }
    }

    public function mount() {
        $this->logged_account = Session::get('logged_account');
    }

    public function render()
    {
        $purchase_orders = PurchaseOrder::orderBy('order_date', 'DESC')
            ->where('sms_account_id', $this->logged_account->account_id)
            ->paginate(10)->onEachSide(1);
        
        return view('livewire.purchase-order.index')->with([
            'purchase_orders' => $purchase_orders
        ]);
    }
}
