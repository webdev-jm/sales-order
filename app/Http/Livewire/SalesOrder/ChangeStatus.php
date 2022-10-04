<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\SalesOrder;

class ChangeStatus extends Component
{
    public $sales_order_id, $sales_order;
    public $confirmation;

    public function selectYes() {
        // dd($this->sales_order);
        $this->sales_order->update([
            'reference' => null,
            'upload_status' => null,
        ]);

        return redirect()->route('sales-order.list')->with([
            'message_success' => 'Status has been cleared.'
        ]);
    }

    public function selectNo() {
        $this->confirmation = false;
    }

    public function changeStatus() {
        $this->confirmation = true;
    }

    public function mount() {
        $this->sales_order = SalesOrder::findOrFail($this->sales_order_id);
    }

    public function render()
    {
        return view('livewire.sales-order.change-status');
    }
}
