<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranchSchedule;
use App\Models\Deviation;
use App\Models\DeviationSchedule;
use App\Models\DeviationApproval;

use Illuminate\Validation\ValidationException;

class ScheduleDeviation extends Component
{
    public $date, $original_schedules, $user, $reason_for_deviation, $cost_center;
    public $new_schedules;

    public $branchQuery, $searchQuery;

    public $user_id, $account_id;
    protected $queryString = [
        'user_id',
        'account_id'
    ];

    protected $listeners = [
        'setDeviation' => 'setDate'
    ];

    public function submitForm() {
        $this->validate([
            'reason_for_deviation' => 'required',
            'cost_center' => 'max:255',
        ]);
        
        if(!empty($this->new_schedules)) {
            // deviations
            $deviation = new Deviation([
                'user_id' => $this->user->id,
                'cost_center' => $this->cost_center,
                'reason_for_deviation' => $this->reason_for_deviation,
                'status' => 'submitted'
            ]);
            $deviation->save();

            // original schedules
            foreach($this->original_schedules as $schedule) {
                $deviation_schedule = new DeviationSchedule([
                    'deciation_id' => $deviation->id,
                    'branch_id' => $schedule->branch_id,
                    'date' => $schedule->date,
                    'activity' => $schedule->objective,
                    'type' => 'original'
                ]);
                $deviation_schedule->save();

                // change status
            }

            // new schedules
            foreach($this->new_schedules as $schedule) {
                $deviation_schedule = new DeviationSchedule([
                    'deciation_id' => $deviation->id,
                    'branch_id' => $schedule['branch_id'],
                    'date' => $schedule['date'],
                    'activity' => $schedule['activity'],
                    'type' => 'new'
                ]);
                $deviation_schedule->save();

                // create request
            }

            // approvals
            $approvals = new DeviationApproval([
                'deviation_id' => $deviation->id,
                'user_id' => auth()->user()->id,
                'status' => 'submitted',
                'remarks' => NULL
            ]);
            $approvals->save();

        } else {
            throw ValidationException::withMessages(['new plans' => 'new plans is required']);
        }
    }

    // branch search
    public function setQuery($key) {
        $query = $this->branchQuery[$key];
        $this->resetQuery();
        $this->branchQuery[$key] = $query;
        $this->searchQuery = $query;
    }

    public function resetQuery() {
        $this->reset([
            'branchQuery',
            'searchQuery'
        ]);
    }

    public function selectBranch($key, $branch_id, $branch_name) {
        $this->new_schedules[$key]['branch_id'] = $branch_id;
        $this->new_schedules[$key]['branch_name'] = $branch_name;

        $this->resetQuery();
    }

    public function addLine() {
        $this->new_schedules[] = [
            'date' => $this->date,
            'branch_id' => '',
            'branch_name' => '',
            'activity' => ''
        ];
    }

    public function removeLine($key) {
        unset($this->new_schedules[$key]);
    }

    public function setDate($date) {
        $this->date = $date;

        $original_schedules = UserBranchSchedule::where('date', $this->date)
        ->whereNull('status');

        if(!empty($this->user_id)) {
            $original_schedules->where('user_id', $this->user_id);
        } else {
            $original_schedules->where('user_id', auth()->user()->id);
        }

        if(!empty($this->account_id)) {
            $original_schedules->whereHas('branch', function($query) {
                $query->where('account_id', $this->account_id);
            });
        }

        $this->original_schedules = $original_schedules->get();

        $this->new_schedules[0]['date'] = $this->date;
    }

    public function mount() {
        if(!empty($this->user_id)) {
            $this->user = User::find($this->user_id);
        } else {
            $this->user = auth()->user();
        }

        $this->new_schedules[] = [
            'date' => $this->date,
            'branch_id' => '',
            'branch_name' => '',
            'activity' => ''
        ];
    }
    
    public function render()
    {
  
        if(!empty($this->searchQuery)) {
            $branches = Branch::orderBy('branch_code')
            ->where('branch_code', 'like', '%'.$this->searchQuery.'%')
            ->orWhere('branch_name', 'like', '%'.$this->searchQuery.'%')
            ->orWhereHas('account', function($query) {
                $query->where('account_code', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('short_name', 'like', '%'.$this->searchQuery.'%');
            })
            ->limit(10)->get();
        } else {
            $branches = Branch::orderBy('branch_code')
            ->limit(10)->get();
        }

        return view('livewire.schedules.schedule-deviation')->with([
            'branches' => $branches
        ]);
    }
}
