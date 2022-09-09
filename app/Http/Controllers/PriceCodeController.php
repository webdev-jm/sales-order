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

class PriceCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $price_codes = PriceCode::orderBy('code', 'ASC')->paginate(10);
        return view('price-codes.index')->with([
            'price_codes' => $price_codes
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
            $products_arr[$product->id] = $product->stock_code;
        }

        return view('price-codes.create')->with([
            'companies' => $companies_arr,
            'products' => $products_arr,
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
        $price_code->update([
            'company_id' => $request->company_id,
            'product_id' => $request->product_id,
            'code' => $request->code,
            'selling_price' => $request->selling_price,
            'price_basis' => $request->price_basis,
        ]);

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

        return back()->with([
            'message_success' => 'Price Codes has been uploaded.'
        ]);
    }
}
