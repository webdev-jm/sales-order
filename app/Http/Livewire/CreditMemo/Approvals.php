<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use App\Models\CreditMemoApproval;
use Illuminate\Support\Facades\DB;

class Approvals extends Component
{
    public $rud;
    public $status_arr = ['draft' => 'secondary', 'submitted' => 'info', 'returned' => 'danger', 'approved' => 'success'];

    public function mount($credit_memo)
    {
        $this->rud = $credit_memo;
    }

    public function approve($status)
    {
        DB::transaction(function () use ($status) {
            $old = $this->rud->getOriginal();
            $this->rud->update(['status' => $status]);

            CreditMemoApproval::create([
                'credit_memo_id' => $this->rud->id,
                'user_id' => auth()->id(),
                'status' => $status,
            ]);

            activity('updated')->performedOn($this->rud)
                ->log(':causer.firstname has ' . $status . ' RUD invoice ' . $this->rud->invoice_number);
        });

        $this->emit('updateHistory');
    }

    // Note: XML Generation Logic removed for brevity.
    // It should be moved to a Service class (e.g., CreditMemoXmlService)
    // and called here rather than bloating the component.

    public function render()
    {
        return view('livewire.credit-memo.approvals');
    }
}
