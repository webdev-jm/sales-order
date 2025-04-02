<?php

namespace App\Http\Livewire\Accounts\Templates;

use Livewire\Component;

class TemplateList extends Component
{
    public $account;

    public function mount($account) {
        $this->account = $account;
    }

    public function render()
    {
        return view('livewire.accounts.templates.template-list');
    }
}
