<?php

namespace App\Http\Controllers;

use App\Models\SalesPerson;
use App\Models\User;
use App\Models\Account;
use App\Http\Requests\StoreSalesPersonRequest;
use App\Http\Requests\UpdateSalesPersonRequest;

class SalesPersonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sales_people = SalesPerson::orderBy('id', 'DESC')->paginate(10);
        return view('sales-people.index')->with([
            'sales_people' => $sales_people
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sales-people.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalesPersonRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesPersonRequest $request)
    {
        $sales_person = new SalesPerson([
            'user_id' => $request->user_id,
            'code' => $request->code
        ]);
        $sales_person->save();

        $sales_person->accounts()->attach($request->accounts);

        // logs
        activity('create')
        ->performedOn($sales_person)
        ->log(':causer.firstname :causer.lastname has created sales person :subject.code');

        return redirect()->route('sales-people.index')->with([
            'message_success' => 'Sales Person '.$sales_person->code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesPerson  $salesPerson
     * @return \Illuminate\Http\Response
     */
    public function show(SalesPerson $salesPerson)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesPerson  $salesPerson
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sales_person = SalesPerson::findOrFail($id);

        $accounts = Account::orderBy('account_code', 'ASC')->get();
        $accounts_arr = [];
        foreach($accounts as $account) {
            $accounts_arr[$account->id] = '['.$account->account_code.'] '.$account->short_name;
        }

        return view('sales-people.edit')->with([
            'sales_person' => $sales_person,
            'accounts' => $accounts_arr,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesPersonRequest  $request
     * @param  \App\Models\SalesPerson  $salesPerson
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesPersonRequest $request, $id)
    {
        $sales_person = SalesPerson::findOrFail($id);
        $code = $sales_person->code;

        $changes_arr['old'] = $sales_person;

        $sales_person->update([
            'user_id' => $request->user_id,
            'code' => $request->code
        ]);
        $sales_person->save();

        $sales_person->accounts()->sync($request->accounts);

        $changes_arr['changes'] = $sales_person->getChanges();

        // logs
        activity('update')
        ->performedOn($sales_person)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated sales person :subject.code.');

        return back()->with([
            'message_success' => 'Sales Person '.$sales_person->code.' was update.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesPerson  $salesPerson
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesPerson $salesPerson)
    {
        //
    }
}
