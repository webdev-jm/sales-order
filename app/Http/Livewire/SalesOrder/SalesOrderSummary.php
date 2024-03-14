<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;

use Illuminate\Support\Facades\Session;
use App\Models\ShippingAddress;

class SalesOrderSummary extends Component
{
    public $data, $logged_account, $account, $order_data, $ship_to_address;

    protected $listeners = [
        'setDataSummary' => 'setData'
    ];

    public function setData($data) {
        $this->order_data = Session::get('order_data');
        $this->data = $data;

        $ship_to_address = [
            'ship_to_name' => $this->account->account_name,
            'ship_to_address1' => $this->account->ship_to_address1,
            'ship_to_address2' => $this->account->ship_to_address2,
            'ship_to_address3' => $this->account->ship_to_address3,
        ];
        if(empty($this->data['shipping_address_id'])) {
            $address = ShipToAddress::where('account_id', $this->account->id)
                ->where('id', $data['shipping_address_id'])
                ->first();
            $ship_to_address = [
                'ship_to_name' => $address->ship_to_name,
                'ship_to_address1' => $address->building,
                'ship_to_address2' => $address->street,
                'ship_to_address3' => $address->city,
            ];
        }

        $this->ship_to_address = $ship_to_address;
        
        // dd($this->order_data);
    }

    public function mount() {
        $this->logged_account = Session::get('logged_account');
        $this->account = $this->logged_account->account;
    }

    public function render()
    {
        return view('livewire.sales-order.sales-order-summary');
    }
}
