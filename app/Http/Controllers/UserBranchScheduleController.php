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
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class UserBranchScheduleController extends Controller
{
    use GlobalTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Set date range for schedules
        $date_from = date('Y-m', strtotime('last month')).'-01';
        $date_to = date('Y-m-d', strtotime('last day of next month'));

        // Extract user and account IDs from the request
        $user_id = trim($request->get('user_id'));
        $account_id = trim($request->get('account_id'));

        // Define colors for schedule types
        $colors = [
            'schedule' => '#25b8b5',
            'reschedule' => '#f37206',
            'delete' => '#c90518',
            'request' => '#32a852',
            'deviation' => '#0e16ad',
        ];

        // Assign colors to variables for better readability
        $schedule_color = $colors['schedule'];
        $reschedule_color = $colors['reschedule'];
        $delete_color = $colors['delete'];
        $request_color = $colors['request'];
        $deviation_color = $colors['deviation'];

        // Get subordinate IDs for the current user
        $subordinates = auth()->user()->getSubordinateIds();
        $subordinate_ids = [];
        foreach ($subordinates as $level => $ids) {
            foreach ($ids as $id) {
                $subordinate_ids[] = $id;
            }
        }

        // Initialize schedule data array
        $schedule_data = [];

        // Check user roles for permission to view all schedules
        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('sales')) {

            // Check for user or account filters
            if (!empty($user_id) || !empty($account_id)) {

                // Define schedule types and their corresponding status
                $schedule_statuses = [
                    'schedule' => null,
                    'request' => 'schedule request',
                    'deviation' => 'submitted',
                ];

                // Loop through schedule types
                foreach ($schedule_statuses as $schedule_type => $status) {
                    if($schedule_type != 'deviation') {
                        // Fetch distinct schedule dates based on status, user, and account filters
                        $schedules_date = UserBranchSchedule::select('date')->distinct()
                            ->where('status', $status)
                            ->where('date', '>=', $date_from)
                            ->where('date', '<=', $date_to)
                            ->when(!empty($user_id), function ($query) use ($user_id) {
                                $query->where('user_id', $user_id);
                            })
                            ->get();
    
                        // Loop through schedule dates
                        foreach ($schedules_date as $schedule) {
                            // Fetch schedules based on date, status, user, and account filters
                            $schedules = UserBranchSchedule::where('date', $schedule->date)
                                ->when($status !== null, function ($query) use ($status) {
                                    $query->where('status', $status);
                                })
                                ->when(!empty($account_id), function ($query) use ($account_id) {
                                    $query->whereHas('branch', function ($qry) use ($account_id) {
                                        $qry->where('account_id', $account_id);
                                    });
                                })
                                ->when(!empty($user_id), function ($query) use ($user_id) {
                                    $query->where('user_id', $user_id);
                                })
                                ->get();
    
                            // Loop through fetched schedules
                            foreach ($schedules as $sched) {
                                $icon = '';
    
                                // Check login status for schedule type 'schedule'
                                if ($status == null) {
                                    $branch_login = BranchLogin::where('user_id', $user_id)
                                        ->where('branch_id', $sched->branch_id)
                                        ->where(DB::raw('DATE(time_in)'), $schedule->date)
                                        ->first();
    
                                    if (!empty($branch_login)) {
                                        $icon = 'fa fa-check';
                                    }
                                }
    
                                // Assign background and border colors based on schedule type
                                $backgroundColor = $colors[$schedule_type];
                                $borderColor = $colors[$schedule_type];
    
                                // Add schedule data to the array
                                $schedule_data[] = [
                                    'title' => '['.$sched->branch->account->short_name.' - '.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                                    'start' => $schedule->date,
                                    'allDay' => true,
                                    'backgroundColor' => $backgroundColor,
                                    'borderColor' => $borderColor,
                                    'type' => 'schedule',
                                    'id' => $sched->id,
                                    'icon' => $icon,
                                ];
                            }
                        }
                    } else {
                        // for deviation
                        $deviation_dates = Deviation::select('date')->distinct()
                            ->where('status', $status)
                            ->where('date', '>=', $date_from)
                            ->where('date', '<=', $date_to)
                            ->get();

                        foreach($deviation_dates as $deviation) {
                            $deviations = Deviation::where('date', $deviation->date)
                            ->where('status', $status)
                            ->when(!empty($user_id), function($query) use($user_id) {
                                $query->where('user_id', $user_id);
                            })
                            ->get();

                            foreach($deviations as $data) {
                                $schedule_data[] = [
                                    'title' => '['.$sched->branch->account->short_name.' - '.$data->user->fullName().'] - '.$data->reason_for_deviation,
                                    'start' => $data->date,
                                    'allDay' => true,
                                    'backgroundColor' => $deviation_color,
                                    'borderColor' => $deviation_color,
                                    'type' => $schedule_type,
                                    'id' => $data->id,
                                ];
                            }
                        }
                    }
                }
            }

            // SET USER FILTER
            $users = UserBranchSchedule::select('user_id')->distinct()
                ->where('date', '>=', $date_from)
                ->where('date', '<=', $date_to)
                ->get('user_id');

            $users_arr = [
                '' => 'select'
            ];

            // Build user filter options
            foreach ($users as $user) {
                $user_data = User::findOrFail($user->user_id);
                $users_arr[$user_data->id] = $user_data->fullName();
            }

        } else {

            // If user_id is not provided, use the current user's ID
            if (empty($user_id)) {
                $user_id = auth()->user()->id;
            }

            // Define schedule types and their corresponding status
            $schedule_statuses = [
                'schedule' => null,
                'schedule_request' => 'schedule request',
                'deviation' => 'submitted',
            ];

            // Loop through schedule types
            foreach ($schedule_statuses as $schedule_type => $status) {
                if($schedule_type != 'deviation') {
                    // Fetch distinct schedule dates based on status, user, and account filters
                    $schedules_date = UserBranchSchedule::select('date')->distinct()
                        ->where('status', $status)
                        ->where('date', '>=', $date_from)
                        ->where('date', '<=', $date_to)
                        ->when(!empty($user_id), function ($query) use ($user_id) {
                            $query->where('user_id', $user_id);
                        })
                        ->get();

                    // Loop through schedule dates
                    foreach ($schedules_date as $schedule) {
                        // Fetch schedules based on date, status, user, and account filters
                        $schedules = UserBranchSchedule::with('branch', 'branch.account')
                            ->where('date', $schedule->date)
                            ->when($status !== null, function ($query) use ($status) {
                                $query->where('status', $status);
                            })
                            ->when(!empty($account_id), function ($query) use ($account_id) {
                                $query->whereHas('branch', function ($qry) use ($account_id) {
                                    $qry->where('account_id', $account_id);
                                });
                            })
                            ->when(!empty($user_id), function ($query) use ($user_id) {
                                $query->where('user_id', $user_id);
                            })
                            ->get();

                        // Loop through fetched schedules
                        foreach ($schedules as $sched) {
                            $icon = '';

                            // Check login status for schedule type 'schedule'
                            if ($status == null) {
                                $branch_login = BranchLogin::where('user_id', $user_id)
                                    ->where('branch_id', $sched->branch_id)
                                    ->where(DB::raw('DATE(time_in)'), $schedule->date)
                                    ->first();

                                if (!empty($branch_login)) {
                                    $icon = 'fa fa-check';
                                }
                            }

                            // Assign background and border colors based on schedule type
                            $backgroundColor = $colors[$schedule_type];
                            $borderColor = $colors[$schedule_type];

                            // Add schedule data to the array
                            $schedule_data[] = [
                                'title' => '['.$sched->branch->account->short_name.' - '.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                                'start' => $schedule->date,
                                'allDay' => true,
                                'backgroundColor' => $backgroundColor,
                                'borderColor' => $borderColor,
                                'type' => 'schedule',
                                'id' => $sched->id,
                                'icon' => $icon,
                            ];
                        }
                    }
                } else {
                    // for deviation
                    $deviation_dates = Deviation::select('date')->distinct()
                        ->where('status', $status)
                        ->where('date', '>=', $date_from)
                        ->where('date', '<=', $date_to)
                        ->get();

                    foreach($deviation_dates as $deviation) {
                        $deviations = Deviation::where('date', $deviation->date)
                            ->where('status', $status)
                            ->when(!empty($user_id), function($query) use($user_id) {
                                $query->where('user_id', $user_id);
                            })
                            ->get();

                        foreach($deviations as $data) {
                            $schedule_data[] = [
                                'title' => '['.$sched->branch->account->short_name.' - '.$data->user->fullName().'] - '.$data->reason_for_deviation,
                                'start' => $data->date,
                                'allDay' => true,
                                'backgroundColor' => $deviation_color,
                                'borderColor' => $deviation_color,
                                'type' => $schedule_type,
                                'id' => $data->id,
                            ];
                        }
                    }
                }
            }

            // user filter options
            $users_arr = [
                auth()->user()->id => auth()->user()->fullName()
            ];

            // If subordinate IDs are available, add them to user filter options
            if (!empty($subordinate_ids)) {
                $users = User::whereIn('id', $subordinate_ids)->get();

                foreach ($users as $user) {
                    $users_arr[$user->id] = $user->fullName();
                }
            }
        }

        // Fetch branches and accounts based on schedule data
        $branches = UserBranchSchedule::select('branch_id')->distinct()
            ->whereNull('status')
            ->where('date', '>=', $date_from)
            ->where('date', '<=', $date_to)
            ->get('branch_id');

        $accounts = Account::orderBy('account_code', 'ASC')
            ->whereHas('branches', function ($query) use ($branches) {
                $query->whereIn('id', $branches->pluck('branch_id'));
            })->get();

        $accounts_arr = [
            '' => 'select'
        ];

        // Build account filter options
        foreach ($accounts as $account) {
            $accounts_arr[$account->id] = $account->account_code.' - '.$account->short_name;
        }

        // Return view with necessary data
        return view('schedules.index')->with([
            'user_id' => $user_id,
            'account_id' => $account_id,
            'users' => $users_arr,
            'accounts' => $accounts_arr,
            'schedule_data' => $schedule_data
        ]);
    }

    // List
    public function list(Request $request) {
        $search = trim($request->get('search'));

        $schedules = UserBranchSchedule::orderBy('updated_at', 'DESC')
        ->where(function($query) {
            $query->whereNotNull('status')
            ->orWhereHas('approvals');
        })
        ->paginate(10)->onEachSide(1);

        return view('schedules.list')->with([
            'search' => $search,
            'schedules' => $schedules
        ]);
    }

    // deviations
    public function deviations(Request $request) {
        $search = trim($request->get('search'));

        $status_arr = [
            'submitted' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        $settings = $this->getSettings();

        $deviations = Deviation::DeviationSearch($search, $settings->data_per_page);

        return view('schedules.deviations')->with([
            'search' => $search,
            'deviations' => $deviations,
            'status_arr' => $status_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserBranchScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserBranchScheduleRequest $request)
    {
        $schedule = new UserBranchSchedule([
            'user_id' => $request->user_id,
            'branch_id' => $request->branch_id,
            'date' => $request->date,
            'source' => 'create'
        ]);
        $schedule->save();

        return back()->with([
            'message_success' => 'Schedule was created'
        ]);
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new ScheduleImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded schedules');

        return back()->with([
            'message_success' => 'Schedule has been uploaded.'
        ]);
    }

    public function printDeviationForm($id) {
        $deviation = Deviation::findOrFail($id);
        $original_schedules = $deviation->schedules()->where('type', 'original')->get();
        $new_schedules = $deviation->schedules()->where('type', 'new')->get();

        $pdf = PDF::loadView('schedules.deviation-pdf', [
            'deviation' => $deviation,
            'original_schedules' => $original_schedules,
            'new_schedules' => $new_schedules
        ]);

        return $pdf->stream('deviation-form-'.$deviation->date.'-'.time().'.pdf');

        // return view('schedules.deviation-pdf')->with([
        //     'deviation' => $deviation,
        //     'original_schedules' => $original_schedules,
        //     'new_schedules' => $new_schedules
        // ]);
    }
}
