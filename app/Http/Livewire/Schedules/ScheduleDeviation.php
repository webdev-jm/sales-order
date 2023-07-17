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

use Illuminate\Support\Facades\Notification;
use App\Notifications\DeviationSubmitted;

use App\Http\Traits\ReminderTrait;

class ScheduleDeviation extends Component
{
    use ReminderTrait;

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
            'new_schedules.*.branch_id' => 'required',
            'new_schedules.*.date' => 'required'
        ]);
        
        if(!empty($this->new_schedules)) {
            // deviations
            $deviation = new Deviation([
                'user_id' => $this->user->id,
                'cost_center' => $this->cost_center,
                'date' => date('y-m-d', strtotime($this->date)),
                'reason_for_deviation' => $this->reason_for_deviation,
                'status' => 'submitted'
            ]);
            $deviation->save();

            // original schedules
            foreach($this->original_schedules as $schedule) {
                $deviation_schedule = new DeviationSchedule([
                    'deviation_id' => $deviation->id,
                    'user_branch_schedule_id' => $schedule->id,
                    'branch_id' => $schedule->branch_id ?? NULL,
                    'date' => date('y-m-d', strtotime($schedule->date)),
                    'activity' => $schedule->objective,
                    'type' => 'original'
                ]);
                $deviation_schedule->save();

                // // change status
                // $schedule->update([
                //     'status' => 'for deviation'
                // ]);
            }

            // new schedules
            foreach($this->new_schedules as $schedule) {
                if(!empty($schedule['branch_id'])) {
                    $deviation_schedule = new DeviationSchedule([
                        'deviation_id' => $deviation->id,
                        'user_branch_schedule_id' => NULL,
                        'branch_id' => $schedule['branch_id'] ?? NULL,
                        'date' => date('y-m-d', strtotime($schedule['date'])),
                        'activity' => $schedule['activity'],
                        'type' => 'new'
                    ]);
                    $deviation_schedule->save();
                }
            }

            // approvals
            $approvals = new DeviationApproval([
                'deviation_id' => $deviation->id,
                'user_id' => auth()->user()->id,
                'status' => 'submitted',
                'remarks' => NULL
            ]);
            $approvals->save();

            // logs
            activity('created')
            ->performedOn($deviation)
            ->log(':causer.firstname :causer.lastname has created schedule deviations :subject.reason_for_deviation');

            // notifications
            // $user_ids = auth()->user()->getSupervisorIds();
            // foreach($user_ids as $user_id) {
            //     if(auth()->user()->id != $user_id) {
            //         $user = User::find($user_id);
            //         Notification::send($user, new DeviationSubmitted($deviation));
            //     }
            // }

            $user_ids = [];
            $supervisor_id = auth()->user()->getImmediateSuperiorId();
            if(auth()->user()->id != $supervisor_id) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    Notification::send($user, new DeviationSubmitted($deviation));
                }
            }

            if(!empty($supervisor_id)) {
                $user_ids[] = $supervisor_id;
            }

            // create reminder
            $this->setReminder('Deviation', $deviation->id, 'deviation form has been submitted for your approval', $user_ids, '/schedule/deviations');

            // reload page
            return redirect(request()->header('Referer'));

        } else {
            throw ValidationException::withMessages(['new plans' => 'new plans is required']);
        }
    }

    // branch search
    public function setQuery($key) {
        $query = $this->branchQuery[$key] ?? '';
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

        foreach($this->new_schedules as $key => $schdule) {
            $this->new_schedules[$key]['date'] = $this->date;
        }
    }

    public function mount() {
        if(!empty($this->user_id)) {
            $this->user = User::find($this->user_id);
        } else {
            $this->user = auth()->user();
        }

        $cost_center = $this->user->cost_centers()->first();
        if(!empty($cost_center)) {
            $this->cost_center = $cost_center->cost_center;
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
