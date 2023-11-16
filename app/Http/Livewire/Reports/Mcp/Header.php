<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class Header extends Component
{
    public $user_id, $date_from, $date_to;

    protected $listeners = [
        'setFilter' => 'setFilter'
    ];

    public function setFilter($user_id, $date_from, $date_to) {
        $this->user_id = $user_id;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    private function getDates($start_date, $end_date) {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        $all_dates = array();
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $all_dates[] = $date->toDateString();
        }

        return $all_dates;
    }

    public function mount() {
    }

    public function render()
    {
        $date_arr = $this->getDates($this->date_from, $this->date_to);

        $schedules_count = 0;
        $deviation_count = 0;
        $request_count = 0;
        $unscheduled_count = 0;
        $deviation_count = 0;
        $visited_count = 0;
        if(!empty($date_arr)) {
            $results = UserBranchSchedule::when(!empty($this->user_id), function($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->whereIn('date', $date_arr)
                ->get();

            $schedules_count = $results->filter(function($schedule) {
                return $schedule->status == NULL;
            })->count();

            $deviation_count = $results->filter(function($schedule) {
                return $schedule->status == NULL && $schedule->source == 'deviation';
            })->count();

            $request_count = $results->filter(function($schedule) {
                return $schedule->status == NULL && $schedule->source == 'request';
            })->count();

            $branch_logins = BranchLogin::query()
                ->selectRaw('DISTINCT user_id, branch_id, DATE(time_in) as date')
                ->when(!empty($this->user_id), function ($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->whereIn(DB::raw('date(time_in)'), $date_arr)
                ->get();

            $visited_count = $branch_logins->filter(function($branch_login) use($results) {
                    return !empty($results->where('user_id', $branch_login->user_id)
                        ->where('branch_id',$branch_login->branch_id)
                        ->where('date', $branch_login->date)
                        ->whereNull('status')
                        ->first());
                })->count();

            $unscheduled_count = $branch_logins->count() - $visited_count;
            $deviation_count = $unscheduled_count;
        }

        return view('livewire.reports.mcp.header')->with([
            'schedules_count' => $schedules_count,
            'visited_count' => $visited_count,
            'deviation_count' => $deviation_count,
            'request_count' => $request_count
        ]);
    }

}
