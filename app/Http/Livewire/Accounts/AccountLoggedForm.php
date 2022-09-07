<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;

class AccountLoggedForm extends Component
{
    public $logged, $account, $accuracy, $longitude, $latitude, $activities, $picture_file;

    public function logout() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'activities' => 'required',
        ]);

        $this->logged->update([
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'accuracy' => $this->accuracy,
            'activities' => $this->activities,
            'time_out' => now(),
        ]);

        return redirect()->to('/home');
    }

    public function mount() {
        $this->account = Account::findOrFail($this->logged->account_id);
        $this->accuracy = $this->logged->accuracy;
        $this->longitude = $this->logged->longitude;
        $this->latitude = $this->logged->latitude;
        $this->activities = $this->logged->activities;
    }

    public function render()
    {
        return view('livewire.accounts.account-logged-form');
    }
}
