<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use AccountLoginModel;

class AccountLoginForm extends Component
{
    public $account, $accuracy, $longitude, $latitude, $activities, $picture_file;

    public function login() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'activities' => 'required',
        ]);

        $user = auth()->user();
        // check if logged in to other accounts
        $logged_account = AccountLoginModel::where('user_id', $user->id)
        ->whereNull('time_out')
        ->first();
        if(empty($logged_account)) { // new login 
            $login = new AccountLoginModel([
                'user_id' => $user->id,
                'account_id' => $this->account->id,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'accuracy' => $this->accuracy,
                'activities' => $this->activities,
                'time_in' => now(),
            ]);
            $login->save();

            return redirect()->to('/home');
        }
    }

    public function render()
    {
        return view('livewire.accounts.account-login-form');
    }
}
