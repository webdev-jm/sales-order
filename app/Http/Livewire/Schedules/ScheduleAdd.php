<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Branch;
use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;

class ScheduleAdd extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;

    public $date, $branch_id;

    public function updatingSearch() {
        $this->resetPage('branch-page');
    }

    public function submitRequest() {
        $this->validate([
            'date' => 'required',
            'branch_id' => 'required'
        ]);

        $schedule = new UserBranchSchedule([
            'user_id' => auth()->user()->id,
            'branch_id' => $this->branch_id,
            'date' => $this->date,
            'status' => 'schedule request',
            'source' => 'request'
        ]);
        $schedule->save();

        $approval = new UserBranchScheduleApproval([
            'user_branch_schedule_id' => $schedule->id,
            'user_id' => auth()->user()->id,
            'status' => 'schedule request',
            'remarks' => null
        ]);
        $approval->save();

        return redirect(request()->header('Referer'));
    }

    public function selectBranch($branch_id) {
        if($this->branch_id == $branch_id) {
            $this->reset('branch_id');
        } else {
            $this->branch_id = $branch_id;
        }
    }

    public function render()
    {
        if($this->search != '') {
            $branches = Branch::whereHas('account', function($query) {
                $query->whereHas('users', function($qry) {
                    $qry->where('id', auth()->user()->id);
                });
            })
            ->where(function($query) {
                $query->where('branch_code', 'like', '%'.$this->search.'%')
                ->orWhere('branch_name', 'like', '%'.$this->search.'%');
            })
            ->paginate(10, ['*'], 'branch-page')->onEachSide(1);

        } else {

            $branches = Branch::whereHas('account', function($query) {
                $query->whereHas('users', function($qry) {
                    $qry->where('id', auth()->user()->id);
                });
            })
            ->paginate(10, ['*'], 'branch-page')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-add')->with([
            'branches' => $branches
        ]);
    }
}
