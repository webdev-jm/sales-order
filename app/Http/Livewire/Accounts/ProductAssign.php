<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Account;
use App\Models\Product;
use App\Models\PriceCode;

class ProductAssign extends Component
{
    use WithPagination;

    public $search;
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public $account, $special_products;

    protected $listeners = [
        'setAccount' => 'setAccount'
    ];

    public function saveChanges() {
        $product_data = [];
        foreach($this->special_products as $product_id => $data) {
            if(isset($data['product']) && $data['product'] == true) { // check if checked
                // price code
                if(isset($data['price_code'])) {
                    $product_data[$product_id] = [
                        'price_code' => $data['price_code']
                    ];
                } else { // set default price code as account price code
                    $product_data[$product_id] = [
                        'price_code' => $this->account->price_code
                    ];
                }
            }
        }

        $this->account->products()->sync($product_data);
    }

    public function setAccount($account_id) {
        $this->account = Account::findOrFail($account_id);
        $this->reset('special_products');

        foreach($this->account->products as $product) {
            $this->special_products[$product->id] = [
                'product' => true,
                'price_code' => $product->pivot->price_code ?? $this->account->price_code
            ];
        }
    }

    public function render()
    {
        if($this->search != '') {
            $products = Product::orderBy('stock_code', 'ASC')
            ->where('special_product', 1)
            ->where(function($query) {
                $query->where('stock_code', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')
                ->orWhere('size', 'like', '%'.$this->search.'%');
            })
            ->paginate(10, ['*'], 'product-page')->onEachSide(1);
        } else {
            $products = Product::orderBy('stock_code', 'ASC')
            ->where('special_product', 1)->paginate(10, ['*'], 'product-page')->onEachSide(1);
        }

        return view('livewire.accounts.product-assign')->with([
            'products' => $products
        ]);
    }
}
