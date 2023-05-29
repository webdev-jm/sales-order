<?php

namespace App\Http\Controllers;

use App\Models\SalesmenLocation;
use App\Http\Requests\StoreSalesmenLocationRequest;
use App\Http\Requests\UpdateSalesmenLocationRequest;

class SalesmenLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $salesmen_locations = SalesmenLocation::orderBy('salesman_id', 'asc')
            ->paginate(10)->onEachSide(1);

        return view('salesmen-locations.index')->with([
            'salesmen_locations' => $salesmen_locations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('salesmen-locations.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalesmenLocationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesmenLocationRequest $request)
    {
        $salesman_location = new SalesmenLocation([
            'salesman_id' => $request->salesman_id,
            'province' => $request->province,
            'city' => $request->city
        ]);
        $salesman_location->save();

        // logs
        activity('created')
        ->performedOn($salesman_location)
        ->log(':causer.firstname :causer.lastname has created salesman loccation :subject.province :subject.city');

        return redirect()->route('salesman-location.index')->with([
            'message_success' => 'Salesman location '.$salesman_location->province.' '.$salesman_location->city.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesmenLocation  $salesmenLocation
     * @return \Illuminate\Http\Response
     */
    public function show(SalesmenLocation $salesmenLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesmenLocation  $salesmenLocation
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesmenLocation $salesmenLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesmenLocationRequest  $request
     * @param  \App\Models\SalesmenLocation  $salesmenLocation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesmenLocationRequest $request, SalesmenLocation $salesmenLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesmenLocation  $salesmenLocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesmenLocation $salesmenLocation)
    {
        //
    }
}
