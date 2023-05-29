<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\SalesmenLocation;
use App\Http\Requests\StoreSalesmanRequest;
use App\Http\Requests\UpdateSalesmanRequest;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SalesmanImport;

class SalesmanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $salesmen = Salesman::where('user_id', auth()->user()->id)
            ->paginate(10)->onEachSide(1);

        return view('salesmen.index')->with([
            'salesmen' => $salesmen
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('salesmen.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalesmanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesmanRequest $request)
    {
        $salesman = new Salesman([
            'code' => $request->code, 
            'name' => $request->name,
            'user_id' => auth()->user()->id
        ]);
        $salesman->save();

        // logs
        activity('created')
        ->performedOn($salesman)
        ->log(':causer.firstname :causer.lastname has created salesman :subject.code :subject.name');

        return redirect()->route('salesman.index')->with([
            'message_success' => 'Salesman '.$salesman->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function show(Salesman $salesman)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $salesman = Salesman::findOrFail($id);

        return view('salesmen.edit')->with([
            'salesman' => $salesman
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesmanRequest  $request
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesmanRequest $request, $id)
    {
        $salesman = Salesman::findOrFail($id);
        $changes_arr['old'] = $salesman->getOriginal();

        $salesman->update([
            'code' => $request->code,
            'name' => $request->name
        ]);

        $changes_arr['changes'] = $salesman->getChanges();

        // logs
        activity('update')
        ->performedOn($salesman)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated salesman :subject.name');

        return back()->with([
            'message_success' => 'Salesman '.$salesman->name.' has been updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function destroy(Salesman $salesman)
    {
        //
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $response = Salesman::SalesmanAjax($search);
        return response()->json($response);
    }

    public function upload(Request $request) {
        $imports = Excel::toArray(new SalesmanImport, $request->upload_file);
        foreach($imports[0] as $row) {
            $code = trim($row[0]);
            $name = trim($row[1]);
            $province = trim($row[2]);
            $city = trim($row[3]);

            if(!empty($code) && !empty($name)) {
                // CHECK
                $salesman = Salesman::where('name', $name)
                    ->where('code', $code)
                    ->first();

                if(empty($salesman)) {
                    $salesman = new Salesman([
                        'user_id' => auth()->user()->id,
                        'name' => $name,
                        'code' => $code
                    ]);
                    $salesman->save();
                }

                // CHECK LOCATIONS
                $location = SalesmenLocation::where('salesman_id', $salesman->id)
                    ->where('province', $province)
                    ->where('city', $city)
                    ->first();

                if(empty($location)) {
                    $location = new SalesmenLocation([
                        'salesman_id' => $salesman->id,
                        'province' => $province,
                        'city' => $city
                    ]);
                    $location->save();
                }
            }
        }

        return back()->with([
            'message_success' => 'Salesmen has been uploaded.'
        ]);
    }
}
