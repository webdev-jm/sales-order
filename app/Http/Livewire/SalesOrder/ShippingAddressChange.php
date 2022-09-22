<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\ShippingAddress;

use Illuminate\Support\Facades\Session;

class ShippingAddressChange extends Component
{
    public $shipping_addresses, $account_id, $account;

    public function openModal() {
        $this->dispatchBrowserEvent('openModal');
    }

    public function select() {
        $this->dispatchBrowserEvent('changeAddress');
        $this->dispatchBrowserEvent('closeModal');
    }

    public function mount() {
        $logged_account = Session::get('logged_account');
        $this->account = $logged_account->account;
    }

    public function render()
    {
        $this->shipping_addresses = ShippingAddress::orderBy('address_code', 'ASC')
        ->where('account_id', $this->account_id)
        ->get();
        
        return view('livewire.sales-order.shipping-address-change')->with([
            'shipping_addresses' => $this->shipping_addresses
        ]);
    }
}
