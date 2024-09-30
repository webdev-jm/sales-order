<?php

namespace App\Http\Controllers;

use App\Models\PafPrePlan;
use Illuminate\Http\Request;

class PafPrePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pre_plans = PafPrePlan::orderBy('created_at', 'DESC')
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pre-plans.index')->with([
            'pre_plans' => $pre_plans
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pre_plan = PafPrePlan::findOrFail($id);

        $pre_plan_details = $pre_plan->pre_plan_details()
            ->paginate(10);

        return view('pre-plans.show')->with([
            'pre_plan' => $pre_plan,
            'pre_plan_details' => $pre_plan_details
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function edit(PafPrePlan $pafPrePlan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PafPrePlan $pafPrePlan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(PafPrePlan $pafPrePlan)
    {
        //
    }
}
