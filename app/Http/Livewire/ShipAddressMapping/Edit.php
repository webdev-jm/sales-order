<?php

namespace App\Http\Livewire\ShipAddressMapping;

use Livewire\Component;

use App\Models\Account;
use App\Models\ShippingAddress;
use App\Models\AccountShipAddressMapping;

class Edit extends Component
{
    public $ship_address_mapping;
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

        $this->ship_address_mapping->update([
            'account_id' => $this->account_id,
            'shipping_address_id' => $this->shipping_address_id,
            'reference1' => $this->reference1,
            'reference2' => $this->reference2,
            'reference3' => $this->reference3,
        ]);

        session()->flash('message_success', 'Ship Address Mapping has been updated successfully.');

        return redirect()->route('ship-address-mapping.index');
    }

    public function mount($ship_address_mapping) {
        $this->ship_address_mapping = $ship_address_mapping;
        $this->account_id = $ship_address_mapping->account_id;
        $this->shipping_address_id = $ship_address_mapping->shipping_address_id;
        $this->reference1 = $ship_address_mapping->reference1;
        $this->reference2 = $ship_address_mapping->reference2;
        $this->reference3 = $ship_address_mapping->reference3;

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

        return view('livewire.ship-address-mapping.edit')->with([
            'shipping_addresses' => $shipping_addresses
        ]);
    }
}
