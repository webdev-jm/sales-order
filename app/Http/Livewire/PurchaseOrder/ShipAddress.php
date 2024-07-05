<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\ShippingAddress;

use Illuminate\Support\Facades\Session;

class ShipAddress extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $po_id;
    public $logged_account;
    public $search;
    public $selected_address;

    protected $listeners = [
        'selectAddress' => 'selectAddress'
    ];

    public function selectAddress($po_id) {
        $this->po_id = $po_id;
    }

    public function setSelectedAddress() {
        $this->emit('setSelectedAddress', $this->selected_address, $this->po_id);
        $this->reset(['search', 'selected_address', 'po_id']);
        $this->resetPage('address-page');
    }

    public function cancel() {
        $this->reset(['search', 'selected_address', 'po_id']);
        $this->resetPage('address-page');
    }

    public function selectAddressCode($address_id) {
        $this->selected_address = ShippingAddress::find($address_id);
    }

    public function updatedSearch() {
        $this->resetPage('address-page');
    }

    public function mount() {
        $this->logged_account = Session::get('logged_account');
    }

    public function render()
    {
        $shipping_addresses = ShippingAddress::where('account_id', $this->logged_account->account_id)
            ->when(!empty($this->search), function($query) {
                $query->where(function($qry) {
                    $qry->where('address_code', 'like', '%'.$this->search.'%')
                        ->orWhere('ship_to_name', 'like', '%'.$this->search.'%')
                        ->orWhere('building', 'like', '%'.$this->search.'%')
                        ->orWhere('street', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%')
                        ->orWhere('tin', 'like', '%'.$this->search.'%')
                        ->orWhere('postal', 'like', '%'.$this->search.'%');
                });
            })
            ->paginate(6, ['*'], 'address-page')->onEachSide(1);

        return view('livewire.purchase-order.ship-address')->with([
            'shipping_addresses' => $shipping_addresses
        ]);
    }
}
