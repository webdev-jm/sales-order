<?php

namespace App\Http\Controllers;

use App\Models\ActivityPlan;
use App\Http\Requests\StoreActivityPlanRequest;
use App\Http\Requests\UpdateActivityPlanRequest;

class ActivityPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('mcp.index');
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
     * @param  \App\Http\Requests\StoreActivityPlanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActivityPlanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function show(ActivityPlan $activityPlan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function edit(ActivityPlan $activityPlan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateActivityPlanRequest  $request
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActivityPlanRequest $request, ActivityPlan $activityPlan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActivityPlan $activityPlan)
    {
        //
    }
}
