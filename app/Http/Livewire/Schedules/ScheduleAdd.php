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

class ScheduleAdd extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;

    public $date, $objective, $branch_id, $account_id;

    public function updatingSearch() {
        $this->resetPage('branch-page');
    }

    public function submitRequest() {
        $this->validate([
            'date' => 'required',
            'objective' => 'required',
            'branch_id' => 'required'
        ]);

        // check if already exists
        $exist = UserBranchSchedule::where('user_id', auth()->user()->id)
            ->where('branch_id', $this->branch_id)
            ->where('date', $this->date)
            ->whereNull('status')
            ->first();
        
        if(empty($exist)) {
            $schedule = new UserBranchSchedule([
                'user_id' => auth()->user()->id,
                'branch_id' => $this->branch_id,
                'date' => $this->date,
                'status' => NULL,
                'objective' => $this->objective,
                'source' => 'request'
            ]);
            $schedule->save();

            // $approval = new UserBranchScheduleApproval([
            //     'user_branch_schedule_id' => $schedule->id,
            //     'user_id' => auth()->user()->id,
            //     'status' => 'schedule request',
            //     'remarks' => null
            // ]);
            // $approval->save();
            
            // logs
            activity('created')
            ->performedOn($schedule)
            ->log(':causer.firstname :causer.lastname added a new schedule :subject.date');
    
            // notifications
            // $user_ids = auth()->user()->getSupervisorIds();
            // foreach($user_ids as $user_id) {
            //     if(auth()->user()->id != $user_id) {
            //         $user = User::find($user_id);
            //         Notification::send($user, new ScheduleAddRequest($schedule));
            //     }
            // }
    
            $supervisor_id = auth()->user()->getImmediateSuperiorId();
            if(auth()->user()->id != $supervisor_id) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    try {
                        Notification::send($user, new ScheduleAddRequest($schedule));
                    } catch(\Exception $e) {
                        Log::error('Notification failed: '.$e->getMessage());
                    }
                }
            }
        }

        return redirect(request()->header('Referer'));
    }

    public function selectBranch($branch_id) {
        if($this->branch_id == $branch_id) {
            $this->reset('branch_id');
        } else {
            $this->branch_id = $branch_id;
        }
    }

    public function updatedAccountId() {
        $this->resetPage('branch-page');
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
            ->when(!empty($this->account_id), function($query) {
                $query->where('account_id', $this->account_id);
            })
            ->paginate(10, ['*'], 'branch-page')->onEachSide(1);

        } else {

            $branches = Branch::whereHas('account', function($query) {
                $query->whereHas('users', function($qry) {
                    $qry->where('id', auth()->user()->id);
                });
            })
            ->when(!empty($this->account_id), function($query) {
                $query->where('account_id', $this->account_id);
            })
            ->paginate(10, ['*'], 'branch-page')->onEachSide(1);
        }

        return view('livewire.schedules.schedule-add')->with([
            'branches' => $branches
        ]);
    }
}
