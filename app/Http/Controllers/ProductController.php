<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use App\Models\PriceCode;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::orderBy('id', 'DESC')->paginate(10)->onEachSide(1);
        return view('products.index')->with([
            'products' => $products
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

        return view('products.create')->with([
            'companies' => $companies_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $product = new Product([
            'stock_code' => $request->stock_code,
            'description' => $request->description,
            'size' => $request->size,
            'category' =>  $request->category,
            'product_class' => $request->product_class,
            'core_group' => $request->core_group,
            'uom' => $request->uom
        ]);
        $product->save();

        return redirect()->route('product.index')->with([
            'message_success' => 'Product '.$product->stock_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit')->with([
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product_code = $product->stock_code;
        $product->update([
            'stock_code' => $request->stock_code,
            'description' => $request->description,
            'size' => $request->size,
            'category' =>  $request->category,
            'product_class' => $request->product_class,
            'core_group' => $request->core_group,
            'uom' => $request->uom
        ]);

        return back()->with([
            'message_success' => 'Product '.$product_code.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new ProductImport, $request->upload_file);

        return back()->with([
            'message_success' => 'Products has been uploaded.'
        ]);
    }
}
