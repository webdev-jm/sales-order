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
        
        $this->paf_data['details'][] = $this->detail;
        Session::put('paf_data', $this->paf_data);

        $this->emit('setDetail');
        $this->emit('closeModal');
    }

    public function render()
    {
        return view('livewire.paf.detail');
    }
}
