<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use AccountLoginModel;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use Illuminate\Support\Facades\Session;

class ScheduleEvent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $date, $schedule_data;
    public $action;
    public $status, $reschedule_date;

    public $accuracy, $longitude, $latitude;

    protected $listeners = [
        'showEvents' => 'setDate'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sign_in() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        // check if logged in to account or branch
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        $logged_branch = BranchLogin::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        if(empty($logged_account) && empty($logged_branch)) {
            $branch_login = new BranchLogin([
                'user_id' => auth()->user()->id,
                'branch_id' => $this->schedule_data->branch_id,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'accuracy' => $this->accuracy,
                'time_in' => now(),
            ]);
            $branch_login->save();

            Session::put('logged_branch', $branch_login);
            
            return redirect()->to('/home')->with([
                'message_success' => 'You are logged in.'
            ]);
        } else {
            return redirect()->to('/home')->with([
                'message_error' => 'Your are currently logged in to a branch.'
            ]);
        }
        
    }

    public function loadLocation() {
        $this->dispatchBrowserEvent('reloadLocation');
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
