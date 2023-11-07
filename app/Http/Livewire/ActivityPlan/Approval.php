<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlan;
use App\Models\ActivityPlanApproval;
use App\Models\UserBranchSchedule;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ActivityPlanRejected;
use App\Notifications\ActivityPlanApproved;

use App\Models\Reminders;

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

            // logs
            activity('rejected')
            ->performedOn($this->activity_plan)
            ->log(':causer.firstname :causer.lastname has rejected activity plan of '.$this->activity_plan->user->fullName());
            
        } else if($this->action == 'approve') {
            $status = 'approved';

            // logs
            activity('approved')
            ->performedOn($this->activity_plan)
            ->log(':causer.firstname :causer.lastname has approved activity plan of '.$this->activity_plan->user->fullName());
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
                Notification::send($user, new ActivityPlanRejected($this->activity_plan, $approval));
            }
        } else if($status == 'approved') { // approved
            if(!empty($user)) {
                Notification::send($user, new ActivityPlanApproved($this->activity_plan, $approval));
            }

            // convert to schedule
            $details = $this->activity_plan->details;
            foreach($details as $detail) {
                if(isset($detail->branch)) {
                    // check if already exist
                    $schedule = UserBranchSchedule::where('user_id', $this->activity_plan->user_id)
                        ->where('branch_id', $detail->branch_id)
                        ->where('date', $detail->date)
                        ->whereNull('status')
                        ->first();
                    
                    if(empty($schedule)) {
                        $schedule = new UserBranchSchedule([
                            'user_id' => $this->activity_plan->user_id,
                            'branch_id' => $detail->branch_id,
                            'date' => $detail->date,
                            'status' => NULL,
                            'objective' => $detail->activity,
                            'source' => 'activity-plan'
                        ]);
                        $schedule->save();
                    }

                    // check if there's a trip detail
                    if(!empty($detail->trip)) {
                        $schedule->update([
                            'activity_plan_detail_trip_id' => $detail->trip->id
                        ]);
                    }

                }
            }
        }

        // update reminder
        $this->activity_plan->reminders()->whereNull('status')->update([
            'status' => 'done'
        ]);

        return redirect(request()->header('Referer'))->with([
            'message_success' => 'Activity Plan has been updated.'
        ]);
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
