<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlanDetail;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;

class ScheduleDetail extends Component
{
    public $detail;
    public $remarks;

    protected $listeners = [
        'setDetail' => 'setDetail'
    ];

    public function setDetail($detail_id) {
        $this->detail = ActivityPlanDetail::find($detail_id);
    }

    public function approve($trip_id) {
        $trip = ActivityPlanDetailTrip::find($trip_id);
        if(!empty($trip)) {
            $trip->update([
                'status' => 'approved'
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
