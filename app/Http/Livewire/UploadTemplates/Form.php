<?php

namespace App\Http\Livewire\UploadTemplates;

use Livewire\Component;

class Form extends Component
{
    public $lines;
    public $name, $breakpoint, $breakpoint_col;

    public function saveTemplate() {

    }

    public function addLine() {
        $this->lines[] = [
            'column_name' => '',
            'column_number' => ''
        ];
    }

    public function removeLine($key) {
        unset($this->lines[$key]);
    }

    public function mount() {
        $this->lines[] = [
            'column_name' => '',
            'column_number' => ''
        ];
    }
    
    public function render()
    {
        return view('livewire.upload-templates.form');
    }
}
