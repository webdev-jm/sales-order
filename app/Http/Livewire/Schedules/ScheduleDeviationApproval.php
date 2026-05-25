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

use Illuminate\Support\Facades\Log;

/** Modal component for viewing and approving/rejecting deviation requests. */
class ScheduleDeviationApproval extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $deviation, $original_schedules, $new_schedules;
    public $remarks, $supervisor_ids;
    public $status_arr = [
        'submitted' => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger'
    ];

    protected $listeners = [
        'setDeviationApproval' => 'setDeviation'
    ];

    public function approve()
    {
        $this->deviation->update([
            'status' => 'approved'
        ]);

        foreach ($this->original_schedules as $original) {
            $schedule = UserBranchSchedule::find($original->user_branch_schedule_id);
            if (!empty($schedule) && $schedule->objective !== 'on-leave') {
                $schedule->update([
                    'status' => 'deviated'
                ]);
            }
        }

        foreach ($this->new_schedules as $new) {
            $branch_schedule = UserBranchSchedule::where('user_id', $this->deviation->user_id)
                ->where('branch_id', $new->branch_id)
                ->where('date', $new->date)
                ->whereNull('status')
                ->where('source', 'deviation')
                ->first();

            if (empty($branch_schedule)) {
                $branch_schedule = new UserBranchSchedule([
                    'user_id' => $this->deviation->user_id,
                    'branch_id' => $new->branch_id,
                    'date' => $new->date,
                    'status' => null,
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

        $approvals = new DeviationApproval([
            'deviation_id' => $this->deviation->id,
            'user_id' => auth()->user()->id,
            'status' => 'approved',
            'remarks' => $this->remarks,
        ]);
        $approvals->save();

        activity('approved')
            ->performedOn($this->deviation)
            ->log(':causer.firstname :causer.lastname has approved schedule deviation :subject.reason_for_deviation');

        try {
            $user = $this->deviation->user;
            Notification::send($user, new DeviationApproved($this->deviation));
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }

        $this->deviation->reminders()->whereNull('status')->update([
            'status' => 'done'
        ]);

        return redirect(request()->header('Referer'));
    }

    public function reject()
    {
        $this->validate([
            'remarks' => 'required'
        ]);

        $this->deviation->update([
            'status' => 'rejected'
        ]);

        foreach ($this->original_schedules as $original) {
            $schedule = UserBranchSchedule::find($original->user_branch_schedule_id);
            if (!empty($schedule) && $schedule->objective !== 'on-leave') {
                $schedule->update([
                    'status' => null
                ]);
            }
        }

        $approvals = new DeviationApproval([
            'deviation_id' => $this->deviation->id,
            'user_id' => auth()->user()->id,
            'status' => 'rejected',
            'remarks' => $this->remarks,
        ]);
        $approvals->save();

        activity('rejected')
            ->performedOn($this->deviation)
            ->log(':causer.firstname :causer.lastname has rejected schedule deviation :subject.reason_for_deviation');

        try {
            $user = $this->deviation->user;
            Notification::send($user, new DeviationRejected($this->deviation));
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }

        $this->deviation->reminders()->whereNull('status')->update([
            'status' => 'done'
        ]);

        return redirect(request()->header('Referer'));
    }

    public function setDeviation($deviation_id): void
    {
        $this->deviation = Deviation::find($deviation_id);

        if (empty($this->deviation)) {
            return;
        }

        $this->supervisor_ids = $this->deviation->user->getSupervisorIds();

        $schedules_by_type = $this->deviation->schedules()->get()->groupBy('type');
        $this->original_schedules = $schedules_by_type->get('original', collect());
        $this->new_schedules = $schedules_by_type->get('new', collect());
    }

    public function render()
    {
        $approvals = [];
        if (!empty($this->deviation)) {
            $approvals = $this->deviation->approvals()
                ->orderBy('created_at', 'DESC')
                ->paginate(5, ['*'], 'approval-page')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-deviation-approval')->with([
            'approvals' => $approvals
        ]);
    }
}
