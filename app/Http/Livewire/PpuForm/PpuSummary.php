<?php

namespace App\Http\Livewire\PpuForm;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\PPUForm;
use App\Models\PPUFormItem;


class PpuSummary extends Component
{
    public $control_number;
    public $account_name;
    public $submitted;
    public $pickup;
    public $items = [];
    public $total_qty = 0;
    public $total_amount = 0;

    protected $listeners = ['loadOrderSummary' => 'loadData'];

    public function loadData($control_number, $items, $account_name, $submitted, $pickup)
    {
        $this->control_number = $control_number;
        $this->items = $items;
        $this->account_name = $account_name;
        $this->submitted = $submitted;
        $this->pickup = $pickup;

        $this->total_qty = collect($items)->sum('qty');
        $this->total_amount = collect($items)->sum('amount');



        Session::put('ppu_item', [
            'control_number' => $this->control_number,
            'items' => $this->items,
            'total_qty' => $this->total_qty,
            'total_amount' => $this->total_amount,
        ]);



    }

    public function confirmOrder()
    {
        Session::put('order_data', [
            'control_number' => $this->control_number,
            'items' => $this->items,
            'total_qty' => $this->total_qty,
            'total_amount' => $this->total_amount,
        ]);

        $this->dispatchBrowserEvent('order-confirmed');
    }


    public function render()
    {
        return view('livewire.ppu-form.ppu-summary');
    }
}
