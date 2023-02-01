<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Company;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCostCenterRequest;
use App\Http\Requests\UpdateCostCenterRequest;

use App\Imports\CostCenterImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Traits\GlobalTrait;

class CostCenterController extends Controller
{
    use GlobalTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        // settings
        $settings = $this->getSettings();

        $cost_centers = CostCenter::CostCenterSearch($search, $settings->data_per_page);

        return view('cost-centers.index')->with([
            'cost_centers' => $cost_centers,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::orderBy('id', 'ASC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('cost-centers.create')->with([
            'companies' => $companies_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCostCenterRequest $request)
    {
        $cost_center = new CostCenter([
            'company_id' => $request->company_id,
            'user_id' => $request->user_id,
            'cost_center' => $request->cost_center
        ]);
        $cost_center->save();

        // logs
        activity('create')
        ->performedOn($cost_center)
        ->log(':causer.firstname :causer.lastname has created cost center :subject.cost_center');

        return redirect()->route('cost-center.index')->with([
            'message_success' => 'Cost Center was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\Response
     */
    public function show(CostCenter $costCenter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cost_center = CostCenter::findOrFail($id);

        $companies = Company::orderBy('id', 'ASC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('cost-centers.edit')->with([
            'cost_center' => $cost_center,
            'companies' => $companies_arr
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCostCenterRequest $request, $id)
    {
        $cost_center = CostCenter::findOrFail($id);
        $changes_arr['old'] = $cost_center->getOriginal();
        
        $cost_center->update([
            'company_id' => $request->company_id,
            'user_id' => $request->user_id,
            'cost_center' => $request->cost_center
        ]);

        $changes_arr['changes'] = $cost_center->getChanges();

        // logs
        activity('update')
        ->performedOn($cost_center)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated cost center :subject.cost_center .');

        return back()->with([
            'message_success' => 'Cost center was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\Response
     */
    public function destroy(CostCenter $costCenter)
    {
        //
    }

    public function importCostCenter(Request $request) {
        Excel::import(new CostCenterImport, $request->upload_file);

        return back()->with([
            'message_success' => 'Cost Center was uploaded.'
        ]);
    }
}
