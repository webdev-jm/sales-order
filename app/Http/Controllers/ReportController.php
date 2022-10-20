<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;

class ReportController extends Controller
{
    public function index() {

        $schedules_count = 0;
        $visited_count = 0;
        $reschedule_count = 0;
        $delete_count = 0;

        $schedules_count = UserBranchSchedule::whereNull('status')->count();
        $reschedule_count = UserBranchSchedule::where('status', 'for reschedule')->count();
        $delete_count = UserBranchSchedule::where('status', 'for deletion')->count();

        $visited_count = DB::table("branch_logins")
        ->select(DB::raw("count(distinct user_id, branch_id, date(time_out)) as total"))
        ->first();
        
        
        return view('reports.index')->with([
            'schedules_count' => $schedules_count,
            'visited_count' => $visited_count,
            'reschedule_count' => $reschedule_count,
            'delete_count' => $delete_count
        ]);
    }
}
