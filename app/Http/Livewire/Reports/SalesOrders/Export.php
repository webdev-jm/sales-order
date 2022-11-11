<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

use App\Exports\SOReportExport;
use Maatwebsite\Excel\Facades\Excel;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class Export extends Component
{
    public $year, $month, $group_code;

    protected $queryString = [
        'year',
        'month',
        'group_code'
    ];

    public function export() {
        return Excel::download(new SOReportExport($this->year, $this->month, $this->group_code), 'SO Reports'.time().'.xlsx');

        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.reports.sales-orders.export');
    }
}
