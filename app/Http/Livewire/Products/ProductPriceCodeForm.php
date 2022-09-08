<?php

namespace App\Http\Livewire\Products;

use Livewire\Component;
use App\Models\Company;

class ProductPriceCodeForm extends Component
{
    public $companies;
    public $inputs = [];
    public $i = 1;

    public function add($i) {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i) {
        unset($this->inputs[$i]);
    }

    public function mount() {
        $this->companies = Company::orderBy('name', 'ASC')->get();
    }

    public function render()
    {
        return view('livewire.products.product-price-code-form');
    }
}
