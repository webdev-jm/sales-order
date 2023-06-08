<?php

namespace App\Http\Controllers;

use App\Models\ChannelOperation;
use Illuminate\Http\Request;

class ChannelOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('channel-operations.index');
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
     * @param  \App\Models\ChannelOperation  $channelOperation
     * @return \Illuminate\Http\Response
     */
    public function show(ChannelOperation $channelOperation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChannelOperation  $channelOperation
     * @return \Illuminate\Http\Response
     */
    public function edit(ChannelOperation $channelOperation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChannelOperation  $channelOperation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ChannelOperation $channelOperation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChannelOperation  $channelOperation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChannelOperation $channelOperation)
    {
        //
    }
}
