<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use Carbon\Carbon;

class Percentage extends Component
{

    public $data, $avg_minutes;
    public $user_id, $date_from, $date_to;

    protected $listeners = [
        'setFilter' => 'setFilter'
    ];

    public function setFilter($user_id, $date_from, $date_to) {
        $this->user_id = $user_id;
        $this->date_from = $date_from;
        $this->date_to = $date_to;

        $this->reset('avg_minutes');
    }

    public function getPercent($total, $count) {
        $percent = 0;
        if($total > 0 && $count > 0) {
            $percent = ($count / $total) * 100;
        }

        return number_format($percent, 2);
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
        $visited_count = 0;
        $total_visit_count = 0;
        $unscheduled_count = 0;
        // foreach($date_arr as $date) {
        //     $schedule_data = DB::table('user_branch_schedules as ubs')
        //     ->select(
        //         DB::raw('COUNT(IF(status IS NULL, id, NULL)) as schedule_count'),
        //         DB::raw('COUNT(IF(status IS NULL AND source = "deviation", id, NULL)) as deviation_count'),
        //         DB::raw('COUNT(IF(status IS NULL AND source = "request", id, NULL)) as request_count')
        //     )
        //     ->whereNull('ubs.deleted_at')
        //     ->where('date', $date)
        //     ->when(!empty($this->user_id), function($query) {
        //         $query->where('user_id', $this->user_id);
        //     })
        //     ->first();

        //     $schedules_count += $schedule_data->schedule_count;
        //     $deviation_count += $schedule_data->deviation_count;

        //     $branch_logins = BranchLogin::select(DB::raw("distinct user_id, branch_id, date(time_in) as date"))
        //         ->when(!empty($this->user_id), function($query) {
        //             $query->where('user_id', $this->user_id);
        //         })
        //         ->where(DB::raw('DATE(time_in)'), $date)
        //         ->get();

        //     foreach($branch_logins as $branch_login) {
        //         $check = UserBranchSchedule::where('branch_id', $branch_login->branch_id)
        //             ->where('date', $branch_login->date)
        //             ->when(!empty($this->user_id), function($query) {
        //                 $query->where('user_id', $this->user_id);
        //             }, function($query) use($branch_login) {
        //                 $query->where('user_id', $branch_login->user_id);
        //             })
        //             ->first();

        //         if(!empty($check)) {
        //             $visited_count++;
        //         } else {
        //             $unscheduled_count++;
        //         }
        //     }

        //     $unscheduled_count += $deviation_count;

        //     $total_visit = BranchLogin::select(DB::raw("COUNT(DISTINCT user_id, branch_id, DATE(time_in)) as total"))
        //         ->when(!empty($this->user_id), function($query) {
        //             $query->where('user_id', $this->user_id);
        //         })
        //         ->where(DB::raw('DATE(time_in)'), $date)
        //         ->first();

        //     $total_visit_count += $total_visit->total;
            
        //     // unvisited
        //     $unvisited_count += $schedules_count - $visited_count;

        //     $avg_minutes = BranchLogin::select(DB::raw('AVG(TIMESTAMPDIFF(minute, time_in, time_out)) as avg'))
        //         ->when(!empty($this->user_id), function($query) {
        //             $query->where('user_id', $this->user_id);
        //         })
        //         ->where(DB::raw('date(time_in)'), $date)
        //         ->first();
        //     $this->avg_minutes += $avg_minutes->avg;
        // }

        if(!empty($date_arr)) {
            $schedule_data = UserBranchSchedule::whereIn('date', $date_arr)
                ->when(!empty($this->user_id), function ($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->get();
        
            $schedules_count = $schedule_data->filter(function($schedule) {
                return $schedule->status == NULL;
            })->count();
        
            $branch_logins = BranchLogin::select(
                    DB::raw("DISTINCT user_id, branch_id, DATE(time_in) as date")
                )
                ->when(!empty($this->user_id), function ($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->whereIn(DB::raw('DATE(time_in)'), $date_arr)
                ->get();
                
            $visited = $branch_logins->filter(function($branch_login) use($schedule_data) {
                return !empty($schedule_data->where('user_id', $branch_login->user_id)
                    ->where('branch_id', $branch_login->branch_id)
                    ->where('date', $branch_login->date)
                    ->whereNull('status')
                    ->first());
            })->count();
                
            $visited_count = $visited;
        
            $unscheduled_count = $branch_logins->count() - $visited;
            $total_visit_count = $branch_logins->count();

            $this->avg_minutes = BranchLogin::select(DB::raw('AVG(TIMESTAMPDIFF(minute, time_in, time_out)) as avg'))
                ->when(!empty($this->user_id), function ($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->whereIn(DB::raw('date(time_in)'), $date_arr)
                ->value('avg');
        }

        $unvisited_count = $schedules_count - $visited_count;

        $this->data = array(
            'schedule visited' => array(
                'total' => $schedules_count,
                'percent' => $this->getPercent($schedules_count, $visited_count),
                'count' => $visited_count
            ),
            'schedule unvisited' => array(
                'total' => $schedules_count,
                'percent' => $this->getPercent($schedules_count, $unvisited_count),
                'count' => $unvisited_count
            ),
            'unscheduled visits' => array(
                'total' => $total_visit_count,
                'percent' => $this->getPercent($total_visit_count, $unscheduled_count),
                'count' => $unscheduled_count
            ),
        );

        return view('livewire.reports.mcp.percentage');
    }
}
