<?php

namespace App\Http\Livewire\War;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\User;
use App\Models\BranchLogin;

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

        return view('livewire.war.war-area-detail')->with([
            'branch_logins' => $branch_logins
        ]);
    }
}
