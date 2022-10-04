<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\ShippingAddress;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Session;

class ShippingAddressChange extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function updatingSearchAddress()
    {
        $this->resetPage();
    }

    public $account_id, $account;
    public $search_address;
    public $shipping_address_id;

    public function openModal() {
        $this->dispatchBrowserEvent('openModal');
    }

    public function select() {
        $this->dispatchBrowserEvent('changeAddress');
        $this->dispatchBrowserEvent('closeModal');
        $this->emit('saveData');
    }

    public function mount() {
        $logged_account = Session::get('logged_account');
        $this->account = $logged_account->account;
    }

    public function render()
    {
        $shipping_addresses = ShippingAddress::orderBy('address_code', 'ASC')
        ->where('account_id', $this->account_id)
        ->where(function($query) {
            $query->where('address_code', 'like', '%'.$this->search_address.'%')
            ->orWhere('ship_to_name', 'like', '%'.$this->search_address.'%')
            ->orWhere('building', 'like', '%'.$this->search_address.'%')
            ->orWhere('street', 'like', '%'.$this->search_address.'%')
            ->orWhere('city', 'like', '%'.$this->search_address.'%')
            ->orWhere('postal', 'like', '%'.$this->search_address.'%');
        })
        ->paginate(7, ['*'], 'address-page')->onEachSide(1);
        
        return view('livewire.sales-order.shipping-address-change')->with([
            'shipping_addresses' => $shipping_addresses
        ]);
    }
}
