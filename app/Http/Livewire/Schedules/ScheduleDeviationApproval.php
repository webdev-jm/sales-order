<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;

use App\Models\UserBranchSchedule;
use App\Models\Deviation;
use App\Models\DeviationApproval;

class ScheduleDeviationApproval extends Component
{
    public $deviation, $original_schedules, $new_schedules;
    public $remarks;

    protected $listeners = [
        'setDeviationApproval' => 'setDeviation'
    ];

    public function approve() {
        // change status
        $this->deviation->update([
            'status' => 'approved'
        ]);

        foreach($this->original_schedules as $original) {
            $schedule = UserBranchSchedule::find($original->user_branch_schedule_id);
            $schedule->update([
                'status' => 'deviated'
            ]);
        }
        // create new schedule
        foreach($this->new_schedules as $new) {
            // create request if approved
            $branch_schedule = new UserBranchSchedule([
                'user_id' => $this->deviation->user_id,
                'branch_id' => $new->branch_id,
                'date' => $new->date,
                'status' => NULL,
                'objective' => $new->activity,
                'source' => 'deviation'
            ]);
            $branch_schedule->save();
        }

        // record approval
        $approvals = new DeviationApproval([
            'deviation_id' => $this->deviation->id,
            'user_id' => auth()->user()->id,
            'status' => 'approved',
            'remarks' => $this->remarks,
        ]);
        $approvals->save();

        // notifications

        return redirect(request()->header('Referer'));
    }

    public function reject() {
        $this->validate([
            'remarks' => 'required'
        ]);

        // update status
        $this->deviation->update([
            'status' => 'rejected'
        ]);

        foreach($this->original_schedules as $original) {
            $schedule = UserBranchSchedule::find($original->user_branch_schedule_id);
            $schedule->update([
                'status' => NULL
            ]);
        }

        // record approval
        $approvals = new DeviationApproval([
            'deviation_id' => $this->deviation->id,
            'user_id' => auth()->user()->id,
            'status' => 'rejected',
            'remarks' => $this->remarks,
        ]);
        $approvals->save();

        return redirect(request()->header('Referer'));
    }

    public function setDeviation($deviation_id) {
        $this->deviation = Deviation::find($deviation_id);

        $this->original_schedules = $this->deviation->schedules()->where('type', 'original')->get();
        $this->new_schedules = $this->deviation->schedules()->where('type', 'new')->get();
    }

    public function render()
    {
        return view('livewire.schedules.schedule-deviation-approval');
    }
}