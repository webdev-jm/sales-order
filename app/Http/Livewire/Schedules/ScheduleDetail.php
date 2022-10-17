<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;

use App\Models\UserBranchSchedule;

class ScheduleDetail extends Component
{
    public $schedule;

    public $status_colors = [
        'for reschedule' => 'bg-warning',
        'for deletion' => 'bg-danger',
        'reschedule rejected' => 'bg-orange',
        'reschedule approved' => 'bg-teal',
        'rescheduled' => 'bg-teal',
        'deletion rejected' => 'bg-maroon',
        'deletion approved' => 'bg-olive',
    ];

    protected $listeners = [
        'setDetail' => 'getDetail'
    ];

    public function getDetail($schedule_id) {
        $this->schedule = UserBranchSchedule::findOrFail($schedule_id);
    }

    public function render()
    {
        return view('livewire.schedules.schedule-detail');
    }
}
