<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\ActivityPlan;
use App\Models\ActivityPlanApproval;

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
        $approvals = ActivityPlanApproval::orderBy('created_at', 'DESC')
        ->where('activity_plan_id', $this->activity_plan->id)
        ->paginate(10, ['*'], 'approval-history')->onEachSide(1);

        return view('livewire.activity-plan.approval-history')->with([
            'approvals' => $approvals
        ]);
    }
}
