<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserChangePassword extends Component
{
    public $user_id, $user, $password, $password_confirmation;

    protected $listeners = [
        'setUser' => 'setUser'
    ];

    public function changePassword() {
        $this->validate([
            'password' => [
                'required', 'confirmed'
            ]
        ]);

        $this->user->update([
            'password' => Hash::make($this->password)
        ]);

        return redirect()->to('/user');
    }

    public function setUser($user_id) {
        $this->user = User::findOrFail($user_id);
    }

    public function render()
    {
        return view('livewire.users.user-change-password');
    }
}
