<?php

namespace App\Http\Livewire\CreditMemo\Approvals;

use App\Http\Livewire\Traits\WithCreditMemoStatus;
use App\Models\CreditMemoApproval;
use App\Models\CreditMemoRemarks;
use Livewire\Component;

class Timeline extends Component
{
    use WithCreditMemoStatus;

    // Only keep the simple ID or model as a public property
    public $creditMemo;

    protected $listeners = [
        'remarkAdded' => 'render',
        'updateHistory' => 'render'
    ];

    public function mount($creditMemo)
    {
        $this->creditMemo = $creditMemo;
    }

    public function render()
    {
        // Fetch approvals
        $approvals = CreditMemoApproval::with('user')
            ->where('credit_memo_id', $this->creditMemo->id)
            ->get();

        // Fetch remarks
        $remarks = CreditMemoRemarks::with('user')
            ->where('credit_memo_id', $this->creditMemo->id)
            ->get();

        $timeline = $approvals->concat($remarks)
            ->sortBy('created_at', SORT_REGULAR, true)
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

        return view('livewire.credit-memo.approvals.timeline', [
            'timeline' => $timeline // Pass it as a variable instead of a public property
        ]);
    }
}
