<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;

use Illuminate\Support\Facades\DB;

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

    public function mount() {
        $this->date_from = date('Y-m').'-01';
    }

    public function render()
    {
        $query = UserBranchSchedule::query()
            ->selectRaw('COUNT(IF(status IS NULL, id, NULL)) as schedule_count')
            ->selectRaw('COUNT(IF(status IS NULL AND source = "deviation", id, NULL)) as deviation_count')
            ->selectRaw('COUNT(IF(status IS NULL AND source = "request", id, NULL)) as request_count')
            ->when(!empty($this->user_id), function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function ($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function ($query) {
                $query->where('date', '<=', $this->date_to);
            });

        $schedule_results = $query->first();

        $schedules_count = $schedule_results->schedule_count;
        $deviation_count = $schedule_results->deviation_count;
        $request_count = $schedule_results->request_count;

        $branch_logins = BranchLogin::query()
            ->selectRaw('DISTINCT user_id, branch_id, DATE(time_in) as date')
            ->when(!empty($this->user_id), function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function ($query) {
                $query->whereDate('time_in', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function ($query) {
                $query->whereDate('time_in', '<=', $this->date_to);
            })
            ->get();

        $visited_count = $branch_logins->filter(function ($branch_login) {
            return UserBranchSchedule::where('user_id', $branch_login->user_id)
                ->where('branch_id', $branch_login->branch_id)
                ->where('date', $branch_login->date)
                ->whereNull('status')
                ->exists();
        })->count();

        $unscheduled_count = $branch_logins->count() - $visited_count;
        $deviation_count += $unscheduled_count;

        return view('livewire.reports.mcp.header')->with([
            'schedules_count' => $schedules_count,
            'visited_count' => $visited_count,
            'deviation_count' => $deviation_count,
            'request_count' => $request_count
        ]);
    }

}
