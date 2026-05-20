<?php

namespace App\Http\Livewire\War;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\ActivityPlanDetail;
use App\Models\User;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

class WarAreaDetail extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user, $date;

    protected $listeners = [
        'setDate' => 'getActivities'
    ];

    public function getActivities($date) {
        $this->date = $date;
        $this->resetPage('war-branch-logins');
    }

    public function mount($user_id) {
        $this->user = User::find($user_id);
    }

    public function render()
    {
        $branch_logins = BranchLogin::where('user_id', $this->user->id)
            ->where('time_in', 'like', $this->date.'%')
            ->paginate(1, ['*'], 'war-branch-logins');

        $schedules = UserBranchSchedule::where('user_id', $this->user->id)
            ->where('date', $this->date)
            ->paginate(5, ['*'], 'war-planned-activities');

        $is_on_leave = !empty($this->date) && ActivityPlanDetail::where('is_on_leave', true)
            ->where('date', $this->date)
            ->whereHas('activity_plan', function ($q) {
                $q->where('status', 'approved')
                  ->where('user_id', $this->user->id);
            })
            ->exists();

        return view('livewire.war.war-area-detail')->with([
            'branch_logins' => $branch_logins,
            'schedules' => $schedules,
            'is_on_leave' => $is_on_leave,
        ]);
    }
}
