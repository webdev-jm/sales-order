<?php

namespace App\Http\Controllers;

use App\Models\JobTitle;
use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;

class JobTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
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
     * @param  \App\Http\Requests\StoreJobTitleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobTitleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobTitle  $jobTitle
     * @return \Illuminate\Http\Response
     */
    public function show(JobTitle $jobTitle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobTitle  $jobTitle
     * @return \Illuminate\Http\Response
     */
    public function edit(JobTitle $jobTitle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobTitleRequest  $request
     * @param  \App\Models\JobTitle  $jobTitle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobTitleRequest $request, JobTitle $jobTitle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobTitle  $jobTitle
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobTitle $jobTitle)
    {
        //
    }
}
