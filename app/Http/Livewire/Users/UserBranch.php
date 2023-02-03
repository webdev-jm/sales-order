<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use App\Models\Account;
use Livewire\WithPagination;

class UserBranch extends Component
{

    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage('account-branch-page');
    }

    public $user, $search;

    protected $listeners = [
        'userBranch' => 'setUserBranch'
    ];

    public function setUserBranch($user_id) {
        $this->user = User::findOrFail($user_id);
    }

    public function render()
    {
        $accounts = [];
        if(!empty($this->user)) {
            $accounts = $this->user->accounts()->orderBy('account_code', 'ASC')
            ->where(function($query) {
                $query->where('account_code', 'like', '%'.$this->search.'%')
            ->orWhere('short_name', 'like', '%'.$this->search.'%');
            })
            ->paginate(5, ['*'], 'account-branch-page');
        }

        return view('livewire.users.user-branch')->with([
            'accounts' => $accounts
        ]);
    }
}
