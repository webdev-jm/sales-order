<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;

use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $logged_account;
    public $selected;
    public $checkedAll = 0;

    public function checkAll() {
        if($this->checkedAll == 0) {
            $purchase_orders = PurchaseOrder::where('sms_account_id', $this->logged_account->account_id)
                ->get();
            foreach($purchase_orders as $order) {
                $this->selected[$order->id] = $order;
            }

            $this->checkedAll = 1;
        } else {
            $this->reset('selected');
            $this->checkedAll = 0;
        }
    }

    public function check($po_id) {
        $purchase_order = PurchaseOrder::find($po_id);
        if(isset($this->selected) && !empty($this->selected[$purchase_order->id])) {
            unset($this->selected[$purchase_order->id]);
        } else {
            $this->selected[$purchase_order->id] = $purchase_order;
        }
    }

    public function mount() {
        $this->logged_account = Session::get('logged_account');
    }

    public function render()
    {
        $purchase_orders = PurchaseOrder::orderBy('order_date', 'DESC')
            ->where('sms_account_id', $this->logged_account->account_id)
            ->paginate(10)->onEachSide(1);
        
        return view('livewire.purchase-order.index')->with([
            'purchase_orders' => $purchase_orders
        ]);
    }
}
