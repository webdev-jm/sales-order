<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Branch;
use App\Models\User;
use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleAddRequest;

use Illuminate\Support\Facades\Log;

/** Modal component for submitting a new schedule request. */
class ScheduleAdd extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;

    public $date, $objective, $branch_id, $account_id;

    public function updatingSearch(): void
    {
        $this->resetPage('branch-page');
    }

    public function submitRequest()
    {
        $this->validate([
            'date' => 'required',
            'objective' => 'required',
            'branch_id' => 'required'
        ]);

        $exist = UserBranchSchedule::where('user_id', auth()->user()->id)
            ->where('branch_id', $this->branch_id)
            ->where('date', $this->date)
            ->whereNull('status')
            ->first();

        if (empty($exist)) {
            $schedule = new UserBranchSchedule([
                'user_id' => auth()->user()->id,
                'branch_id' => $this->branch_id,
                'date' => $this->date,
                'status' => null,
                'objective' => $this->objective,
                'source' => 'request'
            ]);
            $schedule->save();

            activity('created')
                ->performedOn($schedule)
                ->log(':causer.firstname :causer.lastname added a new schedule :subject.date');

            $supervisor_id = auth()->user()->getImmediateSuperiorId();
            if (auth()->user()->id != $supervisor_id) {
                $user = User::find($supervisor_id);
                if (!empty($user)) {
                    try {
                        Notification::send($user, new ScheduleAddRequest($schedule));
                    } catch (\Exception $e) {
                        Log::error('Notification failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect(request()->header('Referer'));
    }

    public function selectBranch($branch_id): void
    {
        if ($this->branch_id == $branch_id) {
            $this->reset('branch_id');
        } else {
            $this->branch_id = $branch_id;
        }
    }

    public function updatedAccountId(): void
    {
        $this->resetPage('branch-page');
    }

    public function render(): \Illuminate\View\View
    {
        $branches = Branch::whereHas('account', function ($query) {
                $query->whereHas('users', fn ($q) => $q->where('id', auth()->user()->id));
            })
            ->when(!empty($this->account_id), fn ($q) => $q->where('account_id', $this->account_id))
            ->when(!empty($this->search), fn ($q) => $q->where(function ($query) {
                $query->where('branch_code', 'like', '%' . $this->search . '%')
                      ->orWhere('branch_name', 'like', '%' . $this->search . '%');
            }))
            ->paginate(10, ['*'], 'branch-page')->onEachSide(1);

        return view('livewire.schedules.schedule-add')->with([
            'branches' => $branches
        ]);
    }
}
