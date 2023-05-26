<?php

namespace App\Http\Livewire\ProductivityReport;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductivityReportImport;

use Illuminate\Support\Facades\Session;

use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Branch;
use App\Models\Classification;

class Details extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $perPage = 10;

    public $err;
    public $upload_file;
    public $details;

    public function uploadFile() {
        if(!empty($this->upload_file)) {
            unset($this->err['file']);
            $this->reset('details');
            $this->resetPage();

            $imports = Excel::toArray(new ProductivityReportImport, $this->upload_file);
            foreach($imports[0] as $row) {
                
                // VISITED
                $visited = 0;
                if(strtolower($row[4]) == 'yes' || $row[4] == 1) {
                    $visited = 1;
                }
                
                // STORE / BRANCH
                $branch = Branch::where('branch_code', $row[2])
                    ->orWhere('branch_name', $row[2])
                    ->first();

                $store = $row[2];
                if(!empty($branch)) {
                    $store = $branch->branch_code.' '.$branch->branch_name;
                }

                // CLASSIFICATION
                $classification = Classification::where('classification_code', $row[3])
                    ->orWhere('classification_name', $row[3])
                    ->first();

                $channel = $row[3];
                if(!empty($classification)) {
                    $channel = $classification->classification_code.' '.$classification->classification_name;
                }

                $this->details[] = [
                    'date' => $row[0],
                    'salesman' => $row[1],
                    'store' => $store,
                    'classification' => $channel,
                    'visited' => $visited,
                    'sales' => $row[5],
                    'branch_id' => $branch->id ?? NULL,
                    'classification_id' => $classification->id ?? NULL,
                ];
            }

            Session::put('productivity_report_data', $this->details);
            
        } else {
            $this->err['file'] = 'Please select file first before uploading.';
        }
    }

    private function paginateArray($data, $perPage)
    {
        $currentPage = $this->page ?: 1;
        $items = collect($data);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $items->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage
        );

        return $paginator;
    }

    public function render()
    {
        $paginatedData = $this->paginateArray($this->details, $this->perPage);

        return view('livewire.productivity-report.details', compact('paginatedData'));
    }
}
