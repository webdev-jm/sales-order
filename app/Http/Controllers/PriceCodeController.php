<?php

namespace App\Http\Controllers;

use App\Models\PriceCode;
use App\Http\Requests\StorePriceCodeRequest;
use App\Http\Requests\UpdatePriceCodeRequest;

class PriceCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePriceCodeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePriceCodeRequest $request)
    {
        //
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
    public function edit(PriceCode $priceCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePriceCodeRequest  $request
     * @param  \App\Models\PriceCode  $priceCode
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePriceCodeRequest $request, PriceCode $priceCode)
    {
        //
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
}
