<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\SalesOrder;
use App\Models\SalesOrderProductUom;
use App\Models\SalesOrderProduct;

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
            'ship_date' => $this->ship_date,
            'shipping_instruction' => $this->shipping_instruction,
            'ship_to_name' => $this->ship_to_name,
            'ship_to_building' => $this->ship_to_address1,
            'ship_to_street' => $this->ship_to_address2,
            'ship_to_city' => $this->ship_to_address3,
            'ship_to_postal' => $this->postal_code,
            'total_quantity' => $order_data['total_quantity'],
            'total_sales' => $order_data['total'],
            'grand_total' => $order_data['grand_total'],
            'po_value' => $order_data['po_value'],
        ]);

        $num = 0;
        $part = 1;
        $limit = $this->logged_account->account->company->order_limit ?? $this->setting->sales_order_limit;
        $curr_limit = $limit;
        $this->sales_order->order_products()->forceDelete();
        
        foreach($order_data['items'] as $product_id => $items) {
            $num++;

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
            }
        }

    }

    public function getData() {
        $this->dispatchBrowserEvent('getData');
    }
    
    public function mount() {
        $this->logged_account = Session::get('logged_account');
        $this->sales_order = SalesOrder::findOrFail($this->sales_order_id);
        $this->dispatchBrowserEvent('getData');
        $this->settings = $this->getSettings();
    }

    public function render()
    {
        return view('livewire.sales-order.autosave');
    }
}
