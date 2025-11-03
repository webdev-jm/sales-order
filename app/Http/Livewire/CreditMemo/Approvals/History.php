<?php

namespace App\Http\Livewire\CreditMemo\Approvals;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CreditMemoApproval;

use Illuminate\Support\Facades\DB;

class History extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'updateHistory' => 'render'
    ];

    public $rud;
    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'returned' => 'danger',
        'approved' => 'success',
    ];
    public function render()
    {

        $approval_dates = CreditMemoApproval::select(DB::raw('DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->where('credit_memo_id', $this->rud->id)
            ->paginate(2, ['*'], 'rud-approval-page');

        $approval_data = [];
        foreach($approval_dates as $data) {
            $approvals = CreditMemoApproval::with('user')
                ->orderBy('created_at', 'DESC')
                ->where('credit_memo_id', $this->rud->id)
                ->where(DB::raw('DATE(created_at)'), $data->date)
                ->get();

            $approval_data[$data->date] = $approvals;
        }

        return view('livewire.credit-memo.approvals.history')->with([
            'approval_dates' => $approval_dates,
            'approvals' => $approval_data
        ]);
    }

    public function mount($rud) {
        $this->rud = $rud;
    }
}
