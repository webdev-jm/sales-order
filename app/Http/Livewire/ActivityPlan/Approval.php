<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlan;
use App\Models\ActivityPlanApproval;
use App\Models\UserBranchSchedule;
use App\Models\ActivityPlanDetailTripApproval;

use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ActivityPlanApproved;
use App\Notifications\ActivityPlanRejected;
use App\Notifications\TripForRevision;
use App\Notifications\TripApprovedSuperior;
use App\Notifications\TripSubmitted;

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

            $details = $this->activity_plan->details;
            foreach($details as $detail) {
                if(isset($detail->branch)) {
                    if(!empty($detail->trip)) {
                        $detail->trip->update([
                            'status' => 'for revision'
                        ]);

                        // trip approval history
                        $approval = new ActivityPlanDetailTripApproval([
                            'user_id' => auth()->user()->id,
                            'activity_plan_detail_trip_id' => $detail->trip->id,
                            'status' => 'for revision',
                        ]);
                        $approval->save();

                        if(!empty($detail->trip->user)) {
                            Notification::send($detail->trip->user, new TripForRevision($detail->trip));
                        }
                    }
                }
            }

            if(!empty($user)) {
                Notification::send($user, new ActivityPlanRejected($this->activity_plan, $approval));
            }
        } else if($status == 'approved') { // approved

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

                        if(!empty($detail->trip) && $detail->trip->trasportation_type == 'AIR') {
                            // logs
                            activity('create')
                                ->performedOn($detail->trip)
                                ->log(':causer.firstname :causer.lastname has submitted trip [ :subject.trip_number ]');
                        }

                    }

                    // check if there's a trip data
                    if(!empty($detail->trip) && !empty($schedule)) {
                        $schedule->update([
                            'activity_plan_detail_trip_id' => $detail->trip->id
                        ]);

                        // update trip status
                        $detail->trip->update([
                            'status' => 'approved by imm. superior'
                        ]);

                        // trip approval history
                        $approval = new ActivityPlanDetailTripApproval([
                            'user_id' => auth()->user()->id,
                            'activity_plan_detail_trip_id' => $detail->trip->id,
                            'status' => 'approved by imm. superior',
                        ]);
                        $approval->save();

                        // notify department admin
                        $admin = $detail->trip->user->department->department_admin ?? NULL;
                        Notification::send($detail->trip->user, new TripApprovedSuperior($detail->trip));
                        if(!empty($admin)) {
                            Notification::send($admin, new TripApprovedSuperior($detail->trip));
                        }
                    }

                }
            }

            if(!empty($user)) {
                Notification::send($user, new ActivityPlanApproved($this->activity_plan, $approval));
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
