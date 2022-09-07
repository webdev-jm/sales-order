<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;

class AccountLogin extends Component
{
    public $acount;

    public function loginModal() {
        $this->dispatchBrowserEvent('openFormModal'.$this->account->id);
    }

    public function mount($account_id) {
        $this->account = Account::findOrFail($account_id);
    }

    public function render()
    {
        return view('livewire.accounts.account-login');
    }
}
