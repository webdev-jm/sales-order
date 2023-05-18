<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;

use App\Models\UserBranchSchedule;
use App\Models\Deviation;
use App\Models\DeviationApproval;

use Livewire\WithPagination;

use Illuminate\Support\Facades\Notification;
use App\Notifications\DeviationApproved;
use App\Notifications\DeviationRejected;

class ScheduleDeviationApproval extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $deviation, $original_schedules, $new_schedules;
    public $remarks, $supervisor_ids;
    public $status_arr = [
        'submitted' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger'
    ];

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
            // check if existed
            $branch_schedule = UserBranchSchedule::where('user_id', $this->deviation->user_id)
            ->where('branch_id', $new->branch_id)
            ->where('date', $new->date)
            ->whereNull('status')
            ->where('source', 'deviation')
            ->first();
            if(empty($branch_schedule)) {
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
            } else {
                $branch_schedule->update([
                    'objective' => $new->activity
                ]);
            }
        }

        // record approval
        $approvals = new DeviationApproval([
            'deviation_id' => $this->deviation->id,
            'user_id' => auth()->user()->id,
            'status' => 'approved',
            'remarks' => $this->remarks,
        ]);
        $approvals->save();

        // logs
        activity('approved')
        ->performedOn($this->deviation)
        ->log(':causer.firstname :causer.lastname has approved schedule deviation :subject.reason_for_deviation');

        // notifications
        $user = $this->deviation->user;
        Notification::send($user, new DeviationApproved($this->deviation));

        // update reminders
        $this->deviation->reminders()->whereNull('status')->update([
            'status' => 'done'
        ]);

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

        // logs
        activity('rejected')
        ->performedOn($this->deviation)
        ->log(':causer.firstname :causer.lastname has rejected schedule deviation :subject.reason_for_deviation');

        // notifications
        $user = $this->deviation->user;
        Notification::send($user, new DeviationRejected($this->deviation));

        // update reminders
        $this->deviation->reminders()->whereNull('status')->update([
            'status' => 'done'
        ]);

        return redirect(request()->header('Referer'));
    }

    public function setDeviation($deviation_id) {
        $this->deviation = Deviation::find($deviation_id);
        $this->supervisor_ids = $this->deviation->user->getSupervisorIds();

        $this->original_schedules = $this->deviation->schedules()->where('type', 'original')->get();
        $this->new_schedules = $this->deviation->schedules()->where('type', 'new')->get();
    }

    public function render()
    {
        $approvals = [];
        if(!empty($this->deviation)) {
            $approvals = $this->deviation->approvals()->orderBy('created_at', 'DESC')
            ->paginate(5, ['*'], 'approval-page')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-deviation-approval')->with([
            'approvals' => $approvals
        ]);
    }
}