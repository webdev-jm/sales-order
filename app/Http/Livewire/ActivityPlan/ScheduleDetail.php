<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlanDetail;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\ActivityPlanDetailTripDestination;
use App\Models\UserBranchSchedule;

class ScheduleDetail extends Component
{
    public $detail;
    public $remarks;
    public $trip_data;
    public $source;

    protected $listeners = [
        'setDetail' => 'setDetail'
    ];

    public function setDetail($detail_id) {
        $this->detail = ActivityPlanDetail::find($detail_id);

        if(!empty($this->detail->trip)) {
            $this->trip_data = $this->detail->trip;
            $this->source = 'trips';
        } else {
            $destination = ActivityPlanDetailTripDestination::where('activity_plan_detail_id', $this->detail->id)
                ->first();
            $this->trip_data = $destination ?? NULL;
            $this->source = 'other';
        }
    }

    public function approve($trip_id) {
        $trip = ActivityPlanDetailTrip::find($trip_id);
        if(!empty($trip)) {
            $trip->update([
                'status' => 'approved'
            ]);

            $detail = $trip->activity_plan_detail;
            
            $activity_plan = $detail->activity_plan;

            // convert to schedules
            $schedule = UserBranchSchedule::updateOrInsert([
                'user_id' => $activity_plan->user_id,
                'branch_id' => $detail->branch_id,
                'date' => $detail->date,
                'activity_plan_detail_trip_id' => $trip->id,
            ], [
                'status' => NULL,
                'objective' => $detail->activity,
                'source' => 'activity-plan',
            ]);

            // record approvals history
            $approval = new ActivityPlanDetailTripApproval([
                'user_id' => auth()->user()->id,
                'activity_plan_detail_trip_id' => $trip->id,
                'status' => 'approved',
                'remarks' => $this->remarks ?? NULL,
            ]);
            $approval->save();

            // refresh page
            return redirect()->route('mcp.show', $this->detail->activity_plan_id)->with([
                'message_success' => 'Shedule trip '.$trip->trip_number.' has been approved.'
            ]);
        } else { // trip not found throw some error
            return redirect()->route('mcp.show', $this->detail->activity_plan_id)->with([
                'message_error' => 'Trip not found!'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.activity-plan.schedule-detail');
    }
}
