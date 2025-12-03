<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;
use App\Models\ActivityPlan;
use App\Models\User;
use App\Models\WeeklyActivityReport;
use App\Models\Deviation;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index() {
        return view('reports.index');
    }

    public function mcpDashboard() {
        return view('reports.mcp');
    }

    public function sales_orders() {
        return view('reports.orders');
    }

    public function combinedReports() {

        return view('reports.combined');
    }

    public function combinedReportPrint($user_id, $year, $month) {

        $user = User::find($user_id);
        $date_string = $year.'-'.$month;

        // Activity Plan
        $activity_plan_status_arr = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'rejected' => 'danger',
            'approved' => 'success'
        ];

        $activity_plans = ActivityPlan::where('user_id', $user_id)
        ->where('year', $year)
        ->where('month', $month)
        ->where('status', '<>', 'draft')
        ->get();

        // Weekly Activity Report'
        $war_status_arr = [
            'draft' => 'secondary',
            'submitted' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        $weekly_activity_reports = WeeklyActivityReport::where('user_id', $user_id)
        ->where(function($query) use($date_string) {
            $query->where('date_from', 'like', $date_string.'%')
            ->orWhere('date_to', 'like', $date_string.'%');
        })
        ->where('status', '<>', 'draft')
        ->get();

        // Deviations
        $deviation_status_arr = [
            'submitted' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        $deviations = Deviation::where('user_id', $user_id)
        ->where('date', 'like', $date_string.'%')
        ->get();

        $pdf = PDF::loadView('reports.combined-pdf', [
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'activity_plans' => $activity_plans,
            'activity_plan_status_arr' => $activity_plan_status_arr,
            'war_status_arr' => $war_status_arr,
            'weekly_activity_reports' => $weekly_activity_reports,
            'deviation_status_arr' => $deviation_status_arr,
            'deviations' => $deviations
        ]);

        return $pdf->stream('mcp reports -'.date('F Y', strtotime($year.'-'.$month.'-01')).'-'.time().'.pdf');
    }

    public function map(Request $request) {
        $date_from = trim($request->get('date_from'));
        $date_to = trim($request->get('date_to'));
        $user_id = trim($request->get('user_id'));

        $chart_data = [];
        $branch_data = [];
        if(!empty($date_from) || !empty($date_to) || !empty($user_id)) {

            $results = DB::table('branch_logins as bl')
                ->select(
                    DB::raw('CONCAT(u.firstname, " ", u.lastname) as name'),
                    'bl.id',
                    'bl.latitude',
                    'bl.longitude',
                    'bl.time_in',
                    'bl.time_out',
                    'bl.accuracy',
                    DB::raw('CONCAT(a.short_name, " ", b.branch_code, " ", b.branch_name) as branch'),
                    'bl.branch_id'
                )
                ->join('users as u', 'u.id', '=', 'bl.user_id')
                ->join('branches as b', 'b.id', '=', 'bl.branch_id')
                ->join('accounts as a', 'a.id', '=', 'b.account_id')
                ->when(!empty($date_from), function($query) use($date_from) {
                    $query->where(DB::raw('DATE(time_in)'), '>=', $date_from);
                })
                ->when(!empty($date_to), function($query) use($date_to) {
                    $query->where(DB::raw('DATE(time_in)'), '<=', $date_to);
                })
                ->when(!empty($user_id), function($query) use($user_id) {
                    $query->where('u.id', $user_id);
                })
                ->get();

            foreach($results as $result) {
                // Actual login marker
                $chart_data[] = [
                    'lat' => (float)$result->latitude,
                    'lon' => (float)$result->longitude,
                    'z' => (float)str_replace('m', '', $result->accuracy),
                    'time_in' => $result->time_in,
                    'time_out' => $result->time_out,
                    'accuracy' => $result->accuracy,
                    'branch' => $result->branch,
                    'user' => $result->name,
                    'color' => '#ff1100ff', // Blue for actual login
                ];

                // Branch address marker
                // $branch_address = BranchAddress::where('branch_id', $result->branch_id)->first();
                // if(!empty($branch_address)) {
                //     $chart_data[] = [
                //         'lat' => (float)$branch_address->latitude,
                //         'lon' => (float)$branch_address->longitude,
                //         'z' => 10,
                //         'branch' => $result->branch,
                //         'color' => '#f02c2cff', // Green for branch address
                //     ];
                // }
            }

            // get user branch schedules
            $schedules = UserBranchSchedule::with('branch')
                ->when(!empty($date_from), function($query) use($date_from) {
                    $query->where('date', '>=', $date_from);
                })
                ->when(!empty($date_to), function($query) use($date_to) {
                    $query->where('date', '<=', $date_to);
                })
                ->when(!empty($user_id), function($query) use($user_id) {
                    $query->where('user_id', $user_id);
                })
                ->get();

            foreach($schedules as $schedule) {
                $branch_address = $schedule->branch->addresses->first();
                if(!empty($branch_address)) {
                    $branch_data[] = [
                        'lat' => (float)$branch_address->latitude,
                        'lon' => (float)$branch_address->longitude,
                        'name' => $schedule->branch->branch_code.' '.$schedule->branch->branch_name,
                        'user' => $schedule->user->fullName(),
                        'schedule_date' => $schedule->date,
                        'objective' => $schedule->objective,
                        'source' => $schedule->source,
                    ];
                }
            }
        }

        $users = User::orderBy('firstname', 'ASC')
            ->whereHas('branch_logins')
            ->get();

        return view('reports.map')->with([
            'chart_data' => $chart_data,
            'branch_data' => $branch_data,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'user_id' => $user_id,
            'users' => $users,
        ]);
    }
}
