<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Component
{
    public $current_password, $password, $password_confirmation;
    public $password_error = '';

    public function submitForm() {
        $this->validate([
            'current_password' => [
                'required', 
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        return $fail(__('The current password is incorrect.'));
                    }
                }
            ],
            'password' => [
                'required', 'confirmed'
            ]
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->password)
        ]);

        session()->flash('message', 'Password has been updated.');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.profile.change-password');
    }
}
