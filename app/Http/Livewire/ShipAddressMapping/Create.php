<?php

namespace App\Http\Livewire\ShipAddressMapping;

use Livewire\Component;

use App\Models\Account;
use App\Models\ShippingAddress;
use App\Models\AccountShipAddressMapping;

class Create extends Component
{
    public $accounts;
    public $account_id, $shipping_address_id;
    public $reference1, $reference2, $reference3;

    public function saveShipAddressMapping() {
        $this->validate([
            'account_id' => [
                'required'
            ],
            'shipping_address_id' => [
                'required',
            ],
            'reference1' => [
                'required'
            ],
            'reference2' => [
                'max:255'
            ],
            'reference3' => [
                'max:255'
            ],
        ]);

        $mapping = new AccountShipAddressMapping([
            'account_id' => $this->account_id,
            'shipping_address_id' => $this->shipping_address_id,
            'reference1' => $this->reference1,
            'reference2' => $this->reference2,
            'reference3' => $this->reference3,
        ]);
        $mapping->save();

        session()->flash('message_success', 'Ship Address Mapping has been created successfully.');

        return redirect()->route('ship-address-mapping.index');
    }

    public function mount() {
        $this->accounts = Account::orderby('short_name', 'ASC')
            ->get();
    }

    public function render()
    {
        $shipping_addresses = array();
        if(!empty($this->account_id)) {
            $shipping_addresses = ShippingAddress::where('account_id', $this->account_id)
                ->orderby('ship_to_name', 'ASC')
                ->get();
        }

        return view('livewire.ship-address-mapping.create')->with([
            'shipping_addresses' => $shipping_addresses
        ]);
    }
}
