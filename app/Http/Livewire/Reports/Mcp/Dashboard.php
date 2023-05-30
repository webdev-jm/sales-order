<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Company;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use App\Exports\MCPDashboardExport;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $year, $month, $companies, $company, $search;

    public function updatedSearch() {
        $this->resetPage('mcp-user-page');
    }

    public function export() {
        return Excel::download(new MCPDashboardExport($this->year, $this->month, $this->company, $this->search), 'MCP Dashboard'.time().'.xlsx');
    }

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }
        if(empty($this->month)) {
            $this->month = date('m');
        }

        // COMPANIES
        $this->companies = Company::all();
    }

    public function render()
    {
        $months = [];
        for($i = 1; $i <= 12; $i++) {
            $i = $i < 10 ? '0'.$i : $i;
            $months[$i] = date('F', strtotime($this->year.'-'.$i.'-01'));
        }

        DB::statement('SET sql_mode=(SELECT REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY",""));');

        $mcp_results = DB::table('users as u')
            ->select(
                DB::raw('CONCAT(u.firstname, " ", u.lastname) as name'),
                'u.group_code',
                DB::raw('COUNT(DISTINCT ubs.branch_id, ubs.date) as mcp'),
                DB::raw('COUNT(DISTINCT bl.branch_id, DATE(bl.time_in)) as total_visit'),
                DB::raw('COUNT(DISTINCT
                    CASE WHEN
                        NOT EXISTS(SELECT * FROM user_branch_schedules ubs WHERE ubs.branch_id = bl.branch_id AND ubs.user_id = bl.user_id AND (source = "activity-plan" OR source = "request"))
                    THEN 
                        bl.branch_id
                    END,
                    CASE WHEN 
                        NOT EXISTS(SELECT * FROM user_branch_schedules ubs WHERE ubs.branch_id = bl.branch_id AND ubs.user_id = bl.user_id AND (source = "activity-plan" OR source = "request"))
                    THEN 
                        DATE(bl.time_in)
                    END

                ) as deviation')
            )
            ->leftJoin('user_branch_schedules as ubs', 'ubs.user_id', '=', 'u.id')
            ->leftJoin('branch_logins as bl', 'bl.user_id', '=', 'u.id')
            ->leftJoin('branches as b', function($join) {
                $join->on('b.id', '=', 'ubs.branch_id')
                    ->whereRaw('b.id = bl.branch_id');
            })
            ->leftJoin('accounts as a', 'a.id', '=', 'b.account_id')
            ->where(function($query) {
                $query->where(DB::raw('YEAR(ubs.date)'), $this->year)
                    ->where(DB::raw('MONTH(ubs.date)'), $this->month)
                    ->where('ubs.source', 'activity-plan')
                    ->orWhere(function($query) {
                        $query->where(DB::raw('YEAR(bl.time_in)'), $this->year)
                            ->where(DB::raw('MONTH(bl.time_in)'), $this->month);
                    });
            })
            ->when(!empty($this->company), function($query) {
                $query->where('a.company_id', $this->company);
            })
            ->groupBy('name')
            ->paginate(10, ['*'], 'mcp-user-page')
            ->onEachSide(1);

        return view('livewire.reports.mcp.dashboard')->with([
            'months' => $months,
            'mcp_results' => $mcp_results
        ]);
    }
}
