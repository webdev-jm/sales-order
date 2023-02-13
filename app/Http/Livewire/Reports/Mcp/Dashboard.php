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

        $users = User::orderBy('firstname', 'ASC');
        if(!empty($this->search)) {
            $users->where('firstname', 'like', '%'.$this->search.'%')
            ->orWhere('lastname', 'like', '%'.$this->search.'%')
            ->orWhere('group_code', 'like', '%'.$this->search.'%');
        }

        $users = $users->paginate(10, ['*'], 'mcp-user-page')
        ->onEachSide(1);

        $date_string = $this->year.'-'.($this->month < 10 ? '0'.(int)$this->month : $this->month);

        $data = [];
        foreach($users as $user) {
            // MCP
            $schedules = UserBranchSchedule::where('user_id', $user->id)
            ->whereNull('status')
            ->where('date', 'like', $date_string.'%');
            // COMPANY FILTER
            if(!empty($this->company)) {
                $schedules->whereHas('branch', function($query) {
                    $query->whereHas('account', function($qry) {
                        $qry->where('company_id', $this->company);
                    });
                });
            }

            $schedules = $schedules->get();
            // VISITED
            $mcp = 0;
            $visited = 0;
            $schedule_dates = [];
            foreach($schedules as $schedule) {
                $mcp++;
                $schedule_dates[] = $schedule->date;

                // VISITED
                $branch_logins = BranchLogin::where('user_id', $schedule->user_id)
                ->where('branch_id', $schedule->branch_id)
                ->where('time_in', 'like', $schedule->date.'%');

                // COMPANY FILTER
                if(!empty($this->company)) {
                    $branch_logins->whereHas('branch', function($query) {
                        $query->whereHas('account', function($qry) {
                            $qry->where('company_id', $this->company);
                        });
                    });
                }

                $branch_logins = $branch_logins->count();

                if($branch_logins > 0) {
                    $visited++;
                }
            }

            $schedule_dates = array_unique($schedule_dates);

            // TOTAL LOGIN
            $total_login = BranchLogin::select(DB::raw("count(DISTINCT user_id, branch_id, date(time_in)) as total"))
            ->where('time_in', 'like', $date_string.'%')
            ->where('user_id', $user->id);

            // COMPANY FILTER
            if(!empty($this->company)) {
                $total_login->whereHas('branch', function($query) {
                    $query->whereHas('account', function($qry) {
                        $qry->where('company_id', $this->company);
                    });
                });
            }

            $total_login = $total_login->get();

            $deviations_count = $total_login[0]['total'] - $visited;

            $performance = 0;
            if($mcp > 0 && $visited > 0) {
                $performance = ($visited / $mcp) * 100;
            }

            $data[$user->id] = [
                'MCP' => $mcp,
                'VISITED' => $visited,
                'DEVIATION' => $deviations_count,
                'PERFORMANCE' => $performance
            ];
        }

        return view('livewire.reports.mcp.dashboard')->with([
            'users' => $users,
            'data' => $data,
            'months' => $months
        ]);
    }
}
