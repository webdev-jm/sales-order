<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Discount;
use App\Models\Company;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accounts = Account::orderBy('id', 'DESC')->paginate(10);
        return view('accounts.index')->with([
            'accounts' => $accounts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $discounts = Discount::orderBy('discount_code', 'ASC')->get();
        $discount_arr = [];
        foreach($discounts as $discount) {
            $discount_arr[$discount->id] = $discount->discount_code;
        }

        $companies = Company::orderBy('name', 'DESC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('accounts.create')->with([
            'discounts' => $discount_arr,
            'companies' => $companies_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAccountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountRequest $request)
    {
        $account = new Account([
            'company_id' => $request->company_id,
            'discount_id' => $request->discount_id,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'short_name' => $request->short_name,
        ]);
        $account->save();

        return redirect()->route('account.index')->with([
            'message_success' => 'Account '.$account->account_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $discounts = Discount::orderBy('discount_code', 'ASC')->get();
        $discount_arr = [];
        foreach($discounts as $discount) {
            $discount_arr[$discount->id] = $discount->discount_code;
        }

        $companies = Company::orderBy('name', 'DESC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('accounts.edit')->with([
            'account' => $account,
            'discounts' => $discount_arr,
            'companies' => $companies_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountRequest $request, $id)
    {
        $account = Account::findOrFail($id);
        $account_name = '['.$account->account_code.'] '.$account->account_name;
        $account->update([
            'company_id' => $request->company_id,
            'discount_id' => $request->discount_id,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'short_name' => $request->short_name,
        ]);

        return back()->with([
            'message_success' => 'Account '.$account_name.' was updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        //
    }
}
