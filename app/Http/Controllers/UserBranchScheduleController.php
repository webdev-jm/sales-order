<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $schedule_data = [];
        if(auth()->user()->hasRole('superadmin')) {
            $schedules = UserBranchSchedule::all();
            foreach($schedules as $schedule) {
                $schedule_data[] = [
                    'title' => $schedule->user->firstname.' '.$schedule->user->lastname.' '.$schedule->branch->branch_code,
                    'start' => $schedule->date,
                    'allDay' => true,
                    'backgroundColor' => '#00a65a', //Success (green)
                    'borderColor' => '#00a65a' //Success (green)
                ];
            }
        } else {
            $schedules = UserBranchSchedule::where('user_id', auth()->user()->id)->get();
            foreach($schedules as $schedule) {
                $schedule_data[] = [
                    'title' => $schedule->branch->branch_code,
                    'start' => $schedule->date,
                    'allDay' => true,
                    'backgroundColor' => '#00a65a', //Success (green)
                    'borderColor' => '#00a65a' //Success (green)
                ];
            }
        }
        
        return view('calendars.index')->with([
            'schedule_data' => $schedule_data
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
