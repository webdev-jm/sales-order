<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;

class AccountLogin extends Component
{
    public $accounts;
    public $account;

    public function loginModal($account_id) {
        $this->account = Account::findOrFail($account_id);
        $this->dispatchBrowserEvent('openFormModal');
    }

    public function mount() {
        $this->accounts = auth()->user()->accounts;
    }

    public function render()
    {
        return view('livewire.accounts.account-login');
    }
}
