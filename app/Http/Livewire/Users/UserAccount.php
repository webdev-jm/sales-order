<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;

class UserAccount extends Component
{
    public $user_id;

    public function assignModals() {
        $this->dispatchBrowserEvent('openFormModal'.$this->user_id);
    }

    public function render()
    {
        return view('livewire.users.user-account');
    }
}
