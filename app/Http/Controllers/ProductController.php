<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::orderBy('id', 'DESC')->paginate(10);
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
        return view('products.create');
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
            'brand' => $request->brand,
            'alternative_code' => $request->alternative_code,
            'stock_uom1' => $request->stock_uom1,
            'stock_uom2' => $request->stock_uom2,
            'stock_uom3' => $request->stock_uom3,
            'uom_price1' => $request->uom_price1,
            'uom_price2' => $request->uom_price2,
            'uom_price3' => $request->uom_price3,
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
            'brand' => $request->brand,
            'alternative_code' => $request->alternative_code,
            'stock_uom1' => $request->stock_uom1,
            'stock_uom2' => $request->stock_uom2,
            'stock_uom3' => $request->stock_uom3,
            'uom_price1' => $request->uom_price1,
            'uom_price2' => $request->uom_price2,
            'uom_price3' => $request->uom_price3,
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
}
