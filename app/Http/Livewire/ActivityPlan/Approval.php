<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlan;
use App\Models\ActivityPlanApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ActivityPlanRejected;
use App\Notifications\ActivityPlanApproved;


class Approval extends Component
{
    public $action, $activity_plan, $remarks;

    protected $listeners = [
        'setApproval' => 'setActivity'
    ];

    public function submitApproval() {
        if($this->action == 'reject') {
            $this->validate([
                'remarks' => 'required'
            ]);
            $status = 'rejected';
            
        } else {
            $status = 'approved';
        }
        
        $this->activity_plan->update([
            'status' => $status,
        ]);

        $approval = new ActivityPlanApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_id' => $this->activity_plan->id,
            'status' => $status,
            'remarks' => $this->remarks
        ]);
        $approval->save();

        // notification
        $user = $this->activity_plan->user;
        if($status == 'rejected') {
            if(!empty($user)) {
                Notification::send($user, new ActivityPlanRejected($this->activity_plan));
            }
        } else {
            if(!empty($user)) {
                Notification::send($user, new ActivityPlanApproved($this->activity_plan));
            }
        }

        return redirect(request()->header('Referer'));
    }

    public function setActivity($action, $activity_plan_id) {
        $this->action = $action;
        $this->activity_plan = ActivityPlan::find($activity_plan_id);
    }

    public function render()
    {
        return view('livewire.activity-plan.approval');
    }
}
