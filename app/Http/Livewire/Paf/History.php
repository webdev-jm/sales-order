<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Paf;
use App\Models\PafApproval;

use Illuminate\Support\Facades\DB;

class History extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $paf_id;

    protected $listeners = [
        'setPafHistory' => 'setPafHistory'
    ];

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'warning',
        'approved'  => 'info',
        'approved by brand' => 'primary',
        'cancelled' => 'danger',
        'completed' => 'success',
    ];

    public function setPafHistory($paf_id) {
        $this->paf_id = $paf_id;
    }

    public function render()
    {
        $approval_dates = PafApproval::select(DB::raw('DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->where('paf_id', $this->paf_id)
            ->paginate(5, ['*'], 'approval-dates-page');

        $approval_data = [];
        foreach($approval_dates as $data) {
            $approvals = PafApproval::orderBy('created_at', 'DESC')
                ->where('paf_id', $this->paf_id)
                ->where(DB::raw('DATE(created_at)'), $data->date)
                ->get();
            
            $approval_data[$data->date] = $approvals;
        }

        return view('livewire.paf.history')->with([
            'approval_dates' => $approval_dates,
            'approvals' => $approval_data
        ]);
    }
}
