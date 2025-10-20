<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\SalesOrder;
use App\Models\SalesOrderProductUom;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUomPAF;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\GlobalTrait;

class Autosave extends Component
{
    use GlobalTrait;

    public $setting;

    public $sales_order_id, $sales_order;
    public $logged_account;
    public $ship_date, $shipping_instruction,
    $shipping_address_id, $ship_to_name, $ship_to_address1,
    $ship_to_address2, $ship_to_address3, $postal_code;

    protected $listeners = [
        'saveData' => 'saveData',
    ];

    public function saveData() {
        $this->save();
    }

    public function save() {
        $order_data = Session::get('order_data');

        $shipping_address_id = $this->shipping_address_id == 'default' ? NULL : $this->shipping_address_id;

        $this->sales_order->update([
            'shipping_address_id' => $shipping_address_id,
            'ship_date' => $this->ship_date ?? $this->sales_order->ship_date,
            'shipping_instruction' => $this->shipping_instruction ?? $this->sales_order->shipping_instruction,
            'ship_to_name' => $this->ship_to_name ?? $this->sales_order->ship_to_name,
            'ship_to_building' => $this->ship_to_address1 ?? $this->sales_order->ship_to_building,
            'ship_to_street' => $this->ship_to_address2 ?? $this->sales_order->ship_to_street,
            'ship_to_city' => $this->ship_to_address3 ?? $this->sales_order->ship_to_city,
            'ship_to_postal' => $this->postal_code ?? $this->sales_order->ship_to_postal,
            'total_quantity' => $order_data['total_quantity'],
            'total_sales' => $order_data['total'],
            'grand_total' => $order_data['grand_total'],
            'po_value' => $order_data['po_value'],
        ]);

        $num = 0;
        $part = 1;
        $limit = $this->logged_account->account->company->order_limit ?? $this->setting->sales_order_limit;
        $curr_limit = $limit;

        foreach($this->sales_order->order_products as $so_product) {
            foreach($so_product->product_uoms as $uom) {
                $paf_data = SalesOrderProductUomPAF::where('sales_order_product_uom_id', $uom->id)->forceDelete();
            }
        }
        $this->sales_order->order_products()->forceDelete();

        foreach($order_data['items'] as $product_id => $items) {
            // $num++;

            // divide by parts
            if($num > $curr_limit) {
                $curr_limit += $limit;
                $part++;
            }

            $sales_order_product = new SalesOrderProduct([
                'sales_order_id' => $this->sales_order->id,
                'product_id' => $product_id,
                'part' => $part,
                'total_quantity' => $items['product_quantity'],
                'total_sales' => $items['product_total'],
            ]);
            $sales_order_product->save();

            foreach($items['data'] as $uom => $data) {
                $sales_order_product_uom = new SalesOrderProductUom([
                    'sales_order_product_id' => $sales_order_product->id,
                    'uom' => $uom,
                    'quantity' => $data['quantity'],
                    'uom_total' => $data['total'],
                    'uom_total_less_disc' => $data['discounted']
                ]);
                $sales_order_product_uom->save();

                // check if there's a PAF row
                if(!empty($data['paf_rows'])) {
                    foreach($data['paf_rows'] as $paf_row) {
                        if(!empty($paf_row['paf_number']) && !empty($paf_row['uom']) && !empty($paf_row['quantity'])) {
                            $sales_order_product_uom_paf = new SalesOrderProductUomPAF([
                                'sales_order_product_uom_id' => $sales_order_product_uom->id,
                                'paf_number' => $paf_row['paf_number'],
                                'uom' => $paf_row['uom'],
                                'quantity' => $paf_row['quantity'],
                            ]);
                            $sales_order_product_uom_paf->save();
                        }
                    }
                }
            }
        }

    }

    public function getData() {
        $this->dispatchBrowserEvent('getData');
    }

    public function mount($sales_order_id) {
        $this->logged_account = Session::get('logged_account');
        $this->sales_order = SalesOrder::findOrFail($sales_order_id);
        $this->dispatchBrowserEvent('getData');
        $this->settings = $this->getSettings();
    }

    public function render()
    {
        return view('livewire.sales-order.autosave');
    }
}
