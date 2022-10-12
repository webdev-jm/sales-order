<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\Account;
use App\Models\User;

use Livewire\WithPagination;

class UserAccount extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user, $search, $assigned;

    public function updatingSearch()
    {
        $this->resetPage('account-page');
    }
    
    protected $listeners = [
        'userAccount' => 'userAccount'
    ];

    public function assign($account_id) {
        // check
        $check = $this->user->accounts()->where('id', $account_id)->first();
        if(empty($check)) {
            $this->user->accounts()->attach($account_id);
        } else {
            $this->user->accounts()->detach($account_id);
        }

        $this->userAccount($this->user->id);
    }

    public function userAccount($user_id) {
        $this->user = User::findOrFail($user_id);
        $this->assigned = $this->user->accounts;
    }

    public function render()
    {
        $accounts = Account::orderBy('account_code', 'ASC')
        ->where('account_code', 'like', '%'.$this->search.'%')
        ->orWhere('account_name', 'like', '%'.$this->search.'%')
        ->orWhere('short_name', 'like', '%'.$this->search.'%')
        ->paginate(12, ['*'], 'account-page')->onEachSide(1);

        return view('livewire.users.user-account')->with([
            'accounts' => $accounts
        ]);
    }
}
