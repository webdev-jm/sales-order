<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Discount;
use App\Models\Company;
use App\Models\InvoiceTerm;
use App\Models\PriceCode;
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
        $discounts = Discount::orderBy('company_id', 'ASC')->get();
        $discount_arr = [];
        foreach($discounts as $discount) {
            $discount_arr[$discount->id] = '['.$discount->company->name.'] '.$discount->discount_code;
        }

        $companies = Company::orderBy('name', 'DESC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        $price_codes = PriceCode::select('code')->distinct()->get();
        $price_codes_arr = [];
        foreach($price_codes as $price_code) {
            $price_codes_arr[$price_code->code] = $price_code->code;
        }

        $invoice_terms = InvoiceTerm::orderBy('term_code', 'ASC')->get();
        $invoice_terms_arr = [];
        foreach($invoice_terms as $invoice_term) {
            $invoice_terms_arr[$invoice_term->id] = '['.$invoice_term->term_code.'] '.$invoice_term->description;
        }

        return view('accounts.create')->with([
            'discounts' => $discount_arr,
            'companies' => $companies_arr,
            'price_codes' => $price_codes_arr,
            'invoice_terms' => $invoice_terms_arr,
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
            'invoice_term_id' => $request->invoice_term_id,
            'company_id' => $request->company_id,
            'discount_id' => $request->discount_id,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'short_name' => $request->short_name,
            'price_code' => $request->price_code,
            'ship_to_address1' => $request->ship_to_address1,
            'ship_to_address2' => $request->ship_to_address2,
            'ship_to_address3' => $request->ship_to_address3,
            'postal_code' => $request->postal_code,
            'tax_number' => $request->tax_number,
            'on_hold' => $request->on_hold,
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
        $discounts = Discount::orderBy('company_id', 'ASC')->get();
        $discount_arr = [];
        foreach($discounts as $discount) {
            $discount_arr[$discount->id] = '['.$discount->company->name.'] '.$discount->discount_code;
        }

        $companies = Company::orderBy('name', 'DESC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        $price_codes = PriceCode::select('code')->distinct()->get();
        $price_codes_arr = [];
        foreach($price_codes as $price_code) {
            $price_codes_arr[$price_code->code] = $price_code->code;
        }

        $invoice_terms = InvoiceTerm::orderBy('term_code', 'ASC')->get();
        $invoice_terms_arr = [];
        foreach($invoice_terms as $invoice_term) {
            $invoice_terms_arr[$invoice_term->id] = '['.$invoice_term->term_code.'] '.$invoice_term->description;
        }

        return view('accounts.edit')->with([
            'account' => $account,
            'discounts' => $discount_arr,
            'companies' => $companies_arr,
            'price_codes' => $price_codes_arr,
            'invoice_terms' => $invoice_terms_arr
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
            'invoice_term_id' => $request->invoice_term_id,
            'company_id' => $request->company_id,
            'discount_id' => $request->discount_id,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'short_name' => $request->short_name,
            'price_code' => $request->price_code,
            'ship_to_address1' => $request->ship_to_address1,
            'ship_to_address2' => $request->ship_to_address2,
            'ship_to_address3' => $request->ship_to_address3,
            'postal_code' => $request->postal_code,
            'tax_number' => $request->tax_number,
            'on_hold' => $request->on_hold,
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
