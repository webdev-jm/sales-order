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
        $user_id = trim($request->get('user_id'));
        $account_id = trim($request->get('account_id'));

        $schedule_color = '#25b8b5';
        $reschedule_color = '#f37206';
        $delete_color = '#c90518';
        $request_color = '#32a852';
        $deviation_color = '#0e16ad';

        // $subordinate_ids = $this->getSubordinates(auth()->user()->id);
        $subordinates = auth()->user()->getSubordinateIds();
        $subordinate_ids = [];
        foreach($subordinates as $level => $ids) {
            foreach($ids as $id) {
                $subordinate_ids[] = $id;
            }
        }

        $schedule_data = [];
        if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('sales')) {

            // check filter
            if(!empty($user_id) || !empty($account_id)) {
                // Schedules
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->whereNull('status')
                ->get();
                
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->whereNull('status')

                    ->where('date', $schedule->date);
                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($account_id)) {
                        $schedules->whereHas('branch', function($query) use ($account_id) {
                            $query->where('account_id', $account_id);
                        });
                    }
    
                    $schedules = $schedules->get();

                    foreach($schedules as $sched) {
                        // check login
                        $branch_login = BranchLogin::where('user_id', $user_id)
                            ->where('branch_id', $sched->branch_id)
                            ->where(DB::raw('DATE(time_in)'), $schedule->date)
                            ->first();
                        
                        $icon = '';
                        if(!empty($branch_login)) {
                            $icon = 'fa fa-check';
                        }

                        $schedule_data[] = [
                            'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $schedule_color,
                            'borderColor' => $schedule_color,
                            'type' => 'schedule',
                            'id' => $sched->id,
                            'icon' => $icon,
                        ];
                    }
                }

                // for reschedule
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'for reschedule')
                ->get();
                
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->where('status', 'for reschedule')
                    ->where('date', $schedule->date);

                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($account_id)) {
                        $schedules->whereHas('branch', function($query) use ($account_id) {
                            $query->where('account_id', $account_id);
                        });
                    }
                    $schedules = $schedules->get();

                    foreach($schedules as $sched) {
                        $schedule_data[] = [
                            'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $reschedule_color,
                            'borderColor' => $reschedule_color,
                            'type' => 'reschedule',
                            'id' => $sched->id,
                        ];
                    }

                    // if($schedules->count() > 0) {
                    //     $schedule_data[] = [
                    //         'title' => $schedules->count().($schedules->count() > 1 ? ' reschedule requests' : ' reschedule request'),
                    //         'start' => $schedule->date,
                    //         'allDay' => true,
                    //         'backgroundColor' => $reschedule_color,
                    //         'borderColor' => $reschedule_color,
                    //         'type' => 'reschedule'
                    //     ];
                    // }
                }

                // for deletion
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'for deletion')
                ->get();
                
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->where('status', 'for deletion')
                    ->where('date', $schedule->date);

                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($account_id)) {
                        $schedules->whereHas('branch', function($query) use ($account_id) {
                            $query->where('account_id', $account_id);
                        });
                    }
                    $schedules = $schedules->get();

                    foreach($schedules as $sched) {
                        $schedule_data[] = [
                            'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $delete_color,
                            'borderColor' => $delete_color,
                            'type' => 'delete',
                            'id' => $sched->id,
                        ];
                    }

                    // if($schedules->count() > 0) {
                    //     $schedule_data[] = [
                    //         'title' => $schedules->count().($schedules->count() > 1 ? ' delete requests' : ' delete request'),
                    //         'start' => $schedule->date,
                    //         'allDay' => true,
                    //         'backgroundColor' => $delete_color,
                    //         'borderColor' => $delete_color,
                    //         'type' => 'delete'
                    //     ];
                    // }
                }

                // for schedule request
                $schedule_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'schedule request')
                ->get();

                foreach($schedule_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->where('status', 'schedule request')
                    ->where('date', $schedule->date);
                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($account_id)) {
                        $schedules->whereHas('branch', function($query) use ($account_id) {
                            $query->where('account_id', $account_id);
                        });
                    }

                    $schedules = $schedules->get();

                    foreach($schedules as $sched) {
                        $schedule_data[] = [
                            'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $request_color,
                            'borderColor' => $request_color,
                            'type' => 'request',
                            'id' => $sched->id,
                        ];
                    }

                    // if($schedule_count > 0) {
                    //     $schedule_data[] = [
                    //         'title' => $schedule_count.($schedule_count > 1 ? ' schedule requests' : ' schedule request'),
                    //         'start' => $schedule->date,
                    //         'allDay' => true,
                    //         'backgroundColor' => $request_color,
                    //         'borderColor' => $request_color,
                    //         'type' => 'request'
                    //     ];
                    // }

                }

                // for deviation
                $deviation_dates = Deviation::select('date')->distinct()
                ->where('status', 'submitted')
                ->get();

                foreach($deviation_dates as $deviation) {
                    $deviations = Deviation::where('date', $deviation->date)
                    ->where('status', 'submitted');

                    if(!empty($user_id)) {
                        $deviations->where('user_id', $user_id);
                    }

                    $deviations = $deviations->get();

                    foreach($deviations as $data) {
                        $schedule_data[] = [
                            'title' => '['.$data->user->fullName().'] - '.$data->reason_for_deviation,
                            'start' => $data->date,
                            'allDay' => true,
                            'backgroundColor' => $deviation_color,
                            'borderColor' => $deviation_color,
                            'type' => 'deviation',
                            'id' => $data->id,
                        ];
                    }
                }

            }

            // // user filter options
            // if(!empty($subordinate_ids)) {
            //     $users = UserBranchSchedule::select('user_id')->distinct()
            //     ->where(function($query) use($subordinate_ids){
            //         $query->whereIn('user_id', $subordinate_ids)
            //         ->orWhere('user_id', auth()->user()->id);
            //     })
            //     ->get('user_id');
            // } else {
                $users = UserBranchSchedule::select('user_id')->distinct()
                ->get('user_id');
            // }
            
            $users_arr = [
                '' => 'select'
            ];
            foreach($users as $user) {
                $user_data = User::findOrFail($user->user_id);
                $users_arr[$user_data->id] = $user_data->fullName();
            }

        } else {
            
            // Schedules
            $schedules_date = UserBranchSchedule::select('date')->distinct()
            ->whereNull('status');
            
            if(!empty($user_id)) {
                $schedules_date->where('user_id', $user_id);
            } else {
                $schedules_date->where('user_id', auth()->user()->id);
            }
            $schedules_date = $schedules_date->get();

            foreach($schedules_date as $schedule) {
                $schedules = UserBranchSchedule::whereNull('status')
                ->where('date', $schedule->date);

                // account filter
                if(!empty($account_id)) {
                    $schedules->whereHas('branch', function($query) use($account_id) {
                        $query->where('account_id', $account_id);
                    });
                }

                // user filter
                if(!empty($user_id)) {
                    $schedules->where('user_id', $user_id);
                } else {
                    $schedules->where('user_id', auth()->user()->id);
                }

                $schedules = $schedules->get();

                foreach($schedules as $sched) {

                    // check login
                    $branch_login = BranchLogin::where('user_id', $user_id)
                        ->where('branch_id', $sched->branch_id)
                        ->where(DB::raw('DATE(time_in)'), $schedule->date)
                        ->first();

                    $schedule_data[] = [
                        'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                        'start' => $schedule->date,
                        'allDay' => true,
                        'backgroundColor' => $schedule_color,
                        'borderColor' => $schedule_color,
                        'type' => 'schedule',
                        'id' => $sched->id,
                    ];
                }

                // if($count > 0) {
                //     $schedule_data[] = [
                //         'title' => $count.($count > 1 ? ' schedules' : ' schedule'),
                //         'start' => $schedule->date,
                //         'allDay' => true,
                //         'backgroundColor' => $schedule_color,
                //         'borderColor' => $schedule_color,
                //         'type' => 'schedule'
                //     ];
                // }
            }

            // For Reschedule
            $schedules_date = UserBranchSchedule::select('date')->distinct()
            ->where('status', 'for reschedule');

            if(!empty($user_id)) {
                $schedules_date->where('user_id', $user_id);
            } else {
                $schedules_date->where('user_id', auth()->user()->id);
            }
            $schedules_date = $schedules_date->get();

            foreach($schedules_date as $schedule) {
                $schedules = UserBranchSchedule::where('status', 'for reschedule')
                ->where('date', $schedule->date);

                // account filter
                if(!empty($account_id)) {
                    $schedules->whereHas('branch', function($query) use($account_id) {
                        $query->where('account_id', $account_id);
                    });
                }

                // user filter
                if(!empty(!empty($user_id))) {
                    $schedules->where('user_id', $user_id);
                } else {
                    $schedules->where('user_id', auth()->user()->id);
                }

                $schedules = $schedules->get();

                foreach($schedules as $sched) {
                    $schedule_data[] = [
                        'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                        'start' => $schedule->date,
                        'allDay' => true,
                        'backgroundColor' => $reschedule_color,
                        'borderColor' => $reschedule_color,
                        'type' => 'reschedule',
                        'id' => $sched->id,
                    ];
                }

                // if($count > 0) {
                //     $schedule_data[] = [
                //         'title' => $count.($count > 1 ? ' reschedule requests' : ' reschedule request'),
                //         'start' => $schedule->date,
                //         'allDay' => true,
                //         'backgroundColor' => $reschedule_color,
                //         'borderColor' => $reschedule_color,
                //         'type' => 'reschedule'
                //     ];
                // }
            }

            // for deletion
            $schedules_date = UserBranchSchedule::select('date')->distinct()
            ->where('status', 'for deletion');

            if(!empty($user_id)) {
                $schedules_date->where('user_id', $user_id);
            } else {
                $schedules_date->where('user_id', auth()->user()->id);
            }
            $schedules_date = $schedules_date->get();

            foreach($schedules_date as $schedule) {
                $schedules = UserBranchSchedule::where('user_id', auth()->user()->id)
                ->where('status', 'for deletion')
                ->where('date', $schedule->date);

                // account filter
                if(!empty($account_id)) {
                    $schedules->whereHas('branch', function($query) use($account_id) {
                        $query->where('account_id', $account_id);
                    });
                }

                // user filter
                if(!empty($user_id)) {
                    $schedules->where('user_id', $user_id);
                } else {
                    $schedules->where('user_id', auth()->user()->id);
                }

                $schedules = $schedules->get();

                foreach($schedules as $sched) {
                    $schedule_data[] = [
                        'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                        'start' => $schedule->date,
                        'allDay' => true,
                        'backgroundColor' => $delete_color,
                        'borderColor' => $delete_color,
                        'type' => 'delete',
                        'id' => $sched->id,
                    ];
                }

                // if($count > 0) {
                //     $schedule_data[] = [
                //         'title' => $count.($count > 1 ? ' delete requests' : ' delete request'),
                //         'start' => $schedule->date,
                //         'allDay' => true,
                //         'backgroundColor' => $delete_color,
                //         'borderColor' => $delete_color,
                //         'type' => 'delete'
                //     ];
                // }
            }

            // for schedule request
            $schedules_date = UserBranchSchedule::select('date')->distinct()
            ->where('status', 'schedule request');

            if(!empty($user_id)) {
                $schedules_date->where('user_id', $user_id);
            } else {
                $schedules_date->where('user_id', auth()->user()->id);
            }
            $schedules_date = $schedules_date->get();

            foreach($schedules_date as $schedule) {
                $schedules = UserBranchSchedule::where('status', 'schedule request')
                ->where('date', $schedule->date);

                // account filter
                if(!empty($account_id)) {
                    $schedules->whereHas('branch', function($query) use($account_id) {
                        $query->where('account_id', $account_id);
                    });
                }

                // user filter
                if(!empty($user_id)) {
                    $schedules->where('user_id', $user_id);
                } else {
                    $schedules->where('user_id', auth()->user()->id);
                }

                $schedules = $schedules->get();

                foreach($schedules as $sched) {
                    $schedule_data[] = [
                        'title' => '['.$sched->branch->branch_code.' - '.$sched->branch->branch_name.'] '.$sched->objective,
                        'start' => $schedule->date,
                        'allDay' => true,
                        'backgroundColor' => $request_color,
                        'borderColor' => $request_color,
                        'type' => 'request',
                        'id' => $sched->id,
                    ];
                }
            }

            // $users_arr = [
            //     auth()->user()->id => auth()->user()->fullName()
            // ];

            // user filter options
            $users_arr = [
                auth()->user()->id => auth()->user()->fullName()
            ];

            if(!empty($subordinate_ids)) {
                $users = User::whereIn('id', $subordinate_ids)->get();
                
                foreach($users as $user) {
                    $users_arr[$user->id] = $user->fullName();
                }
            } else {
                // $users = User::find(auth()->user);

                // foreach($users as $user) {
                //     $user_data = User::findOrFail($user->user_id);
                //     $users_arr[$user_data->id] = $user_data->fullName();
                // }
            }
        }
    
        $branches = UserBranchSchedule::select('branch_id')->distinct()
        ->whereNull('status')
        ->get('branch_id');
        
        $accounts = Account::orderBy('account_code', 'ASC')
        ->whereHas('branches', function($query) use ($branches) {
            $query->whereIn('id', $branches->pluck('branch_id'));
        })->get();

        $accounts_arr = [
            '' => 'select'
        ];
        foreach($accounts as $account) {
            $accounts_arr[$account->id] = $account->account_code.' - '.$account->short_name;
        }
        
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserBranchSchedule  $userBranchSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(UserBranchSchedule $userBranchSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserBranchSchedule  $userBranchSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(UserBranchSchedule $userBranchSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserBranchScheduleRequest  $request
     * @param  \App\Models\UserBranchSchedule  $userBranchSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserBranchScheduleRequest $request, UserBranchSchedule $userBranchSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserBranchSchedule  $userBranchSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserBranchSchedule $userBranchSchedule)
    {
        //
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

    public function getSubordinates($user_id) {
        $user = User::findOrFail($user_id);
        $organizations = $user->organizations;
        $subordinate_ids = [];
        foreach($organizations as $organization) {
            $subordinates = OrganizationStructure::where('reports_to_id', $organization->id)
            ->get();
            foreach($subordinates as $subordinate) {
                if(!empty($subordinate->user_id)) {
                    $subordinate_ids[] = $subordinate->user_id;
                }
                // get second level subordinates
                $subordinates2 = OrganizationStructure::where('reports_to_id', $subordinate->id)
                ->get();
                foreach($subordinates2 as $subordinate2) {
                    if(!empty($subordinate2->user_id)) {
                        $subordinate_ids[] = $subordinate2->user_id;
                    }
                    // get third level subordinates
                    $subordinates3 = OrganizationStructure::where('reports_to_id', $subordinate2->id)
                    ->get();
                    foreach($subordinates3 as $subordinate3) {
                        if(!empty($subordinate3->user_id)) {
                            $subordinate_ids[] = $subordinate3->user_id;
                        }
                    }
                }
            }
        }

        return $subordinate_ids;
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
