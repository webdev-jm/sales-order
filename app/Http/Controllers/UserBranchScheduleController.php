<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Account;
use App\Models\UserBranchSchedule;
use App\Models\OrganizationStructure;
use App\Models\Deviation;
use App\Models\BranchLogin;
use App\Http\Requests\StoreUserBranchScheduleRequest;
use App\Http\Requests\UpdateUserBranchScheduleRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScheduleImport;

use App\Http\Traits\GlobalTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserBranchScheduleController extends Controller
{
    use GlobalTrait;

    public function index(Request $request): \Illuminate\View\View
    {
        $date_from = date('Y-m', strtotime('last month')) . '-01';
        $date_to = date('Y-m-d', strtotime('last day of next month'));

        $user_id = trim((string) $request->input('user_id'));
        $account_id = trim((string) $request->input('account_id'));

        $colors = [
            'schedule'   => '#25b8b5',
            'reschedule' => '#f37206',
            'delete'     => '#c90518',
            'request'    => '#32a852',
            'deviation'  => '#0e16ad',
        ];

        $subordinate_ids = collect(auth()->user()->getSubordinateIds())->flatten()->toArray();

        $is_privileged = auth()->user()->hasRole('superadmin')
            || auth()->user()->hasRole('admin')
            || auth()->user()->hasRole('sales');

        if ($is_privileged) {
            $schedule_data = (!empty($user_id) || !empty($account_id))
                ? $this->buildScheduleData($date_from, $date_to, $user_id, $account_id, $colors)
                : [];

            // Resolve all schedule users in one query instead of findOrFail per row
            $user_ids = UserBranchSchedule::select('user_id')->distinct()
                ->whereBetween('date', [$date_from, $date_to])
                ->pluck('user_id');

            $users_arr = ['' => 'select'] + User::whereIn('id', $user_ids)
                ->get()
                ->mapWithKeys(fn(User $u) => [$u->id => $u->fullName()])
                ->all();
        } else {
            if (empty($user_id)) {
                $user_id = (string) auth()->user()->id;
            }

            $schedule_data = $this->buildScheduleData($date_from, $date_to, $user_id, $account_id, $colors);

            $users_arr = [auth()->user()->id => auth()->user()->fullName()];

            if (!empty($subordinate_ids)) {
                $subordinate_users = User::whereIn('id', $subordinate_ids)
                    ->get()
                    ->mapWithKeys(fn(User $u) => [$u->id => $u->fullName()])
                    ->all();

                $users_arr = $users_arr + $subordinate_users;
            }
        }

        $branch_ids = UserBranchSchedule::select('branch_id')->distinct()
            ->whereNull('status')
            ->whereBetween('date', [$date_from, $date_to])
            ->pluck('branch_id');

        $accounts_arr = ['' => 'select'] + Account::orderBy('account_code')
            ->whereHas('branches', fn($q) => $q->whereIn('id', $branch_ids))
            ->get()
            ->mapWithKeys(fn(Account $a) => [$a->id => $a->account_code . ' - ' . $a->short_name])
            ->all();

        return view('schedules.index')->with([
            'user_id'       => $user_id,
            'account_id'    => $account_id,
            'users'         => $users_arr,
            'accounts'      => $accounts_arr,
            'schedule_data' => $schedule_data,
        ]);
    }

    public function list(Request $request): \Illuminate\View\View
    {
        $search = trim($request->input('search'));

        $schedules = UserBranchSchedule::orderBy('updated_at', 'DESC')
            ->where(function ($query) {
                $query->whereNotNull('status')
                    ->orWhereHas('approvals');
            })
            ->paginate(10)->onEachSide(1);

        return view('schedules.list')->with([
            'search'    => $search,
            'schedules' => $schedules,
        ]);
    }

    public function deviations(Request $request): \Illuminate\View\View
    {
        $search = trim($request->input('search'));

        $status_arr = [
            'submitted' => 'warning',
            'approved'  => 'success',
            'rejected'  => 'danger',
        ];

        $settings = $this->getSettings();

        $deviations = Deviation::DeviationSearch($search, $settings->data_per_page);

        return view('schedules.deviations')->with([
            'search'     => $search,
            'deviations' => $deviations,
            'status_arr' => $status_arr,
        ]);
    }

    public function store(StoreUserBranchScheduleRequest $request): \Illuminate\Http\RedirectResponse
    {
        UserBranchSchedule::create([
            'user_id'   => $request->user_id,
            'branch_id' => $request->branch_id,
            'date'      => $request->date,
            'source'    => 'create',
        ]);

        return back()->with(['message_success' => 'Schedule was created']);
    }

    public function upload(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['upload_file' => ['mimes:xlsx']]);

        Excel::import(new ScheduleImport, $request->upload_file);

        activity('upload')->log(':causer.firstname :causer.lastname has uploaded schedules');

        return back()->with(['message_success' => 'Schedule has been uploaded.']);
    }

    public function printDeviationForm(int $id): \Illuminate\Http\Response
    {
        $deviation = Deviation::findOrFail($id);

        // Single query instead of two separate where('type', ...) calls
        $schedules_by_type = $deviation->schedules()->get()->groupBy('type');

        $pdf = PDF::loadView('schedules.deviation-pdf', [
            'deviation'          => $deviation,
            'original_schedules' => $schedules_by_type->get('original', collect()),
            'new_schedules'      => $schedules_by_type->get('new', collect()),
        ]);

        return $pdf->stream('deviation-form-' . $deviation->date . '-' . time() . '.pdf');
    }

    /**
     * Build calendar event data for schedules, schedule requests, and deviations.
     *
     * Eliminates the date-first N+1 pattern by fetching all records at once with
     * eager loading, and pre-fetches BranchLogins to avoid per-schedule queries.
     */
    private function buildScheduleData(
        string $date_from,
        string $date_to,
        string $user_id,
        string $account_id,
        array $colors
    ): array {
        $schedule_data = [];

        // Pre-fetch all branch logins for this user/range to avoid a query per schedule
        $branch_logins_by_key = collect();
        if (!empty($user_id)) {
            $branch_logins_by_key = BranchLogin::where('user_id', $user_id)
                ->whereRaw('DATE(time_in) BETWEEN ? AND ?', [$date_from, $date_to])
                ->get()
                ->groupBy(fn(BranchLogin $bl) => $bl->branch_id . '_' . Carbon::parse($bl->time_in)->toDateString());
        }

        $schedule_types = [
            ['status' => null,               'type' => 'schedule'],
            ['status' => 'schedule request', 'type' => 'request'],
        ];

        foreach ($schedule_types as ['status' => $status, 'type' => $type]) {
            // Single query per type with eager loading — replaces the date-loop N+1 pattern
            $schedules = UserBranchSchedule::with('branch.account')
                ->when($status === null, fn($q) => $q->whereNull('status'))
                ->when($status !== null, fn($q) => $q->where('status', $status))
                ->whereBetween('date', [$date_from, $date_to])
                ->when(!empty($user_id), fn($q) => $q->where('user_id', $user_id))
                ->when(!empty($account_id), fn($q) => $q->whereHas('branch', fn($q) => $q->where('account_id', $account_id)))
                ->get();

            foreach ($schedules as $sched) {
                $icon = '';
                if ($status === null) {
                    $login_key = $sched->branch_id . '_' . Carbon::parse($sched->date)->toDateString();
                    $icon = $branch_logins_by_key->has($login_key) ? 'fa fa-check' : '';
                }

                $schedule_data[] = [
                    'title'           => '[' . $sched->branch->account->short_name . ' - ' . $sched->branch->branch_code . ' - ' . $sched->branch->branch_name . '] ' . $sched->objective,
                    'start'           => Carbon::parse($sched->date)->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => $colors[$type],
                    'borderColor'     => $colors[$type],
                    'type'            => 'schedule',
                    'id'              => $sched->id,
                    'icon'            => $icon,
                ];
            }
        }

        // Single query with eager-loaded user — replaces date-loop + N+1 on $data->user
        $deviations = Deviation::with('user')
            ->where('status', 'submitted')
            ->whereBetween('date', [$date_from, $date_to])
            ->when(!empty($user_id), fn($q) => $q->where('user_id', $user_id))
            ->get();

        foreach ($deviations as $deviation) {
            $schedule_data[] = [
                'title'           => '[' . $deviation->user->fullName() . ' - deviation] - ' . $deviation->reason_for_deviation,
                'start'           => Carbon::parse($deviation->date)->toDateString(),
                'allDay'          => true,
                'backgroundColor' => $colors['deviation'],
                'borderColor'     => $colors['deviation'],
                'type'            => 'deviation',
                'id'              => $deviation->id,
            ];
        }

        return $schedule_data;
    }
}
