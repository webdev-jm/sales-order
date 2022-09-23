<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

class CompanyController extends Controller
{

    use GlobalTrait;
    
    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $companies = Company::CompanySearch($search, $this->setting->data_per_page);
        
        return view('companies.index')->with([
            'companies' => $companies,
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
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCompanyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyRequest $request)
    {
        $company = new Company([
            'name' => $request->name,
            'order_limit' => $request->order_limit
        ]);
        $company->save();

        // logs
        activity('create')
        ->performedOn($company)
        ->log(':causer.firstname :causer.lastname has created company :subject.name');

        return redirect()->route('company.index')->with([
            'message_success' => 'Company '.$company->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.edit')->with([
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompanyRequest  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyRequest $request, $id)
    {
        $company = Company::findOrFail($id);
        $company_name = $company->name;
        $company->update([
            'name' => $request->name,
            'order_limit' => $request->order_limit
        ]);
        
        $changes_arr = [
            'old' => $company,
            'changes' => $company->getChanges()
        ];

        // logs
        activity('update')
        ->performedOn($company)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated company :subject.name.');

        return back()->with([
            'message_success' => 'Company '.$company_name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
