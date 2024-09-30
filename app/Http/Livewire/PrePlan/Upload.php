<?php

namespace App\Http\Livewire\PrePlan;

use Livewire\Component;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PrePlanUploadImport;

class Upload extends Component
{
    use WithFileUploads;

    public $file;

    public function updatedFile() {
        $this->validate([
            'file' => [
                'required',
                'mimes:xls,xlsx',
            ]
        ]);
    }

    public function upload() {
        $this->validate([
            'file' => [
                'required',
                'mimes:xls,xlsx',
            ]
        ]);

        Excel::import(new PrePlanUploadImport, $this->file);

        // logs
        activity('upload')
            ->log(':causer.firstname :causer.lastname has uploaded pre plans');
    }

    public function render()
    {
        return view('livewire.pre-plan.upload');
    }
}
