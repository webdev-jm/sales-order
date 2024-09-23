<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Brand;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Traits\GlobalTrait;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class ProductController extends Controller
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
        $products = Product::ProductSearch($search, $this->setting->data_per_page);
        return view('products.index')->with([
            'products' => $products,
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

        $brands = Brand::orderBy('brand', 'ASC')->get();
        $brands_arr = [];
        foreach($brands as $brand) {
            $brands_arr[$brand->id] = $brand->brand;
        }

        return view('products.create')->with([
            'companies' => $companies_arr,
            'brands_arr' => $brands_arr
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
        $status = $request->status == 'active' ? null : $request->status;
        $special_product = $request->special_product ?? false;

        $product = new Product([
            'brand_id' => $request->brand_id,
            'stock_code' => $request->stock_code,
            'description' => $request->description,
            'size' => $request->size,
            'category' =>  $request->category,
            'product_class' => $request->product_class,
            'brand' => $request->brand,
            'core_group' => $request->core_group,
            'stock_uom' => $request->stock_uom,
            'order_uom' => $request->order_uom,
            'other_uom' => $request->other_uom,
            'order_uom_conversion' => $request->order_uom_conversion,
            'other_uom_conversion' => $request->other_uom_conversion,
            'order_uom_operator' => $request->order_uom_operator,
            'other_uom_operator' => $request->other_uom_operator,
            'status' => $status,
            'special_product' => $special_product
        ]);
        $product->save();
        
        // logs
        activity('create')
        ->performedOn($product)
        ->log(':causer.firstname :causer.lastname has created product :subject.stock_code :subject.description');

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

        $brands = Brand::orderBy('brand', 'ASC')->get();
        $brands_arr = [];
        foreach($brands as $brand) {
            $brands_arr[$brand->id] = $brand->brand;
        }

        return view('products.edit')->with([
            'product' => $product,
            'brands_arr' => $brands_arr
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

        $changes_arr['old'] = $product->getOriginal();

        $status = $request->status == 'active' ? null : $request->status;
        $special_product = $request->special_product ?? false;

        $product->update([
            'brand_id' => $request->brand_id,
            'stock_code' => $request->stock_code,
            'description' => $request->description,
            'size' => $request->size,
            'category' =>  $request->category,
            'product_class' => $request->product_class,
            'brand' => $request->brand,
            'core_group' => $request->core_group,
            'stock_uom' => $request->stock_uom,
            'order_uom' => $request->order_uom,
            'other_uom' => $request->other_uom,
            'order_uom_conversion' => $request->order_uom_conversion,
            'other_uom_conversion' => $request->other_uom_conversion,
            'order_uom_operator' => $request->order_uom_operator,
            'other_uom_operator' => $request->other_uom_operator,
            'status' => $status,
            'special_product' => $special_product
        ]);

        $changes_arr['changes'] = $product->getChanges();

        // logs
        activity('update')
        ->performedOn($product)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated product :subject.stock_code :subject.description .');

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

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded product');

        return back()->with([
            'message_success' => 'Products has been uploaded.'
        ]);
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $response = Product::ProductAjax($search);
        return response()->json($response);
    }

    public function getAjax($id) {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }
}
