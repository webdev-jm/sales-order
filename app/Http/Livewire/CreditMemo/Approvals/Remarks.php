<?php

namespace App\Http\Livewire\CreditMemo\Approvals;

use Livewire\Component;
use App\Models\CreditMemoRemarks;

class Remarks extends Component
{
    public $rud;
    public $message;

    public function render()
    {
        $remarks = CreditMemoRemarks::orderBy('created_at', 'ASC')
            ->where('credit_memo_id', $this->rud->id)
            ->get();

        return view('livewire.credit-memo.approvals.remarks')->with([
            'remarks' => $remarks
        ]);
    }

    public function mount($rud) {
        $this->rud = $rud;
    }

    public function saveRemarks() {
        $this->validate([
            'message' => [
                'required'
            ]
        ]);

        $cm_remark = new CreditMemoRemarks([
            'credit_memo_id' => $this->rud->id,
            'user_id' => auth()->user()->id,
            'message' => $this->message,
            'seen_by' => NULL
        ]);
        $cm_remark->save();

        $this->reset('message');

        $this->emit('remarkAdded');
    }
}
