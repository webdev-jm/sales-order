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

        // $visited_count = DB::table("branch_logins")
        // ->select(DB::raw("count(distinct user_id, branch_id, date(time_out)) as total"))
        // ->first();

        // check schedule
        // $visited_count = 0;
        // $unscheduled_count = 0;
        // $schedules = UserBranchSchedule::whereNull('status')->get();
        // foreach($schedules as $schedule) {
        //     $check = BranchLogin::where('user_id', $schedule->user_id)
        //     ->where('branch_id', $schedule->branch_id)
        //     ->where('time_in', 'like', $schedule->date.'%')->first();

        //     if(!empty($check)) {
        //         $visited_count++;
        //     } else {

        //     }
        // }

        $visited_count = 0;
        $unscheduled_count = 0;
        $branch_logins = BranchLogin::select(DB::raw("distinct user_id, branch_id, date(time_in) as date"))->get();
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
        
        return view('reports.index')->with([
            'schedules_count' => $schedules_count,
            'visited_count' => $visited_count,
            'reschedule_count' => $reschedule_count,
            'delete_count' => $delete_count,
            'unscheduled_count' => $unscheduled_count
        ]);
    }

    public function sales_orders() {

        return view('reports.orders');
    }
}
