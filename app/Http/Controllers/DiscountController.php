<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Discount;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DiscountImport;

use App\Http\Traits\GlobalTrait;

class DiscountController extends Controller
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
        $discounts = Discount::DiscountSearch($search, $this->setting->data_per_page);
        return view('discounts.index')->with([
            'discounts' => $discounts,
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
        $companies = Company::orderBy('name', 'ASC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }
        
        return view('discounts.create')->with([
            'companies' => $companies_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDiscountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDiscountRequest $request)
    {
        $discount = new Discount([
            'company_id' => $request->company_id,
            'discount_code' => $request->discount_code,
            'description' => $request->description,
            'discount_1' => $request->discount_1,
            'discount_2' => $request->discount_2,
            'discount_3' => $request->discount_3,
        ]);
        $discount->save();

        // logs
        activity('create')
        ->performedOn($discount)
        ->log(':causer.firstname :causer.lastname has created discount :subject.description');

        return redirect()->route('discount.index')->with([
            'message_success' => 'Discount '.$discount->discount_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $discount = Discount::findOrFail($id);

        $companies = Company::orderBy('name', 'ASC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        return view('discounts.edit')->with([
            'discount' => $discount,
            'companies' => $companies_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDiscountRequest  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDiscountRequest $request, $id)
    {
        $discount = Discount::findOrFail($id);
        $discount_code = $discount->discount_code;

        $changes_arr['old'] = $discount->getOriginal();

        $discount->update([
            'company_id' => $request->company_id,
            'discount_code' => $request->discount_code,
            'description' => $request->description,
            'discount_1' => $request->discount_1,
            'discount_2' => $request->discount_2,
            'discount_3' => $request->discount_3,
        ]);

        $changes_arr['changes'] = $discount->getChanges();

        // logs
        activity('update')
        ->performedOn($discount)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated discount :subject.description .');

        return redirect()->route('discount.index')->with([
            'message_success' => 'Discount '.$discount_code.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new DiscountImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded discounts');

        return back()->with([
            'message_success' => 'Discounts has been uploaded.'
        ]);
    }
}
