<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;

class ScheduleEvent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $date, $schedule_data;

    protected $listeners = [
        'showEvents' => 'setDate'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewSchedule($schedule_id) {
        $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);
    }

    public function back() {
        $this->reset('schedule_data');
    }

    public function setDate($date) {
        $this->date = $date;
        $this->reset('schedule_data');
    }

    public function render()
    {
        $branch_schedules = UserBranchSchedule::where('date', $this->date)
        ->paginate(10)->onEachSide(1);

        return view('livewire.schedules.schedule-event')->with([
            'branch_schedules' => $branch_schedules
        ]);
    }
}
