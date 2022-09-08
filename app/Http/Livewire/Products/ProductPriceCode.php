<?php

namespace App\Http\Livewire\Products;

use Livewire\Component;
use App\Models\Product;
use App\Models\Company;
use App\Models\PriceCode;

class ProductPriceCode extends Component
{
    public $product_id, $product, $companies;
    public $code, $selling_price, $price_basis;
    public $price_codes;

    public function savePriceCode() {
        dd($this->code);
    }

    public function managePriceCode() {
        $this->dispatchBrowserEvent('openFormModal'.$this->product_id);
    }

    public function mount() {
        $this->product = Product::findOrFail($this->product_id);
        $this->companies = Company::orderBy('name', 'ASC')->get();
    }

    public function render()
    {
        return view('livewire.products.product-price-code');
    }
}
