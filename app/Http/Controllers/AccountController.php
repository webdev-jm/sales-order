<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Discount;
use App\Models\Company;
use App\Models\InvoiceTerm;
use App\Models\PriceCode;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountImport;

use App\Http\Traits\GlobalTrait;

class AccountController extends Controller
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
        $accounts = Account::AccountSearch($search, $this->setting->data_per_page);
        return view('accounts.index')->with([
            'accounts' => $accounts,
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
        $discounts = Discount::orderBy('company_id', 'ASC')->get();
        $discount_arr = [];
        foreach($discounts as $discount) {
            $discount_arr[$discount->id] = '['.$discount->company->name.'] '.$discount->discount_code.' - '.$discount->description;
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

        // logs
        activity('create')
        ->performedOn($account)
        ->log(':causer.firstname :causer.lastname has created account [ :subject.account_code ] :subject.account_name');

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
            $discount_arr[$discount->id] = '['.$discount->company->name.'] '.$discount->discount_code.' - '.$discount->description;
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

        $changes_arr['old'] = $account->getOriginal();

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
        
        $changes_arr['changes'] = $account->getChanges();

        // logs
        activity('update')
        ->performedOn($account)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated account [ :subject.account_code ] :subject.account_name .');

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

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new AccountImport, $request->upload_file);

        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded accounts');

        return back()->with([
            'message_success' => 'Accounts has been uploaded.'
        ]);
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $response = Account::AccountAjax($search);
        return response()->json($response);
    }

    public function getAjax($id) {
        $account = Account::findOrFail($id);
        return response()->json($account);
    }
}
