<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;

/** Modal component for viewing schedule history and approval timeline. */
class ScheduleDetail extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $schedule;

    public $status_colors = [
        'for reschedule'           => 'bg-warning',
        'for deletion'             => 'bg-danger',
        'reschedule rejected'      => 'bg-orange',
        'rescheduled'              => 'bg-teal',
        'deletion rejected'        => 'bg-maroon',
        'deletion approved'        => 'bg-olive',
        'schedule request rejected' => 'bg-purple',
        'schedule request approved' => 'bg-lime',
        'schedule request'         => 'bg-success',
        'for deviation'            => 'bg-primary',
        'deviated'                 => 'bg-warning',
    ];

    protected $listeners = [
        'setDetail' => 'getDetail'
    ];

    public function getDetail($schedule_id): void
    {
        $this->schedule = UserBranchSchedule::findOrFail($schedule_id);
    }

    public function render(): \Illuminate\View\View
    {
        $approvals = [];
        if (!empty($this->schedule)) {
            $approvals = $this->schedule->approvals()
                ->orderBy('created_at', 'DESC')
                ->paginate(10, ['*'], 'schedule-request')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-detail')->with([
            'approvals' => $approvals
        ]);
    }
}
