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
    public $action;
    public $status, $reschedule_date;

    protected $listeners = [
        'showEvents' => 'setDate'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function submit() {
        if($this->action == 'reschedule-request') {
            $this->validate([
                'reschedule_date' => 'required'
            ]);
            $this->schedule_data->update([
                'reschedule_date' => $this->reschedule_date,
                'status' => 'reschedule-request'
            ]);

            $this->reset('action');
            $this->reset('schedule_data');
            $this->reset('reschedule_date');
        }
        if($this->action == 'delete-request') {
            
        }
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function backAction() {
        $this->reset('action');
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
        if(auth()->user()->hasRole('superadmin')) {
            $branch_schedules = UserBranchSchedule::where('date', $this->date)
            ->paginate(10)->onEachSide(1);
        } else {
            $branch_schedules = UserBranchSchedule::where('date', $this->date)
            ->where('user_id', auth()->user()->id)
            ->paginate(10)->onEachSide(1);
        }

        return view('livewire.schedules.schedule-event')->with([
            'branch_schedules' => $branch_schedules
        ]);
    }
}
