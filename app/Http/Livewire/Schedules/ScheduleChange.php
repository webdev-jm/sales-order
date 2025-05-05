<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleRescheduleApproved;
use App\Notifications\ScheduleRescheduleRejected;

use Illuminate\Support\Facades\Log;

class ScheduleChange extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $date, $schedule_data, $approvals;
    public $action, $remarks;

    protected $listeners = [
        'setDate' => 'getDate',
        'showChange' => 'showDetail'
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
            'reschedule_date' => NULL,
            'objective' => $this->schedule_data->objective,
            'source' => 'reschedule'
        ]);
        $new_schedule->save();

        // logs
        activity('approved')
        ->performedOn($new_schedule)
        ->log(':causer.firstname :causer.lastname has approved reschedule request :subject.date');

        // notification
        $delete_request = $this->schedule_data->approvals()->where('status', 'for reschedule')->orderBy('id', 'DESC')->first();
        $user = $delete_request->user;
        if(!empty($user)) {
            try {
                Notification::send($user, new ScheduleRescheduleApproved($this->schedule_data));
            } catch(\Exception $e) {
                Log::error('Notification failed: '.$e->getMessage());
            }
        }

        return redirect(request()->header('Referer'));
    }

    public function submitReject() {
        $this->validate([
            'remarks' => 'required'
        ]);

        $this->schedule_data->update([
            'status' => null
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id' => auth()->user()->id,
            'status' => 'reschedule rejected',
            'remarks' => $this->remarks
        ]);
        $approval->save();

        // logs
        activity('rejected')
        ->performedOn($this->schedule_data)
        ->log(':causer.firstname :causer.lastname has rejected reschedule request :subject.date');

        // notification
        $delete_request = $this->schedule_data->approvals()->where('status', 'for reschedule')->orderBy('id', 'DESC')->first();
        $user = $delete_request->user;
        if(!empty($user)) {
            try {
                Notification::send($user, new ScheduleRescheduleRejected($this->schedule_data));
            } catch(\Exception $e) {
                Log::error('Notification failed: '.$e->getMessage());
            }
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
        $this->date = $this->schedule_data->date;
    }

    public function getDate($date, $schedule_id) {
        $this->date = $date;
        if(!empty($schedule_id)) {
            $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
            $this->approvals =  $this->schedule_data->approvals;
            $this->date = $this->schedule_data->date;
        }
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
