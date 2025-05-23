<?php

namespace App\Http\Livewire\Coe\Reports;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\DB;

class CompetetiveReports extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user_data, $account_data;
    public $date_from, $date_to;

    protected $listeners = [
        'setUser' => 'setUser', 
        'setAccount' => 'setAccount',
        'setDateFrom' => 'setDateFrom',
        'setDateTo' => 'setDateTo',
    ];

    public function setUser($user_data) {
        $this->user_data = $user_data;
        $this->resetPage('competetive-report-page');
    }

    public function setAccount($account_data) {
        $this->account_data = $account_data;
        $this->resetPage('competetive-report-page');
    }

    public function setDateFrom($date_from) {
        $this->date_from = $date_from;
    }

    public function setDateTo($date_to) {
        $this->date_to = $date_to;
    }

    public function render()
    {
        $results = DB::table('channel_operation_competetive_reports as cocr')
            ->select(
                'a.short_name',
                'cocr.company_name',
                'cocr.product_description',
                'cocr.srp',
                'cocr.type_of_promotion',
            )
            ->join('channel_operations as co', 'co.id', '=', 'cocr.channel_operation_id')
            ->join('branch_logins as bl', 'bl.id', '=', 'co.branch_login_id')
            ->join('branches as b', 'b.id', '=', 'bl.branch_id')
            ->join('accounts as a', 'a.id', '=', 'b.account_id')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('bl.user_id', $this->user_data);
            })
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('a.id', $this->account_data);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('date', '<=', $this->date_to);
            })
            ->paginate(10, ['*'], 'competetive-report-page');

        return view('livewire.coe.reports.competetive-reports')->with([
            'results' => $results
        ]);
    }
}
