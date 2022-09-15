<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\Account;
use App\Models\User;
// use Livewire\WithPagination;

use App\Http\Traits\GlobalTrait;

class UserAssignForm extends Component
{
    // use WithPagination;
    use GlobalTrait;

    public $user_id, $assigned, $user, $search;
    public $setting;
    
    // protected $paginationTheme = 'bootstrap';

    // public function updatingSearch()
    // {
    //     $this->resetPage();
    // }

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

        $this->setting = $this->getSettings();
    }

    public function render()
    {
        $accounts = Account::orderBy('account_code', 'ASC')
        ->where('account_code', 'like', '%'.$this->search.'%')
        ->orWhere('short_name', 'like', '%'.$this->search.'%')
        ->limit($this->setting->data_per_page)->get();
        // ->paginate(12, ['*'], 'accountPage')->onEachSide(1);

        return view('livewire.users.user-assign-form')->with([
            'accounts' => $accounts
        ]);
    }
}
