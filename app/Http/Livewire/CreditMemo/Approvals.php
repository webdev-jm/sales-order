<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\CreditMemoApproval;

class Approvals extends Component
{
    public $rud;

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'returned' => 'danger',
        'approved' => 'success',
    ];

    public function render()
    {
        return view('livewire.credit-memo.approvals');
    }

    public function mount($credit_memo) {
        $this->rud = $credit_memo;
    }

    public function approve($status) {
        $changes_arr['old'] = $this->rud->getOriginal();

        $this->rud->update([
            'status' => $status,
        ]);

        $changes_arr['old'] = $this->rud->getChanges();

        $approval = new CreditMemoApproval([
            'credit_memo_id' => $this->rud->id,
            'user_id' => auth()->user()->id,
            'status' => $status,
            'remarks' => NULL
        ]);
        $approval->save();

        // logs
        activity('updated')
            ->performedOn($this->rud)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has '.$status.' a RUD Invoice: :subject.invoice_number');
    }
}
