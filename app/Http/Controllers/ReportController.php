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
}
