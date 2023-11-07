<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

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
    }

    public function getPercent($total, $count) {
        $percent = 0;
        if($total > 0 && $count > 0) {
            $percent = ($count / $total) * 100;
        }

        return number_format($percent, 2);
    }

    public function mount() {
        $this->date_from = date('Y-m').'-01';
    }

    public function render()
    {
        $schedule_data = DB::table('user_branch_schedules as ubs')
            ->select(
                DB::raw('COUNT(IF(status IS NULL, id, NULL)) as schedule_count'),
                DB::raw('COUNT(IF(status IS NULL AND source = "deviation", id, NULL)) as deviation_count'),
                DB::raw('COUNT(IF(status IS NULL AND source = "request", id, NULL)) as request_count')
            )
            ->whereNull('ubs.deleted_at')
            ->when(!empty($this->user_id), function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('date', '<=', $this->date_to);
            })
            ->first();

        $schedules_count = $schedule_data->schedule_count;
        $deviation_count = $schedule_data->deviation_count;

        $branch_logins = BranchLogin::select(DB::raw("distinct user_id, branch_id, date(time_in) as date"))
            ->when(!empty($this->user_id), function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where(DB::raw('DATE(time_in)'), '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where(DB::raw('DATE(time_in)'), '<=', $this->date_to);
            })
            ->get();

        $visited_count = 0;
        $unscheduled_count = 0;
        foreach($branch_logins as $branch_login) {
            $check = UserBranchSchedule::where('branch_id', $branch_login->branch_id)
                ->where('date', $branch_login->date)
                ->when(!empty($this->user_id), function($query) {
                    $query->where('user_id', $this->user_id);
                }, function($query) use($branch_login) {
                    $query->where('user_id', $branch_login->user_id);
                })
                ->first();

            if(!empty($check)) {
                $visited_count++;
            } else {
                $unscheduled_count++;
            }
        }

        $unscheduled_count += $deviation_count;

        $total_visit = BranchLogin::select(DB::raw("COUNT(DISTINCT user_id, branch_id, DATE(time_in)) as total"))
            ->when(!empty($this->user_id), function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where(DB::raw('DATE(time_in)'), '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where(DB::raw('DATE(time_in)'), '<=', $this->date_to);
            })
            ->first();
        $total_visit = $total_visit->total;
        
        // unvisited
        $unvisited_count = $schedules_count - $visited_count;

        $avg_minutes = BranchLogin::select(DB::raw('AVG(TIMESTAMPDIFF(minute, time_in, time_out)) as avg'))
            ->when(!empty($this->user_id), function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where(DB::raw('DATE(time_in)'), '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where(DB::raw('DATE(time_in)'), '<=', $this->date_to);
            })
            ->first();
        $this->avg_minutes = $avg_minutes->avg;

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
                'total' => $total_visit,
                'percent' => $this->getPercent($total_visit, $unscheduled_count),
                'count' => $unscheduled_count
            ),
        );

        return view('livewire.reports.mcp.percentage');
    }
}
