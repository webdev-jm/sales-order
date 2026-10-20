<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BranchLogin;
use App\Models\ActivityPlan;
use App\Models\User;
use App\Models\WeeklyActivityReport;
use App\Models\Deviation;
use App\Services\Maps\Geo;
use App\Services\Maps\RouteSummary;
use App\Services\Maps\VisitMap;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index() {
        return view('pages.reports.index');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function mcpDashboard() {
        return view('pages.reports.mcp');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function sales_orders() {
        return view('pages.reports.orders');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function combinedReports() {

        return view('pages.reports.combined');
    }

    /**
     * @param  int|string  $user_id
     * @param  int|string  $year
     * @param  int|string  $month
     * @return \Illuminate\Http\Response
     */
    public function combinedReportPrint(int|string $user_id, int|string $year, int|string $month) {

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

        $pdf = PDF::loadview('pages.reports.combined-pdf', [
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

    /**
     * MCP MAP: each branch sign-in plotted against the branch's pinned address, with the scheduled branches
     * marked visited or missed.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function map(Request $request) {
        $date_from = trim((string) $request->input('date_from'));
        $date_to = trim((string) $request->input('date_to'));
        $user_id = trim((string) $request->input('user_id'));

        $restricted_ids = $this->restrictedUserIds();

        $report = null;
        if (!empty($date_from) || !empty($date_to) || !empty($user_id)) {
            $report = VisitMap::build($date_from, $date_to, $user_id, $restricted_ids);
        }

        $users = User::orderBy('firstname', 'ASC')
            ->whereHas('branch_logins')
            ->when(!is_null($restricted_ids), function($query) use($restricted_ids) {
                $query->whereIn('id', $restricted_ids);
            })
            ->get();

        return view('pages.reports.map')->with([
            'report' => $report,
            'statuses' => VisitMap::STATUSES,
            'on_site_m' => VisitMap::ON_SITE_M,
            'near_m' => VisitMap::NEAR_M,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'user_id' => $user_id,
            'users' => $users,
        ]);
    }

    /**
     * Store location records report: traces the GPS trail captured for each branch login as a route on the map,
     * with the places the user stayed and the distance covered, filterable by user and date.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function locations(Request $request) {
        $date_from = trim((string) $request->input('date_from'));
        $date_to = trim((string) $request->input('date_to'));
        $user_id = trim((string) $request->input('user_id'));

        $restricted_ids = $this->restrictedUserIds();

        $routes = [];
        if (!empty($date_from) || !empty($date_to) || !empty($user_id)) {
            $logins = BranchLogin::with(['user:id,firstname,lastname', 'branch:id,account_id,branch_code,branch_name', 'branch.account:id,short_name'])
                ->whereNotNull('location_trail')
                ->when(!empty($date_from), function ($query) use ($date_from) {
                    $query->where('time_in', '>=', $date_from.' 00:00:00');
                })
                ->when(!empty($date_to), function ($query) use ($date_to) {
                    $query->where('time_in', '<=', $date_to.' 23:59:59');
                })
                ->when(!empty($user_id), function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })
                ->when(!is_null($restricted_ids), function ($query) use ($restricted_ids) {
                    $query->whereIn('user_id', $restricted_ids);
                })
                ->orderBy('time_in', 'ASC')
                ->get();

            foreach ($logins as $login) {
                $summary = RouteSummary::for($login);
                if (count($summary['points']) < 1 && count($summary['coordinates']) < 2) {
                    continue;
                }

                $user_name = trim(($login->user->firstname ?? '').' '.($login->user->lastname ?? ''));
                $branch_name = trim(($login->branch->account->short_name ?? '').' '.($login->branch->branch_code ?? '').' '.($login->branch->branch_name ?? ''));

                $routes[] = [
                    'id'                  => $login->id,
                    'user_id'             => $login->user_id,
                    'user'                => $user_name,
                    'branch'              => $branch_name,
                    'time_in'             => (string) $login->time_in,
                    'time_out'            => (string) $login->time_out,
                    'start'               => Geo::isUsable($login->latitude, $login->longitude) ? ['lat' => (float) $login->latitude, 'lon' => (float) $login->longitude] : null,
                    'end'                 => (!is_null($login->time_out) && Geo::isUsable($login->time_out_latitude, $login->time_out_longitude)) ? ['lat' => (float) $login->time_out_latitude, 'lon' => (float) $login->time_out_longitude] : null,
                    'coordinates'         => $summary['coordinates'],
                    'points'              => array_map(fn ($point) => [
                        'lat'          => $point['latitude'],
                        'lon'          => $point['longitude'],
                        'accuracy'     => $point['accuracy'],
                        'recorded_at'  => $point['recorded_at'],
                        'last_seen_at' => $point['last_seen_at'],
                        'samples'      => $point['samples'],
                    ], $summary['points']),
                    'stops'               => $summary['stops'],
                    'distance_km'         => round($summary['distance_m'] / 1000, 2),
                    'duration_minutes'    => $summary['duration_minutes'],
                    'tracked_points'      => $summary['tracked_points'],
                    'vague_points'        => $summary['vague_points'],
                    'gaps'                => $summary['gaps'],
                    'longest_gap_minutes' => $summary['longest_gap_minutes'],
                ];
            }
        }

        $users = User::orderBy('firstname', 'ASC')
            ->whereHas('branch_logins', function ($query) {
                $query->whereNotNull('location_trail');
            })
            ->when(!is_null($restricted_ids), function ($query) use ($restricted_ids) {
                $query->whereIn('id', $restricted_ids);
            })
            ->get();

        return view('pages.reports.locations')->with([
            'routes'     => $routes,
            'totals'     => [
                'routes'      => count($routes),
                'users'       => count(array_unique(array_column($routes, 'user_id'))),
                'distance_km' => round(array_sum(array_column($routes, 'distance_km')), 2),
                'hours'       => round(array_sum(array_column($routes, 'duration_minutes')) / 60, 1),
                'stops'       => array_sum(array_map(fn ($route) => count($route['stops']), $routes)),
                'gaps'        => array_sum(array_column($routes, 'gaps')),
            ],
            'date_from'  => $date_from,
            'date_to'    => $date_to,
            'user_id'    => $user_id,
            'users'      => $users,
        ]);
    }

    /**
     * Subordinate user IDs to constrain report visibility to when the current
     * user carries the "report restricted" permission, or null when the user
     * may see everyone.
     *
     * @return array<int>|null
     */
    private function restrictedUserIds(): ?array
    {
        if (!auth()->user()->can('report restricted')) {
            return null;
        }

        $subordinate_ids = auth()->user()->getSubordinateIds();

        return empty($subordinate_ids) ? [] : array_merge(...array_values($subordinate_ids));
    }
}
