<?php

namespace App\Http\Livewire\SalesOrder\Multiple;

use Livewire\Component;

class Summary extends Component
{
    public $so_data;

    protected $listeners = [
        'setSummary' => 'getSoData'
    ];

    public function getSoData($so_data) {
        $this->so_data = $so_data;
    }

    public function render()
    {
        return view('livewire.sales-order.multiple.summary');
    }
}
