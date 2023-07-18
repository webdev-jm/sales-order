<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use App\Exports\MCPDashboardExport;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $year, $month, $search;

    public function updatedSearch() {
        $this->resetPage('mcp-user-page');
    }

    public function export() {
        return Excel::download(new MCPDashboardExport($this->year, $this->month, $this->search), 'MCP Dashboard'.time().'.xlsx');
    }

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }
        if(empty($this->month)) {
            $this->month = date('m');
        }
    }

    public function render()
    {
        $months = [];
        for($i = 1; $i <= 12; $i++) {
            $i = $i < 10 ? '0'.$i : $i;
            $months[$i] = date('F', strtotime($this->year.'-'.$i.'-01'));
        }

        $query = UserBranchSchedule::query()
                ->selectRaw('u.id as uid')
                ->selectRaw('CONCAT(u.firstname, " ", u.lastname) as name')
                ->selectRaw('COUNT(IF(status IS NULL, user_branch_schedules.id, NULL)) as schedule_count')
                ->selectRaw('COUNT(IF(status IS NULL AND source = "deviation", user_branch_schedules.id, NULL)) as deviation_count')
                ->selectRaw('COUNT(IF(status IS NULL AND source = "request", user_branch_schedules.id, NULL)) as request_count')
            ->join('users as u', 'u.id', '=', 'user_branch_schedules.user_id')
            ->where(DB::raw('MONTH(date)'), $this->month)
            ->where(DB::raw('YEAR(date)'), $this->year)
            ->orderBy('name')
            ->groupBy(['uid', 'name']);

        $schedule_results = $query->paginate(10, ['*'], 'mcp-user-page');

        $user_data = array();
        foreach($schedule_results as $result) {
            $branch_logins = BranchLogin::query()
                ->selectRaw('DISTINCT branch_id, DATE(time_in) as date')
                ->where('user_id', $result->uid)
                ->where(DB::raw('MONTH(time_in)'), $this->month)
                ->where(DB::raw('YEAR(time_in)'), $this->year)
                ->get();
    
            $visited_count = $branch_logins->filter(function ($branch_login) use($result) {
                return UserBranchSchedule::where('user_id', $result->uid)
                    ->where('branch_id', $branch_login->branch_id)
                    ->where('date', $branch_login->date)
                    ->whereNull('status')
                    ->exists();
            })->count();

            $user_data[$result->uid]['visited'] = $visited_count;
            $unscheduled_count = $branch_logins->count() - $visited_count;

            $deviation = $result->deviation_count + $unscheduled_count;
            $user_data[$result->uid]['deviation'] = $deviation;
        }

        return view('livewire.reports.mcp.dashboard')->with([
            'months' => $months,
            'schedule_results' => $schedule_results,
            'user_data' => $user_data
        ]);
    }
}
