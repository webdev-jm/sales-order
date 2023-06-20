<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Validation\Rule;

class ProfileUpdate extends Component
{
    public $firstname, $lastname, $email, $notify_email;

    public function submitForm() {
        $this->validate([
            'firstname' => [
                'required'
            ],
            'lastname' => [
                'required'
            ],
            'email' => [
                'required', Rule::unique('users')->ignore(auth()->user()->id)
            ],
            'notify_email' => [
                'email'
            ]
        ]);

        auth()->user()->update([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'notify_email' => $this->notify_email,
        ]);

        session()->flash('message', 'User details has been updated.');
    }

    public function mount() {
        $this->firstname = auth()->user()->firstname;
        $this->lastname = auth()->user()->lastname;
        $this->email = auth()->user()->email;
        $this->notify_email = auth()->user()->notify_email;
    }

    public function render()
    {
        return view('livewire.profile.profile-update');
    }
}
