<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleRequestRejected;
use App\Notifications\ScheduleRequestApproved;

class ScheduleRequest extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'setRequestDate' => 'setDate'
    ];

    public $date, $schedule_data, $action, $remarks;

    public function submitApprove() {
        $this->schedule_data->update([
            'status' => NULL
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'schedule request approved',
            'remarks' => NULL
        ]);
        $approval->save();

        // notification
        $schedule_request = $this->schedule_data->approvals()->where('status', 'schedule request')->orderBy('id', 'DESC')->first();
        $user = $schedule_request->user;
        if(!empty($user)) {
            Notification::send($user, new ScheduleRequestApproved($this->schedule_data));
        }

        return redirect(request()->header('Referer'));
    }

    public function submitReject() {
        $this->validate([
            'remarks' => 'required'
        ]);

        $this->schedule_data->update([
            'status' => 'schedule request rejected'
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'schedule request rejected',
            'remarks' => $this->remarks
        ]);
        $approval->save();

        // notification
        $schedule_request = $this->schedule_data->approvals()->where('status', 'schedule request')->orderBy('id', 'DESC')->first();
        $user = $schedule_request->user;
        if(!empty($user)) {
            Notification::send($user, new ScheduleRequestRejected($this->schedule_data));
        }

        return redirect(request()->header('Referer'));
    }

    public function cancel() {
        $this->reset('action');
    }

    public function approve() {
        $this->action = 'approve';
    }

    public function reject() {
        $this->action = 'reject';
    }

    public function back() {
        $this->reset('schedule_data');
    }

    public function selectSchedule($schedule_id) {
        $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
    }

    public function setDate($date, $schedule_id) {
        $this->date = $date;
        if(!empty($schedule_id)) {
            $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
        }
    }

    public function render()
    {
        $schedules = UserBranchSchedule::orderBy('id', 'DESC')
        ->where('status', 'schedule request')
        ->where('date', $this->date)
        ->paginate(10, ['*'], 'request-page')->onEachSide(1);
        
        return view('livewire.schedules.schedule-request')->with([
            'schedules' => $schedules
        ]);
    }
}