<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;

use Livewire\WithFileUploads;

class AccountBranchLoginForm extends Component
{
    use WithFileUploads;

    public $logged_branch, $branch;
    public $activities, $picture_file;
    public $image_url;

    public function mount($logged_branch) {
        $this->branch = $logged_branch->branch;
    }

    public function render()
    {
        return view('livewire.accounts.account-branch-login-form');
    }
}
