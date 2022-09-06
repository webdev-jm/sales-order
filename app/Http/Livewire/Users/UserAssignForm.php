<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\Account;
use App\Models\User;

class UserAssignForm extends Component
{
    public $user_id, $assigned, $user;

    public function assign($account_id) {
        // check
        $check = $this->user->accounts()->where('id', $account_id)->first();
        if(empty($check)) {
            $this->user->accounts()->attach($account_id);
        } else {
            $this->user->accounts()->detach($account_id);
        }

        $this->mount($this->user_id);
    }

    public function mount($user_id) {
        $user = User::findOrFail($user_id);
        $this->user = $user;
        $this->assigned = $user->accounts;
    }

    public function render()
    {
        $accounts = Account::orderBy('account_code', 'ASC')->get();
        return view('livewire.users.user-assign-form')->with([
            'accounts' => $accounts
        ]);
    }
}
