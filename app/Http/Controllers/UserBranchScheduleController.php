<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranchSchedule;
use App\Http\Requests\StoreUserBranchScheduleRequest;
use App\Http\Requests\UpdateUserBranchScheduleRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScheduleImport;

class UserBranchScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = trim($request->get('user_id'));
        $branch_id = trim($request->get('branch_id'));

        $request_color = '#25b8b5';

        $schedule_data = [];
        if(auth()->user()->hasRole('superadmin')) {

            if(!empty($user_id) || !empty($branch_id)) {
                $schedules_date = UserBranchSchedule::select('date')->distinct()->get();
                
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->where('date', $schedule->date);
                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($branch_id)) {
                        $schedules->where('branch_id', $branch_id);
                    }
    
                    $schedules = $schedules->get();

                    if($schedules->count() > 0) {
                        $schedule_data[] = [
                            'title' => $schedules->count().($schedules->count() > 1 ? ' requests' : ' request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $request_color,
                            'borderColor' => $request_color,
                        ];
                    }
                }
            } else {
                $schedules_date = UserBranchSchedule::select('date')->distinct()->get();
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::where('date', $schedule->date);

                    if($schedules->count() > 0) {
                        $schedule_data[] = [
                            'title' => $schedules->count().($schedules->count() > 1 ? ' requests' : ' request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $request_color,
                            'borderColor' => $request_color,
                        ];
                    }
                }
            }

            $users = UserBranchSchedule::select('user_id')->distinct()->get('user_id');

            $users_arr = [
                '' => 'All'
            ];
            foreach($users as $user) {
                $user_data = User::findOrFail($user->user_id);
                $users_arr[$user_data->id] = $user_data->firstname.' '.$user_data->lastname;
            }
        } else {
            if(!empty($branch_id)) {
                $schedules_date = UserBranchSchedule::select('date')->distinct()->get();
                foreach($schedules_date as $schedule) {
                    $count = UserBranchSchedule::where('user_id', auth()->user()->id)
                    ->where('branch_id', $branch_id)
                    ->where('date', $schedule->date)
                    ->count();

                    if($count > 0) {
                        $schedule_data[] = [
                            'title' => $count.($count > 1 ? ' requests' : ' request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $request_color,
                            'borderColor' => $request_color,
                        ];
                    }
                }
            } else {
                $schedules_date = UserBranchSchedule::select('date')->distinct()->get();
                foreach($schedules_date as $schedule) {
                    $count = UserBranchSchedule::where('user_id', auth()->user()->id)
                    ->where('date', $schedule->date)
                    ->count();

                    if($count > 0) {
                        $schedule_data[] = [
                            'title' => $count.($count > 1 ? ' schedules' : ' schedule'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $request_color,
                            'borderColor' => $request_color,
                        ];
                    }
                }
            }

            $users_arr = [
                auth()->user()->id => auth()->user()->firstname.' '.auth()->user()->lastname
            ];
        }
    
        $branches = UserBranchSchedule::select('branch_id')->distinct()->get('branch_id');

        $branches_arr = [
            '' => 'All'
        ];
        foreach($branches as $branch) {
            $branch_val = Branch::findOrFail($branch->branch_id);
            $branches_arr[$branch_val->id] = $branch_val->branch_code.' '.$branch_val->branch_name;
        }
        
        return view('schedules.index')->with([
            'user_id' => $user_id,
            'branch_id' => $branch_id,
            'schedule_data' => $schedule_data,
            'users' => $users_arr,
            'branches' => $branches_arr
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
        //
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
}
