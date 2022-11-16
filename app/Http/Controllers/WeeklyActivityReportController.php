<?php

namespace App\Http\Controllers;

use App\Models\WeeklyActivityReport;
use App\Http\Requests\StoreWeeklyActivityReportRequest;
use App\Http\Requests\UpdateWeeklyActivityReportRequest;

class WeeklyActivityReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreWeeklyActivityReportRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWeeklyActivityReportRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function show(WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function edit(WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWeeklyActivityReportRequest  $request
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWeeklyActivityReportRequest $request, WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }
}
