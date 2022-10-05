<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;

use App\Models\Account;
use App\Models\Product;

class ProductAssign extends Component
{
    public $account;

    protected $listeners = [
        'setAccount' => 'setAccount'
    ];

    public function setAccount($account_id) {
        $this->account = Account::findOrFail($account_id);
    }

    public function render()
    {
        $products = Product::orderBy('stock_code', 'ASC')
        ->where('special_product', 1)->get();
        return view('livewire.accounts.product-assign')->with([
            'products' => $products
        ]);
    }
}
