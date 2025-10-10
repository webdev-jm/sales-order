<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;
use App\Models\User;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Exports\MCPReportExport;
use Maatwebsite\Excel\Facades\Excel;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

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

    public function showScheduleDetail($schedule_id, $type) {
        $this->emit('showScheduleDetail', $schedule_id, $type);
        $this->dispatchBrowserEvent('showScheduleDetail');
    }

    public function updatedUserId() {
        $this->reset(['schedules', 'actuals', 'deviations']);
    }

    public function updatedDateFrom() {
        $this->reset(['schedules', 'actuals', 'deviations']);
    }

    public function updatedDateTo() {
        $this->reset(['schedules', 'actuals', 'deviations']);
    }

    private function getDates($start_date, $end_date) {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        $all_dates = array();
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            // check if theres data
            $check = User::where(function($query) use($date) {
                    $query->whereHas('schedules', function($query) use($date) {
                        $query->whereNull('status')
                            ->where('date', $date->toDateString());
                    })
                    ->orWhereHas('branch_logins', function($query) use($date) {
                        $query->where(DB::raw('date(time_in)'), $date->toDateString());
                    });
                })
                ->when(!empty($this->user_id), function($query) {
                    $query->where('id', $this->user_id);
                })
                ->when(auth()->user()->hasRole('GSM'), function($query) {
                    $subordinateIds = auth()->user()->getSubordinateIds();
                    $subordinateIds = array_merge(...array_values($subordinateIds));
                    $query->whereIn('id', $subordinateIds);
                })
                ->first();

            if(!empty($check)) {
                $all_dates[] = $date->toDateString();
            }
        }

        return $all_dates;
    }

    private function paginateArray($data, $perPage) {
        $currentPage = $this->page ?: 1;
        $items = collect($data);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $items->slice($offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'onEachSide' => 1]
        );

        return $paginator;
    }

    public function mount() {
    }

    public function render()
    {
        $date_arr = $this->getDates($this->date_from, $this->date_to);

        $paginatedData = NULL;
        if(!empty($date_arr)) {
            $paginatedData = $this->paginateArray($date_arr, 3);
        }

        if(!empty($date_arr)) {
            foreach($date_arr as $key => $date) {
                $users_arr = User::where(function($query) use($date) {
                        $query->whereHas('schedules', function($query) use($date) {
                            $query->whereNull('status')
                                ->where('date', $date);
                        })
                        ->orWhereHas('branch_logins', function($query) use($date) {
                            $query->where(DB::raw('date(time_in)'), $date);
                        });
                    })
                    ->when(!empty($this->user_id), function($query) {
                        $query->where('id', $this->user_id);
                    })
                    ->when(auth()->user()->hasRole('GSM'), function($query) {
                        $subordinateIds = auth()->user()->getSubordinateIds();
                        $subordinateIds = array_merge(...array_values($subordinateIds));
                        $query->whereIn('id', $subordinateIds);
                    })
                    ->get();

                if(!empty($users_arr->count())) {
                    foreach($users_arr as $user) {
                        // get schedules
                        $schedules_data = UserBranchSchedule::with('branch', 'branch.account')
                            ->where('date', $date)
                            ->whereNull('status')
                            ->where(function($query) {
                                $query->where('source', 'activity-plan')
                                    ->orWhere('source', 'request')
                                    ->orWhere('source', 'deviation');
                            })
                            ->where('user_id', $user->id)
                            ->get();

                        $this->schedules[$date][$user->id]['schedules'] = $schedules_data;
                        $this->schedules[$date][$user->id]['user'] = $user;

                        foreach($schedules_data as $schedule) {
                            // get actual branch sign-in
                            $branch_logins = BranchLogin::where('user_id', $schedule->user_id)
                                ->where('branch_id', $schedule->branch_id)
                                ->where(DB::raw('date(time_in)'), $schedule->date)
                                ->get();

                            if(!empty($branch_logins)) {
                                $this->actuals[$schedule->id] = $branch_logins;
                            }
                        }

                        // get deviated schedules
                        $deviations_data = BranchLogin::orderBy('time_in', 'ASC')
                            ->where('user_id', $user->id)
                            ->where('time_in', 'like', $date.'%')
                            ->whereNotIn('branch_id', $schedules_data->pluck('branch_id'))
                            ->get();

                        foreach($deviations_data as $key => $deviation) {

                            $this->deviations[$date][$user->id][$deviation->branch_id]['id'] = $deviation->id;
                            $this->deviations[$date][$user->id][$deviation->branch_id]['date'] = date('Y-m-d', strtotime($deviation->time_in));
                            $this->deviations[$date][$user->id][$deviation->branch_id]['branch_code'] = $deviation->branch->branch_code;
                            $this->deviations[$date][$user->id][$deviation->branch_id]['branch_name'] = $deviation->branch->branch_name;
                            $this->deviations[$date][$user->id][$deviation->branch_id]['account_name'] = $deviation->branch->account->short_name;
                            $this->deviations[$date][$user->id][$deviation->branch_id]['source'] = $deviation->source;

                            // actuals
                            $this->deviations[$date][$user->id][$deviation->branch_id]['actuals'][$deviation->id] = [
                                'id' => $deviation->id,
                                'latitude' => $deviation->latitude,
                                'longitude' => $deviation->longitude,
                                'time_in' => $deviation->time_in,
                                'time_out' => $deviation->time_out,
                            ];

                        }
                    }
                } else {
                    $this->schedules[$date] = [];
                }

            }
        }

        // Filter options
        $users = User::orderBy('firstname', 'ASC')
            ->whereHas('schedules')
            ->when(auth()->user()->hasRole('GSM'), function($query) {
                $subordinateIds = auth()->user()->getSubordinateIds();
                $subordinateIds = array_merge(...array_values($subordinateIds));
                $query->whereIn('id', $subordinateIds);
            })
            ->get();

        return view('livewire.reports.mcp.report')->with([
            'paginatedData' => $paginatedData,
            'users' => $users,
        ]);
    }
}
