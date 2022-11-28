<?php

namespace App\Http\Livewire\War;

use Livewire\Component;

use App\Models\User;
use App\Models\BranchLogin;

class WarAreaDetail extends Component
{
    public $user, $date, $branch_logins;

    protected $listeners = [
        'setDate' => 'getActivities'
    ];

    public function getActivities($date) {
        $this->branch_logins = BranchLogin::where('user_id', $this->user->id)
        ->where('time_in', 'like', $date.'%')
        ->get();
    }

    public function mount($user_id) {
        $this->user = User::find($user_id);
    }

    public function render()
    {
        return view('livewire.war.war-area-detail');
    }
}
