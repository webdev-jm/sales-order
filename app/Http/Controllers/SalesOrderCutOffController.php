<?php

namespace App\Http\Controllers;

use App\Models\SalesOrderCutOff;
use App\Http\Requests\StoreSalesOrderCutOffRequest;
use App\Http\Requests\UpdateSalesOrderCutOffRequest;

class SalesOrderCutOffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cut_offs = SalesOrderCutOff::orderBy('id', 'DESC')
        ->paginate(10);

        return view('sales-orders.cut-offs.index')->with([
            'cut_offs' => $cut_offs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sales-orders.cut-offs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalesOrderCutOffRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesOrderCutOffRequest $request)
    {
        $cut_off = new SalesOrderCutOff([
            'user_id' => auth()->user()->id,
            'date' => $request->date,
            'time' => $request->time,
            'message' => $request->message
        ]);
        $cut_off->save();

        // logs
        activity('create')
        ->performedOn($cut_off)
        ->log(':causer.firstname :causer.lastname has created sales order cut-off on :subject.date');

        return redirect()->route('cut-off.index')->with([
            'message_success' => 'Cut-off was created'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesOrderCutOff  $salesOrderCutOff
     * @return \Illuminate\Http\Response
     */
    public function show(SalesOrderCutOff $salesOrderCutOff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesOrderCutOff  $salesOrderCutOff
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cut_off = SalesOrderCutOff::findOrFail($id);

        return view('sales-orders.cut-offs.edit')->with([
            'cut_off' => $cut_off
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesOrderCutOffRequest  $request
     * @param  \App\Models\SalesOrderCutOff  $salesOrderCutOff
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesOrderCutOffRequest $request, $id)
    {
        $cut_off = SalesOrderCutOff::findOrFail($id);

        $changes_arr['old'] = $cut_off->getOriginal();

        $cut_off->update([
            'date' => $request->date,
            'time' => $request->time,
            'message' => $request->message
        ]);

        $changes_arr['changes'] = $cut_off->getChanges();

        // logs
        activity('update')
        ->performedOn($cut_off)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated sales order cut-off :subject.date');

        return back()->with([
            'message_success' => 'Sales order cut-off was updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesOrderCutOff  $salesOrderCutOff
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesOrderCutOff $salesOrderCutOff)
    {
        //
    }
}
