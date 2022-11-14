<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleDeleteApproved;
use App\Notifications\ScheduleDeleteRejected;

class ScheduleDelete extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $date, $schedule_data, $approvals;
    public $action, $remarks;

    protected $listeners = [
        'getDate' => 'setDate',
        'showDetail' => 'viewDetail'
    ];

    public function submitApprove() {
        $this->schedule_data->update([
            'status' => 'deletion approved'
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'deletion approved',
            'remarks' => NULL
        ]);
        $approval->save();

        // notification
        $delete_request = $this->schedule_data->approvals()->where('status', 'for deletion')->orderBy('id', 'DESC')->first();
        $user = $delete_request->user;
        if(!empty($user)) {
            Notification::send($user, new ScheduleDeleteApproved($this->schedule_data));
        }

        return redirect(request()->header('Referer'));
    }

    public function submitReject() {
        $this->validate([
            'remarks' => 'required'
        ]);

        $this->schedule_data->update([
            'status' => NULL
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'deletion rejected',
            'remarks' => $this->remarks
        ]);
        $approval->save();

        // notification
        $delete_request = $this->schedule_data->approvals()->where('status', 'for deletion')->orderBy('id', 'DESC')->first();
        $user = $delete_request->user;
        if(!empty($user)) {
            Notification::send($user, new ScheduleDeleteRejected($this->schedule_data));
        }

        return redirect(request()->header('Referer'));
    }

    public function approve() {
        $this->action = 'approve';
    }

    public function reject() {
        $this->action = 'reject';
    }

    public function cancel() {
        $this->reset([
            'action',
            'remarks',
        ]);
    }

    public function back() {
        $this->reset([
            'schedule_data',
            'approvals',
            'action',
        ]);
    }

    public function viewDetail($schedule_id) {
        $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
        $this->approvals = $this->schedule_data->approvals;
        $this->date = $this->schedule_data->date;
    }

    public function setDate($date, $schedule_id) {
        $this->date = $date;
        if(!empty($schedule_id)) {
            $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
            $this->approvals = $this->schedule_data->approvals;
            $this->date = $this->schedule_data->date;
        }
    }

    public function render()
    {
        $schedules = [];
        if(!empty($this->date)) {
            $schedules = UserBranchSchedule::where('status', 'for deletion')
            ->where('date', $this->date)
            ->paginate(10, ['*'], 'delete-page')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-delete')->with([
            'schedules' => $schedules
        ]);
    }
}
