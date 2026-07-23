<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use App\Services\AccountLoginResolver;
use AccountLoginModel;

use Intervention\Image\Facades\Image;

class AccountLoginForm extends Component
{

    public $account, $accuracy, $longitude, $latitude, $activities;

    /**
     * The account login already open, if any, so the form can warn that
     * signing in switches away from it.
     */
    public $logged_account;

    protected $listeners = ['loginForm' => 'set'];

    public function login() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        // Signing in while already signed in to an account is a switch: the
        // previous login is closed and the new one takes over.
        app(AccountLoginResolver::class)->switchTo(auth()->user(), $this->account, [
            'longitude' => $this->longitude,
            'latitude'  => $this->latitude,
            'accuracy'  => $this->accuracy,
        ]);

        return redirect()->to('/sales-order');
    }

    public function set($account_id) {
        $this->account = Account::findOrFail($account_id);
        $this->logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
            ->whereNull('time_out')
            ->first();
    }

    public function mount() {
        $this->account = Account::first();
    }

    public function render()
    {
        return view('livewire.accounts.account-login-form');
    }
}
