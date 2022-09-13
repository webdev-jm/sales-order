<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Product;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $logged_account = auth()->user()->logged_account();
        if(!empty($logged_account)) {
            $sales_orders = SalesOrder::orderBy('po_number', 'DESC')
            ->whereHas('account_login', function($query) use($logged_account) {
                $query->where('account_id', $logged_account->account_id);
            })
            ->paginate(10);

            return view('sales-orders.index')->with([
                'sales_orders' => $sales_orders
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to accounts before creating sales order'
            ]);
        }
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $control_number = 'SO-'.date('Ymd', time()).'-1001';
        $logged_account = auth()->user()->logged_account();

        return view('sales-orders.create')->with([
            'control_number' => $control_number,
            'logged_account' => $logged_account
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalesOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesOrderRequest $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function show(SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesOrderRequest  $request
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesOrder $salesOrder)
    {
        //
    }
}
