<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;

class AccountLogged extends Component
{
    public $logged;

    public function loggedForm() {
        $this->dispatchBrowserEvent('openLoggedModal'.$this->logged->id);
    }

    public function mount() {
        $this->logged = auth()->user()->account_logins()
        ->whereNull('time_out')
        ->first();
    }

    public function render()
    {
        return view('livewire.accounts.account-logged');
    }
}
