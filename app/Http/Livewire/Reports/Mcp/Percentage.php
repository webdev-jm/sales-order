<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

use App\Models\BranchLogin;

class Percentage extends Component
{

    public $data, $avg_minutes;

    public function getPercent($total, $count) {
        $percent = 0;
        if($total > 0 && $count > 0) {
            $percent = ($count / $total) * 100;
        }

        return number_format($percent, 2);
    }

    public function mount($schedules_count, $visited_count, $unscheduled_count) {

        // total visit
        $total_visit = DB::table("branch_logins")
        ->select(DB::raw("count(distinct user_id, branch_id, date(time_out)) as total"))
        ->first();
        // unvisited
        $unvisited_count = $schedules_count - $visited_count;
        // total minutes
        $this->avg_minutes = BranchLogin::select(DB::raw("AVG(TIMESTAMPDIFF(minute ,time_in, time_out)) as avg"))->first();

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
    }

    public function render()
    {
        return view('livewire.reports.mcp.percentage');
    }
}
