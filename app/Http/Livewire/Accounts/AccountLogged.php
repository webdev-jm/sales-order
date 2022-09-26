<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use AccountLoginModel;

class AccountLogged extends Component
{
    public $logged;

    public function loggedForm() {
        $this->dispatchBrowserEvent('openLoggedModal'.$this->logged->id);
    }

    public function mount() {
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();
        if(empty($logged_account)) {
            Session::forget('logged_account');
        } else {
            // // check
            // $check = auth()->user()->accounts()->where('id', $logged_account->account_id)->first();
            // if(empty($check)) {
            //     Session::forget('logged_account');
            //     $logged_account->update([
            //         'time_out' => now()
            //     ]);
            // } else {
                Session::put('logged_account', $logged_account);
            // }
        }

        $this->logged = Session::get('logged_account');
    }

    public function render()
    {
        return view('livewire.accounts.account-logged');
    }
}
