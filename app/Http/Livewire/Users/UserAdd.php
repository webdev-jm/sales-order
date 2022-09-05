<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserAdd extends Component
{
    public $roles;
    public $firstname, $middlename, $lastname, $email, $role;

    public function addForm() {
        $this->dispatchBrowserEvent('openFormModal');
    }

    public function submitForm() {
    }

    public function mount() {
        $this->roles = Role::orderBy('name', 'ASC')->get();
    }

    public function render()
    {
        return view('livewire.users.user-add');
    }
}
