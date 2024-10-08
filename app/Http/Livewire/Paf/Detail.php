<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Detail extends Component
{
    public $paf_data;
    public $detail;

    protected $listeners = [
        'pafAddDetail' => 'setDetail'
    ];

    public function setDetail() {
        $this->paf_data = Session::get('paf_data');
    }

    public function save() {
        $this->validate([
            'detail.product_id' => [
                'required'
            ],
            'detail.type' => [
                'required',
            ],
            'detail.quantity' => [
                'required',
            ],
            'detail.srp' => [
                'required',
            ],
            'detail.percentage' => [
                'required',
            ],
            'detail.amount' => [
                'required',
            ],
            'detail.expense' => [
                'required'
            ],
        ]);

        $product = $this->paf_data['products'][$this->detail['product_id']];
        $this->detail['product'] = $product['stock_code'].'- '.$product['description'].' '.$product['size'];
        
        $this->paf_data['details'][] = $this->detail;
        Session::put('paf_data', $this->paf_data);

        $this->reset('detail');

        $this->emit('setDetail');
        $this->emit('closeModal');
    }

    public function render()
    {
        return view('livewire.paf.detail');
    }
}
