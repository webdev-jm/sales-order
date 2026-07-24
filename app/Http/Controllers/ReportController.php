<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\UserBranchSchedule;
use App\Models\BranchLogin;
use App\Models\BranchAddress;
use App\Models\ActivityPlan;
use App\Models\User;
use App\Models\WeeklyActivityReport;
use App\Models\Deviation;

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
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function map(Request $request) {
        $date_from = trim($request->input('date_from'));
        $date_to = trim($request->input('date_to'));
        $user_id = trim($request->input('user_id'));

        $restricted_ids = $this->restrictedUserIds();

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
                ->when(!is_null($restricted_ids), function($query) use($restricted_ids) {
                    $query->whereIn('u.id', $restricted_ids);
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
                    'color' => '#ff1100ff', // Red for actual login
                ];

                // Branch address marker
                // $branch_address = BranchAddress::where('branch_id', $result->branch_id)->first();
                // if(!empty($branch_address)) {
                //     $chart_data[] = [
                //         'lat' => (float)$branch_address->latitude,
                //         'lon' => (float)$branch_address->longitude,
                //         'z' => 10,
                //         'branch' => $result->branch,
                //         'color' => '#f02c2cff', // Red for branch address
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
                ->when(!is_null($restricted_ids), function($query) use($restricted_ids) {
                    $query->whereIn('user_id', $restricted_ids);
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
            ->when(!is_null($restricted_ids), function($query) use($restricted_ids) {
                $query->whereIn('id', $restricted_ids);
            })
            ->get();

        return view('pages.reports.map')->with([
            'chart_data' => $chart_data,
            'branch_data' => $branch_data,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'user_id' => $user_id,
            'users' => $users,
        ]);
    }

    /**
     * Store location records report: traces the per-minute GPS trail captured
     * for each branch login as a route on the map, filterable by user and date.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function locations(Request $request) {
        $date_from = trim($request->input('date_from'));
        $date_to = trim($request->input('date_to'));
        $user_id = trim($request->input('user_id'));

        $restricted_ids = $this->restrictedUserIds();

        $route_data = [];
        $point_data = [];
        $start_data = [];
        $end_data = [];
        if (!empty($date_from) || !empty($date_to) || !empty($user_id)) {
            $logins = BranchLogin::with(['user', 'branch', 'branch.account'])
                ->whereNotNull('location_trail')
                ->when(!empty($date_from), function ($query) use ($date_from) {
                    $query->where(DB::raw('DATE(time_in)'), '>=', $date_from);
                })
                ->when(!empty($date_to), function ($query) use ($date_to) {
                    $query->where(DB::raw('DATE(time_in)'), '<=', $date_to);
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
                $trail = $login->location_trail ?? [];
                if (count($trail) < 1) {
                    continue;
                }

                $user_name = trim(($login->user->firstname ?? '').' '.($login->user->lastname ?? ''));
                $branch_name = trim(($login->branch->account->short_name ?? '').' '.($login->branch->branch_code ?? '').' '.($login->branch->branch_name ?? ''));

                // The full route runs sign-in -> per-minute trail -> sign-out so
                // the connecting line always begins and ends where the user did.
                $coordinates = [];

                $coordinates[] = [(float) $login->longitude, (float) $login->latitude];
                $start_data[] = [
                    'lat'     => (float) $login->latitude,
                    'lon'     => (float) $login->longitude,
                    'user'    => $user_name,
                    'branch'  => $branch_name,
                    'time_in' => (string) $login->time_in,
                ];

                foreach ($trail as $index => $point) {
                    $coordinates[] = [(float) $point['longitude'], (float) $point['latitude']];

                    $point_data[] = [
                        'lat'         => (float) $point['latitude'],
                        'lon'         => (float) $point['longitude'],
                        'user'        => $user_name,
                        'branch'      => $branch_name,
                        'accuracy'    => $point['accuracy'] ?? null,
                        'recorded_at' => $point['recorded_at'] ?? null,
                        'sequence'    => $index + 1,
                    ];
                }

                if (!is_null($login->time_out_latitude) && !is_null($login->time_out_longitude)) {
                    $coordinates[] = [(float) $login->time_out_longitude, (float) $login->time_out_latitude];
                    $end_data[] = [
                        'lat'      => (float) $login->time_out_latitude,
                        'lon'      => (float) $login->time_out_longitude,
                        'user'     => $user_name,
                        'branch'   => $branch_name,
                        'time_out' => (string) $login->time_out,
                    ];
                }

                $route_data[] = [
                    'name'     => $user_name.' - '.$branch_name,
                    'user'     => $user_name,
                    'branch'   => $branch_name,
                    'time_in'  => (string) $login->time_in,
                    'time_out' => (string) $login->time_out,
                    'points'   => count($trail),
                    'geometry' => [
                        'type'        => 'LineString',
                        'coordinates' => $coordinates,
                    ],
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
            'route_data' => $route_data,
            'point_data' => $point_data,
            'start_data' => $start_data,
            'end_data'   => $end_data,
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
