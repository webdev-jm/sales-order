<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

class ScheduleChange extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $date, $schedule_data, $approvals;
    public $action, $remarks;

    protected $listeners = [
        'setDate' => 'getDate'
    ];

    public function submitApprove() {
        $this->schedule_data->update([
            'status' => 'rescheduled'
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'reschedule approved',
        ]);
        $approval->save();

        $new_schedule = new UserBranchSchedule([
            'user_id' => $this->schedule_data->user_id,
            'branch_id' => $this->schedule_data->branch_id,
            'date'  => $this->schedule_data->reschedule_date,
            'status' => NULL,
            'reschedule_date' => NULL
        ]);
        $new_schedule->save();

        return redirect(request()->header('Referer'));
    }

    public function submitReject() {
        $this->validate([
            'remarks' => 'required'
        ]);

        $this->schedule_data->update([
            'status' => 'reschedule rejected'
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'reschedule rejected',
            'remarks' => $this->remarks
        ]);
        $approval->save();

        return redirect(request()->header('Referer'));
    }

    public function approve() {
        $this->action = 'approve';
    }

    public function reject() {
        $this->action = 'reject';
    }

    public function cancel() {
        $this->reset('action');
        $this->reset('remarks');
    }

    public function back() {
        $this->reset('schedule_data');
        $this->reset('approvals');
        $this->reset('action');
    }

    public function showDetail($schedule_id) {
        $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
        $this->approvals =  $this->schedule_data->approvals;
    }

    public function getDate($date) {
        $this->date = $date;
    }

    public function render()
    {
        $schedules = [];
        if(!empty($this->date)) {
            $schedules = UserBranchSchedule::where('status', 'for reschedule')
            ->where('date', $this->date)
            ->paginate(10)->onEachSide(1);
        }

        return view('livewire.schedules.schedule-change')->with([
            'schedules' => $schedules
        ]);
    }
}
