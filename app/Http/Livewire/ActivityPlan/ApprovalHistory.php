<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\ActivityPlan;
use App\Models\ActivityPlanApproval;

use Illuminate\Support\Facades\DB;

class ApprovalHistory extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $activity_plan;
    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'rejected' => 'danger',
        'approved' => 'success'
    ];

    public function mount($activity_plan_id) {
        $this->activity_plan = ActivityPlan::find($activity_plan_id);
    }

    public function render()
    {
        $approval_dates = ActivityPlanApproval::select(DB::raw('DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->where('activity_plan_id', $this->activity_plan->id)
            ->paginate(5, ['*'], 'approval-dates-page');

        $approval_data = [];
        foreach($approval_dates as $data) {
            $approvals = ActivityPlanApproval::orderBy('created_at', 'DESC')
                ->where('activity_plan_id', $this->activity_plan->id)
                ->where(DB::raw('DATE(created_at)'), $data->date)
                ->get();
            
            $approval_data[$data->date] = $approvals;
        }


        return view('livewire.activity-plan.approval-history')->with([
            'approvals' => $approval_data,
            'approval_dates' => $approval_dates
        ]);
    }
}
