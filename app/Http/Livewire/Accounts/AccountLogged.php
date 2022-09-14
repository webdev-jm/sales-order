<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class AccountLogged extends Component
{
    public $logged;

    public function loggedForm() {
        $this->dispatchBrowserEvent('openLoggedModal'.$this->logged->id);
    }

    public function mount() {
        $this->logged = Session::get('logged_account');
    }

    public function render()
    {
        return view('livewire.accounts.account-logged');
    }
}
