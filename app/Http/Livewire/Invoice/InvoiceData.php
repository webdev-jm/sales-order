<?php

namespace App\Http\Livewire\Invoice;

use Livewire\Component;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Invoice;

use App\Exports\InvoiceExport;

class InvoiceData extends Component
{
    use WithFileUploads;

    public $logged_account;
    public $upload_file;
    public $po_data;

    public function DownloadData() {
        if(!empty($this->po_data)) {
            return Excel::download(new InvoiceExport($this->po_data), 'PO Invoice'.time().'.xlsx');
        }
    }

    public function uploadFile() {
        $this->validate([
            'upload_file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->upload_file->storeAs('invoice-po', $this->upload_file->getClientOriginalName());
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

        $this->getData($data);
    }

    public function getData($data) {
        $po_data = [];
        foreach($data as $key => $val) {
            $po_number = $val[0];
            
            if($po_number !== 'PO NUMBER') {
                $invoices = Invoice::where('po_number', 'LIKE', '%'.$po_number.'%')
                    ->where('account_code', $this->logged_account->account->account_code)
                    ->where('company', $this->logged_account->account->company->name)
                    ->get();
    
                if(!empty($invoices->count())) {
                    $po_data[$po_number] = $invoices;
                }
            }

        }

        $this->po_data = $po_data; 
    }

    public function mount($logged_account) {
        $this->logged_account = $logged_account;
    }

    public function render()
    {
        return view('livewire.invoice.invoice-data');
    }
}
