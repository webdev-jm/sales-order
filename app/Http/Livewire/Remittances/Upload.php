<?php

namespace App\Http\Livewire\Remittances;

use Livewire\Component;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Upload extends Component
{
    use WithFileUploads;

    public $upload_file;

    public function checkFile() {
        $this->validate([
            'upload_file' => 'required|mimes:xls,xlsx,csv'
        ]);

        $path1 = $this->upload_file->storeAs('remittances', $this->upload_file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowResults = []; // Array to store results for this row
            foreach ($row->getCellIterator() as $cell) {
                $rowResults[] = $cell->getCalculatedValue(); // Store the result of the formula
            }
            $data[] = $rowResults; // Store the results for this row in the main results array
        }

        dd($data);
    }

    public function render()
    {
        return view('livewire.remittances.upload');
    }
}
