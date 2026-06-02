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
    public string $successMessage = '';
    public string $errorMessage   = '';

    public function updatedFile(): void
    {
        $this->validate([
            'file' => [
                'required',
                'mimes:xls,xlsx',
            ]
        ]);
    }

    public function upload(): void
    {
        $this->validate([
            'file' => [
                'required',
                'mimes:xls,xlsx',
            ]
        ]);

        $this->successMessage = '';
        $this->errorMessage   = '';

        try {
            $import = new PrePlanUploadImport;
            Excel::import($import, $this->file);

            activity('upload')
                ->log(':causer.firstname :causer.lastname has uploaded pre plans');

            $this->successMessage = "Imported {$import->importedCount} pre-plan(s), skipped {$import->skippedCount} row(s).";
            $this->file = null;
            $this->dispatchBrowserEvent('pre-plan-uploaded');

        } catch (\Exception $e) {
            $this->errorMessage = 'Upload failed. Please check the file format and try again.';
        }
    }

    public function render()
    {
        return view('livewire.pre-plan.upload');
    }
}
