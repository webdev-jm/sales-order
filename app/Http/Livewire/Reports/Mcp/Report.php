<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;
use App\Models\User;

use Illuminate\Support\Facades\DB;

use App\Exports\MCPReportExport;
use Maatwebsite\Excel\Facades\Excel;

class Report extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $schedules, $actuals, $deviations;
    public $user_id, $date_from, $date_to;

    public function filter() {
        $this->resetPage('report-page');

        $this->emit('setFilter', $this->user_id, $this->date_from, $this->date_to);
    }

    public function export() {
        return Excel::download(new MCPReportExport($this->user_id, $this->date_from, $this->date_to), 'MCPReports'.time().'.xlsx');
    }

    public function showDetail($login_id) {
        $this->emit('showDetail', $login_id);
        $this->dispatchBrowserEvent('showDetail');
    }

    public function mount() {
        $this->date_from = date('Y-m').'-01';
    }

    public function render()
    {

        // get schedule dates of user
        if(!empty($this->user_id) || !empty($this->date_from) || !empty($this->date_to)) {
            $schedules_dates = UserBranchSchedule::orderBy('user_id', 'ASC')
            ->orderBy('date', 'ASC')
            ->select('date', 'user_id')->distinct()
            ->whereNull('status');
            // USER
            if(!empty($this->user_id)) {
                $schedules_dates->where('user_id', $this->user_id);
            }
            // DATE FROM
            if(!empty($this->date_from)) {
                $schedules_dates->where('date', '>=', $this->date_from);
            }
            // DATE TO
            if(!empty($this->date_to)) {
                $schedules_dates->where('date', '<=', $this->date_to);
            }
            $schedules_dates = $schedules_dates->groupBy('date', 'user_id')->paginate(7, ['*'], 'report-page')->onEachSide(1);

        } else {
            $schedules_dates = UserBranchSchedule::orderBy('user_id', 'ASC')
            ->orderBy('date', 'ASC')
            ->select('date', 'user_id')->distinct()
            ->whereNull('status')
            ->groupBy('date', 'user_id')
            ->paginate(7, ['*'], 'report-page')->onEachSide(1);
        }

        foreach($schedules_dates as $schedule_date) {
            // get schedules
            $schedules = UserBranchSchedule::where('user_id', $schedule_date->user_id)
            ->where('date', $schedule_date->date)
            ->whereNull('status')
            ->where(function($query) {
                $query->where('source', 'activity-plan')
                    ->orWhere('source', 'request');
            })
            ->get();

            $this->schedules[$schedule_date->user_id][$schedule_date->date] = $schedules;

            foreach($schedules as $schedule) {
                // get actual branch sign-in
                $branch_logins = BranchLogin::where('user_id', $schedule->user_id)
                ->where('branch_id', $schedule->branch_id)
                ->where('time_in', 'like', $schedule->date.'%')
                ->get();

                if(!empty($branch_logins)) {
                    $this->actuals[$schedule->id] = $branch_logins;
                }
            }

            // get deviated schedules
            $deviations_data = BranchLogin::orderBy('time_in', 'ASC')
            ->where('user_id', $schedule_date->user_id)
            ->where('time_in', 'like', $schedule_date->date.'%')
            ->whereNotIn('branch_id', $schedules->pluck('branch_id'))
            ->get();

            foreach($deviations_data as $key => $deviation) {

                $this->deviations[$schedule_date->user_id][$schedule_date->date][$deviation->branch_id]['date'] = date('Y-m-d', strtotime($deviation->time_in));
                $this->deviations[$schedule_date->user_id][$schedule_date->date][$deviation->branch_id]['branch_code'] = $deviation->branch->branch_code;
                $this->deviations[$schedule_date->user_id][$schedule_date->date][$deviation->branch_id]['branch_name'] = $deviation->branch->branch_name;

                // actuals
                $this->deviations[$schedule_date->user_id][$schedule_date->date][$deviation->branch_id]['actuals'][$deviation->id] = [
                    'id' => $deviation->id,
                    'latitude' => $deviation->latitude,
                    'longitude' => $deviation->longitude,
                    'time_in' => $deviation->time_in,
                    'time_out' => $deviation->time_out,
                ];

            }

        }

        // get unscheduled visits
        $unscheduled_visits = BranchLogin::orderBy('user_id', 'ASC')->orderBy('time_in', 'ASC')
        ->whereNotExists(function($query) {
            $query->from('user_branch_schedules')
                ->select('*')
                ->where('user_branch_schedules.date','=',DB::raw('date(branch_logins.time_in)'))
                ->where('user_branch_schedules.user_id','=',DB::raw('branch_logins.user_id'))
                ->where('user_branch_schedules.branch_id','=',DB::raw('branch_logins.branch_id'));
        });
        // user
        if(!empty($this->user_id)) {
            $unscheduled_visits->where('user_id', $this->user_id);
        }
        // date from
        if(!empty($this->date_from)) {
            $unscheduled_visits->where(DB::raw('date(time_in)'), '>=', $this->date_from);
        }
        // date to
        if(!empty($this->date_to)) {
            $unscheduled_visits->where(DB::raw('date(time_in)'), '<=', $this->date_to);
        }

        $unscheduled_visits = $unscheduled_visits->paginate(10, ['*'], 'unschedule-page')->onEachSide(1);

        // Filter options
        $users = User::orderBy('firstname', 'ASC')
        ->whereHas('schedules')->get();

        return view('livewire.reports.mcp.report')->with([
            'schedule_dates' => $schedules_dates,
            'unscheduled_visits' => $unscheduled_visits,
            'users' => $users,
        ]);
    }
}
