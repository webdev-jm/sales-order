<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

class Create extends Component
{
    public $accounts = [];

    public function mount() {
        $this->accounts = auth()->user()->accounts;
    }

    public function render()
    {
        return view('livewire.paf.create');
    }
}
