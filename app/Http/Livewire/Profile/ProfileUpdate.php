<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Validation\Rule;

class ProfileUpdate extends Component
{
    public $firstname, $lastname, $email;

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
            ]
        ]);

        auth()->user()->update([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email
        ]);

        session()->flash('message', 'User details has been updated.');
    }

    public function mount() {
        $this->firstname = auth()->user()->firstname;
        $this->lastname = auth()->user()->lastname;
        $this->email = auth()->user()->email;
    }

    public function render()
    {
        return view('livewire.profile.profile-update');
    }
}
