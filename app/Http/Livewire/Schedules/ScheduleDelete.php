<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleDeleteApproved;
use App\Notifications\ScheduleDeleteRejected;

use Illuminate\Support\Facades\Log;

/** Modal component for approving or rejecting schedule deletion requests. */
class ScheduleDelete extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $date, $schedule_data, $approvals;
    public $action, $remarks;

    protected $listeners = [
        'getDate'    => 'setDate',
        'showDetail' => 'viewDetail'
    ];

    public function submitApprove(): \Illuminate\Http\RedirectResponse
    {
        $this->schedule_data->update([
            'status' => 'deletion approved'
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id'                 => auth()->user()->id,
            'status'                  => 'deletion approved',
            'remarks'                 => null,
        ]);
        $approval->save();

        activity('approved')
            ->performedOn($this->schedule_data)
            ->log(':causer.firstname :causer.lastname approved schedule delete request :subject.date');

        $delete_request = $this->schedule_data->approvals()
            ->where('status', 'for deletion')
            ->orderBy('id', 'DESC')
            ->first();

        if (!empty($delete_request)) {
            $user = $delete_request->user;
            if (!empty($user)) {
                try {
                    Notification::send($user, new ScheduleDeleteApproved($this->schedule_data));
                } catch (\Exception $e) {
                    Log::error('Notification failed: ' . $e->getMessage());
                }
            }
        }

        return redirect(request()->header('Referer'));
    }

    public function submitReject(): \Illuminate\Http\RedirectResponse
    {
        $this->validate([
            'remarks' => 'required'
        ]);

        $this->schedule_data->update([
            'status' => null
        ]);

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $this->schedule_data->id,
            'user_id'                 => auth()->user()->id,
            'status'                  => 'deletion rejected',
            'remarks'                 => $this->remarks,
        ]);
        $approval->save();

        activity('rejected')
            ->performedOn($this->schedule_data)
            ->log(':causer.firstname :causer.lastname rejected schedule delete request :subject.date');

        $delete_request = $this->schedule_data->approvals()
            ->where('status', 'for deletion')
            ->orderBy('id', 'DESC')
            ->first();

        if (!empty($delete_request)) {
            $user = $delete_request->user;
            if (!empty($user)) {
                try {
                    Notification::send($user, new ScheduleDeleteRejected($this->schedule_data));
                } catch (\Exception $e) {
                    Log::error('Notification failed: ' . $e->getMessage());
                }
            }
        }

        return redirect(request()->header('Referer'));
    }

    public function approve(): void
    {
        $this->action = 'approve';
    }

    public function reject(): void
    {
        $this->action = 'reject';
    }

    public function cancel(): void
    {
        $this->reset(['action', 'remarks']);
    }

    public function back(): void
    {
        $this->reset(['schedule_data', 'approvals', 'action']);
    }

    public function viewDetail($schedule_id): void
    {
        $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
        $this->approvals = $this->schedule_data->approvals;
        $this->date = $this->schedule_data->date;
    }

    public function setDate($date, $schedule_id): void
    {
        $this->date = $date;
        if (!empty($schedule_id)) {
            $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
            $this->approvals = $this->schedule_data->approvals;
            $this->date = $this->schedule_data->date;
        }
    }

    public function render(): \Illuminate\View\View
    {
        $schedules = [];
        if (!empty($this->date)) {
            $schedules = UserBranchSchedule::where('status', 'for deletion')
                ->where('date', $this->date)
                ->paginate(10, ['*'], 'delete-page')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-delete')->with([
            'schedules' => $schedules
        ]);
    }
}
