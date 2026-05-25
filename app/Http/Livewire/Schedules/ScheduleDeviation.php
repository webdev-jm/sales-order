<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranchSchedule;
use App\Models\Deviation;
use App\Models\DeviationSchedule;
use App\Models\DeviationApproval;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Notification;
use App\Notifications\DeviationSubmitted;

use App\Http\Traits\ReminderTrait;

use Illuminate\Support\Facades\Log;

/** Handles deviation form submission — allows a user to log a deviation from their original schedule. */
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

    public function submitForm()
    {
        $this->validate([
            'reason_for_deviation' => 'required',
            'cost_center' => 'max:255',
            'new_schedules.*.branch_id' => 'required',
            'new_schedules.*.date' => 'required'
        ]);

        if (!empty($this->new_schedules)) {
            $deviation = new Deviation([
                'user_id' => $this->user->id,
                'cost_center' => $this->cost_center,
                'date' => Carbon::parse($this->date)->toDateString(),
                'reason_for_deviation' => $this->reason_for_deviation,
                'status' => 'submitted'
            ]);
            $deviation->save();

            foreach ($this->original_schedules as $schedule) {
                $deviation_schedule = new DeviationSchedule([
                    'deviation_id' => $deviation->id,
                    'user_branch_schedule_id' => $schedule->id,
                    'branch_id' => $schedule->branch_id ?? null,
                    'date' => Carbon::parse($schedule->date)->toDateString(),
                    'activity' => $schedule->objective,
                    'type' => 'original'
                ]);
                $deviation_schedule->save();
            }

            foreach ($this->new_schedules as $schedule) {
                if (!empty($schedule['branch_id'])) {
                    $deviation_schedule = new DeviationSchedule([
                        'deviation_id' => $deviation->id,
                        'user_branch_schedule_id' => null,
                        'branch_id' => $schedule['branch_id'] ?? null,
                        'date' => Carbon::parse($schedule['date'])->toDateString(),
                        'activity' => $schedule['activity'],
                        'type' => 'new'
                    ]);
                    $deviation_schedule->save();
                }
            }

            $approvals = new DeviationApproval([
                'deviation_id' => $deviation->id,
                'user_id' => auth()->user()->id,
                'status' => 'submitted',
                'remarks' => null
            ]);
            $approvals->save();

            activity('created')
                ->performedOn($deviation)
                ->log(':causer.firstname :causer.lastname has created schedule deviations :subject.reason_for_deviation');

            $user_ids = [];
            $supervisor_id = auth()->user()->getImmediateSuperiorId();
            if (auth()->user()->id != $supervisor_id) {
                $user = User::find($supervisor_id);
                if (!empty($user)) {
                    try {
                        Notification::send($user, new DeviationSubmitted($deviation));
                    } catch (\Exception $e) {
                        Log::error('Notification failed: ' . $e->getMessage());
                    }
                }
            }

            if (!empty($supervisor_id)) {
                $user_ids[] = $supervisor_id;
            }

            $this->setReminder('Deviation', $deviation->id, 'deviation form has been submitted for your approval', $user_ids, '/schedule/deviations');

            return redirect(request()->header('Referer'));
        }

        throw ValidationException::withMessages(['new plans' => 'new plans is required']);
    }

    public function setQuery($key): void
    {
        $query = $this->branchQuery[$key] ?? '';
        $this->resetQuery();
        $this->branchQuery[$key] = $query;
        $this->searchQuery = $query;
    }

    public function resetQuery(): void
    {
        $this->reset([
            'branchQuery',
            'searchQuery'
        ]);
    }

    public function selectBranch($key, $branch_id, $branch_name): void
    {
        $this->new_schedules[$key]['branch_id'] = $branch_id;
        $this->new_schedules[$key]['branch_name'] = $branch_name;

        $this->resetQuery();
    }

    public function addLine(): void
    {
        $this->new_schedules[] = [
            'date' => $this->date,
            'branch_id' => '',
            'branch_name' => '',
            'activity' => ''
        ];
    }

    public function removeLine($key): void
    {
        unset($this->new_schedules[$key]);
        $this->new_schedules = array_values($this->new_schedules);
    }

    public function setDate($date): void
    {
        $this->date = $date;

        $original_schedules = UserBranchSchedule::where('date', $this->date)
            ->whereNull('status');

        if (!empty($this->user_id)) {
            $original_schedules->where('user_id', $this->user_id);
        } else {
            $original_schedules->where('user_id', auth()->user()->id);
        }

        if (!empty($this->account_id)) {
            $original_schedules->whereHas('branch', function ($query) {
                $query->where('account_id', $this->account_id);
            });
        }

        $this->original_schedules = $original_schedules->get();

        foreach ($this->new_schedules as $key => $schedule) {
            $this->new_schedules[$key]['date'] = $this->date;
        }
    }

    public function mount(): void
    {
        if (!empty($this->user_id)) {
            $this->user = User::find($this->user_id);
        } else {
            $this->user = auth()->user();
        }

        $cost_center = $this->user->cost_centers()->first();
        if (!empty($cost_center)) {
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
        if (!empty($this->searchQuery)) {
            $branches = Branch::orderBy('branch_code')
                ->where('branch_code', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('branch_name', 'like', '%' . $this->searchQuery . '%')
                ->orWhereHas('account', function ($query) {
                    $query->where('account_code', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('short_name', 'like', '%' . $this->searchQuery . '%');
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
