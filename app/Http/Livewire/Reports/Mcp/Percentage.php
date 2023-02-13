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

    public function render()
    {
        // schedules count
        $schedules_count = UserBranchSchedule::whereNull('status');
        // user
        if(!empty($this->user_id)) {
            $schedules_count->where('user_id', $this->user_id);
        }
        // date from
        if(!empty($this->date_from)) {
            $schedules_count->where(DB::raw('date'), '>=', $this->date_from);
        }
        // date to
        if(!empty($this->date_to)) {
            $schedules_count->where(DB::raw('date'), '<=', $this->date_to);
        }
        $schedules_count = $schedules_count->count();

        $branch_logins = BranchLogin::select(DB::raw("distinct user_id, branch_id, date(time_in) as date"));
        // user
        if(!empty($this->user_id)) {
            $branch_logins->where('user_id', $this->user_id);
        }
        // date from
        if(!empty($this->date_from)) {
            $branch_logins->where(DB::raw('date(time_in)', '>=', $this->date_from));
        }
        // date to
        if(!empty($this->date_to)) {
            $branch_logins->where(DB::raw('date(time_in)'), '<=', $this->date_to);
        }
        $branch_logins = $branch_logins->get();

        $visited_count = 0;
        $unscheduled_count = 0;
        foreach($branch_logins as $branch_login) {
            $check = UserBranchSchedule::where('branch_id', $branch_login->branch_id)
            ->where('date', $branch_login->date);
            // user
            if(!empty($this->user_id)) {
                $check->where('user_id', $this->user_id);
            } else {
                $check->where('user_id', $branch_login->user_id);
            }
            $check = $check->first();

            if(!empty($check)) {
                $visited_count++;
            } else {
                $unscheduled_count++;
            }
        }

        // total visit
        $total_visit = BranchLogin::select(DB::raw("count(distinct user_id, branch_id, date(time_in)) as total"));
        // USER
        if(!empty($this->user_id)) {
            $total_visit->where('user_id', $this->user_id);
        }
        // DATE FROM
        if(!empty($this->date_from)) {
            $total_visit->where(DB::raw('date(time_in)', '>=', $this->date_from));
        }
        // DATE TO
        if(!empty($this->date_to)) {
            $total_visit->where(DB::raw('date(time_in)', '<=', $this->date_to));
        }
        $total_visit = $total_visit->first();
        
        // unvisited
        $unvisited_count = $schedules_count - $visited_count;
        // total minutes
        $avg_minutes = BranchLogin::select(DB::raw("AVG(TIMESTAMPDIFF(minute ,time_in, time_out)) as avg"));
        // user
        if(!empty($this->user_id)) {
            $avg_minutes->where('user_id', $this->user_id);
        }
        // date from
        if(!empty($this->date_from)) {
            $avg_minutes->where(DB::raw('date(time_in)', '>=', $this->date_from));
        }
        // date to
        if(!empty($this->date_to)) {
            $avg_minutes->where(DB::raw('date(time_in)', '<=', $this->date_to));
        }
        $avg_minutes = $avg_minutes->get();
        $this->avg_minutes = $avg_minutes[0]['avg'];

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
                'total' => $total_visit->total,
                'percent' => $this->getPercent($total_visit->total, $unscheduled_count),
                'count' => $unscheduled_count
            ),
        );

        return view('livewire.reports.mcp.percentage');
    }
}
