<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;

class SalesOrderProducts extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $account, $quantity, $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount() {
        $logged_account = auth()->user()->logged_account();
        $account = $logged_account->account;
        
        $this->account = $account;
    }

    public function render()
    {
        $products = Product::whereHas('price_code', function($query) {
            $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
        })
        ->Where(function($query) {
            $query->where('stock_code', 'like', '%'.$this->search.'%')
            ->orWhere('description', 'like', '%'.$this->search.'%')
            ->orWhere('category', 'like', '%'.$this->search.'%');
        })
        ->paginate(10);

        return view('livewire.sales-order.sales-order-products')->with([
            'products' => $products
        ]);
    }
}
