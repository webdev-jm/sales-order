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

        $schedules_query = UserBranchSchedule::when(!empty($this->user_id), function($query) {
                $query->where('user_id', $this->user_id);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('date', '<=', $this->date_to);
            });
        
        $schedules_count = $schedules_query->whereNull('status')->count();
        $reschedule_count = $schedules_query->where('status', 'for reschedule')->count();
        $delete_count = $schedules_query->where('status', 'for deletion')->count();

        $visited_count = 0;
        $unscheduled_count = 0;
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
        foreach($branch_logins as $branch_login) {
            $check = UserBranchSchedule::where('user_id', $branch_login->user_id)
            ->where('branch_id', $branch_login->branch_id)
            ->where('date', $branch_login->date)->first();

            if(!empty($check)) {
                $visited_count++;
            } else {
                $unscheduled_count++;
            }
        }

        return view('livewire.reports.mcp.header')->with([
            'schedules_count' => $schedules_count,
            'visited_count' => $visited_count,
            'reschedule_count' => $reschedule_count,
            'delete_count' => $delete_count,
            'unscheduled_count' => $unscheduled_count
        ]);
    }
}
