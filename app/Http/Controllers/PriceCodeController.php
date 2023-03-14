<?php

namespace App\Http\Controllers;

use App\Models\PriceCode;
use App\Models\Company;
use App\Models\Product;
use App\Http\Requests\StorePriceCodeRequest;
use App\Http\Requests\UpdatePriceCodeRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PriceCodeImport;

use App\Http\Traits\GlobalTrait;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class PriceCodeController extends Controller
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

        $code = trim($request->get('code'));
        
        $codes = PriceCode::select('code')->distinct()->get('code');
        $code_arr[''] = 'ALL';
        foreach($codes as $data) {
            $code_arr[$data->code] = $data->code;
        }

        $price_codes = PriceCode::PriceCodeSearch($search, $code, $this->setting->data_per_page);
        return view('price-codes.index')->with([
            'price_codes' => $price_codes,
            'search' => $search,
            'codes' => $code_arr,
            'code' => $code
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

        $products = Product::orderBy('stock_code', 'ASC')->get();
        $products_arr = [];
        foreach($products as $product) {
            $products_arr[$product->id] = '['.$product->stock_code.'] '.$product->description .' '.$product->size;
        }

        $price_basis = PriceCode::select('price_basis')->distinct()->get();
        $price_basis_arr = [];
        $price_basis_data = [
            'S' => 'Stock',
            'A' => 'Order',
            'O' => 'Other'  
        ];
        foreach($price_basis as $basis) {
            $price_basis_arr[$basis->price_basis] = $basis->price_basis.' - '.$price_basis_data[$basis->price_basis];
        }

        return view('price-codes.create')->with([
            'companies' => $companies_arr,
            'products' => $products_arr,
            'price_basis' => $price_basis_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePriceCodeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePriceCodeRequest $request)
    {
        $price_code = new PriceCode([
            'company_id' => $request->company_id,
            'product_id' => $request->product_id,
            'code' => $request->code,
            'selling_price' => $request->selling_price,
            'price_basis' => $request->price_basis,
        ]);
        $price_code->save();

        // logs
        activity('create')
        ->performedOn($price_code)
        ->log(':causer.firstname :causer.lastname has created price code :subject.code');

        return redirect()->route('price-code.index')->with([
            'message_success' => 'Price Code '.$price_code->code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PriceCode  $priceCode
     * @return \Illuminate\Http\Response
     */
    public function show(PriceCode $priceCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PriceCode  $priceCode
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $price_code = PriceCode::findOrFail($id);

        $companies = Company::orderBy('name', 'ASC')->get();
        $companies_arr = [];
        foreach($companies as $company) {
            $companies_arr[$company->id] = $company->name;
        }

        $products = Product::orderBy('stock_code', 'ASC')->get();
        $products_arr = [];
        foreach($products as $product) {
            $products_arr[$product->id] = $product->stock_code;
        }

        return view('price-codes.edit')->with([
            'price_code' => $price_code,
            'companies' => $companies_arr,
            'products' => $products_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePriceCodeRequest  $request
     * @param  \App\Models\PriceCode  $priceCode
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePriceCodeRequest $request, $id)
    {
        $price_code = PriceCode::findOrFail($id);
        $code = $price_code->code;

        $changes_arr['old'] = $price_code->getOriginal();

        $price_code->update([
            'company_id' => $request->company_id,
            'product_id' => $request->product_id,
            'code' => $request->code,
            'selling_price' => $request->selling_price,
            'price_basis' => $request->price_basis,
        ]);

        $changes_arr['changes'] = $price_code->getChanges();

        // logs
        activity('update')
        ->performedOn($price_code)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated price code :subject.code .');

        return back()->with([
            'message_success' => 'Price Code '.$code.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PriceCode  $priceCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(PriceCode $priceCode)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new PriceCodeImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded price code');

        return back()->with([
            'message_success' => 'Price Codes has been uploaded.'
        ]);
    }
}
