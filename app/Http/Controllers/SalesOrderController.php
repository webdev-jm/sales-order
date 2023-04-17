<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\Product;
use App\Models\SalesOrderCutOff;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

use Illuminate\Support\Facades\Session;

class SalesOrderController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function list(Request $request) {
        $search = trim($request->get('search'));
        $status = trim($request->get('status'));
        $order_date = trim($request->get('order-date'));

        if($search != '' || $status != '' || $order_date != '') {
            $query = SalesOrder::orderBy('control_number', 'DESC');
            if($search != '') {
                $query->where(function($qry) use ($search) {
                    $qry->where('control_number', 'like', '%'.$search.'%')
                    ->orWhere('po_number', 'like', '%'.$search.'%')
                    ->orWhere('order_date', 'like', '%'.$search.'%')
                    ->orWhere('ship_date', 'like', '%'.$search.'%')
                    ->orWhere('ship_to_name', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhere('reference', 'like', '%'.$search.'%');
                })
                ->orWhereHas('account_login', function($qry) use($search) {
                    $qry->whereHas('account', function($qry1) use($search) {
                        $qry1->where('account_code', 'like', '%'.$search.'%')
                        ->orWhere('short_name', 'like', '%'.$search.'%');
                    })->orWhereHas('user', function($qry1) use ($search) {
                        $qry1->where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%');
                    });
                });
            }

            if($status != '') {
                if($status == 'uploaded') {
                    $query->where('upload_status', 1);
                } else if($status == 'upload_error') {
                    $query->where('upload_status', 0);
                } else {
                    $query->where('status', $status)->whereNull('upload_status');
                }
            }

            if($order_date != '') {
                $query->where('order_date', $order_date);
            }

            $sales_orders = $query->paginate($this->setting->data_per_page)->onEachSide(1)->appends(request()->query());
        } else {
            $sales_orders = SalesOrder::orderBy('control_number', 'DESC')
            ->whereHas('account_login', function($qry) use($search) {
                $qry->whereHas('account', function($qry1) use($search) {
                });
            })
            ->paginate($this->setting->data_per_page)->onEachSide(1)->appends(request()->query());
        }

        // $sales_orders = SalesOrder::SalesOrderListSearch($search, $this->setting->data_per_page);

        return view('sales-orders.list')->with([
            'search' => $search,
            'status' => $status,
            'order_date' => $order_date,
            'sales_orders' => $sales_orders
        ]);
    }

    public function dashboard() {
        return view('sales-orders.dashboard');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $logged_account = Session::get('logged_account');
        $search = trim($request->get('search'));
        if(isset($logged_account)) {

            Session::forget('order_data');

            $date = date('Y-m-d');

            // check if theres cut-off today
            $cut_off = SalesOrderCutOff::orderBy('time', 'ASC')
            ->where('date', $date)->first();

            $sales_orders = SalesOrder::SalesOrderSearch($search, $logged_account,$this->setting->data_per_page);
            return view('sales-orders.index')->with([
                'sales_orders' => $sales_orders,
                'search' => $search,
                'cut_off' => $cut_off,
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
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
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        $date_code = date('Ymd', time());
        $control_number = 'SO-'.$date_code.'-001';
        $sales_order = SalesOrder::withTrashed()->orderBy('control_number', 'DESC')->first();
        if(!empty($sales_order)) {
            // increment control number
            $control_number_arr = explode('-', $sales_order->control_number);
            $last = end($control_number_arr);
            array_pop($control_number_arr);
            $prev_date = end($control_number_arr);
            array_pop($control_number_arr);
            if($date_code == $prev_date) { // same day increment number
                $number = (int)$last + 1;
            } else { // reset on different day
                $number = 1;
            }
            for($i = strlen($number);$i <= 2; $i++) {
                $number = '0'.$number;
            }
            array_push($control_number_arr, $date_code);
            array_push($control_number_arr, $number);
            $control_number = implode('-', $control_number_arr);
        }

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
        $logged_account = Session::get('logged_account');
        $account = $logged_account->account;

        $order_data = Session::get('order_data');

        if(empty($order_data['items'])) {
            return back()->with([
                'message_error' => 'Please add items first.',
                'shipping_address_id' => $request->shipping_address_id,
                'control_number' => $request->control_number,
                'po_number' => $request->po_number,
                'paf_number' => $request->paf_number,
                'order_date' => $request->order_date,
                'ship_date' => $request->ship_date,
                'shipping_instruction' => $request->shipping_instruction,
                'ship_to_name' => $request->ship_to_name,
                'ship_to_building' => $request->ship_to_address1,
                'ship_to_street' => $request->ship_to_address2,
                'ship_to_city' => $request->ship_to_address3,
                'ship_to_postal' => $request->postal_code,
            ]);
        }

        if(empty($order_data['po_value']) || $order_data['po_value'] <= 0) {
            return back()->with([
                'message_error' => 'PO value is required.',
                'shipping_address_id' => $request->shipping_address_id,
                'control_number' => $request->control_number,
                'po_number' => $request->po_number,
                'paf_number' => $request->paf_number,
                'order_date' => $request->order_date,
                'ship_date' => $request->ship_date,
                'shipping_instruction' => $request->shipping_instruction,
                'ship_to_name' => $request->ship_to_name,
                'ship_to_building' => $request->ship_to_address1,
                'ship_to_street' => $request->ship_to_address2,
                'ship_to_city' => $request->ship_to_address3,
                'ship_to_postal' => $request->postal_code,
            ]);
        }

        $shipping_address_id = $request->shipping_address_id == 'default' ? NULL : $request->shipping_address_id;

        $sales_order = new SalesOrder([
            'account_login_id' => $logged_account->id,
            'shipping_address_id' => $shipping_address_id,
            'control_number' => $request->control_number,
            'po_number' => $request->po_number,
            'paf_number' => $request->paf_number,
            'order_date' => $request->order_date,
            'ship_date' => $request->ship_date,
            'shipping_instruction' => $request->shipping_instruction,
            'ship_to_name' => $request->ship_to_name,
            'ship_to_building' => $request->ship_to_address1,
            'ship_to_street' => $request->ship_to_address2,
            'ship_to_city' => $request->ship_to_address3,
            'ship_to_postal' => $request->postal_code,
            'status' => $request->status,
            'total_quantity' => $order_data['total_quantity'],
            'total_sales' => $order_data['total'],
            'grand_total' => $order_data['grand_total'],
            'po_value' => $order_data['po_value']
        ]);
        $sales_order->save();

        $num = 0;
        $part = 1;
        $limit = $account->company->order_limit ?? $this->setting->sales_order_limit;
        $curr_limit = $limit;
        foreach($order_data['items'] as $product_id => $items) {
            $num++;

            // divide by parts
            if($num > $curr_limit) {
                $curr_limit += $limit;
                $part++;
            }

            $sales_order_product = new SalesOrderProduct([
                'sales_order_id' => $sales_order->id,
                'product_id' => $product_id,
                'part' => $part,
                'total_quantity' => $items['product_quantity'],
                'total_sales' => $items['product_total'],
            ]);
            $sales_order_product->save();

            foreach($items['data'] as $uom => $data) {
                $sales_order_product_uom = new SalesOrderProductUom([
                    'sales_order_product_id' => $sales_order_product->id,
                    'uom' => $uom,
                    'quantity' => $data['quantity'],
                    'uom_total' => $data['total'],
                    'uom_total_less_disc' => $data['discounted']
                ]);
                $sales_order_product_uom->save();
            }
        }

        // logs
        activity('create')
        ->performedOn($sales_order)
        ->log(':causer.firstname :causer.lastname has created sales order :subject.control_number :subject.po_number');

        return redirect()->route('sales-order.index')->with([
            'message_success' => 'Sales Order '.$sales_order->control_number.' was created'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sales_order = SalesOrder::findOrFail($id);
        $parts = SalesOrderProduct::select('part')->distinct()->where('sales_order_id', $sales_order->id)->get('part');

        $reference_arr = explode(' ,', $sales_order->reference);
        
        return view('sales-orders.show')->with([
            'sales_order' => $sales_order,
            'parts' => $parts,
            'reference_arr' => $reference_arr
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        $sales_order = SalesOrder::findOrFail($id);

        // check if already finalized
        if($sales_order->status == 'finalized') {
            return redirect()->route('sales-order.show', $sales_order->id)->with([
                'message_error' => 'SO cannot be edited once status has been finalized.'
            ]);
        }

        $order_data = [];
        $order_products = $sales_order->order_products;
        foreach($order_products as $order_product) {
            $product = $order_product->product;
            $order_data['items'][$order_product->product_id] = [
                'stock_code' => $product->stock_code,
                'description' => $product->description,
                'size' => $product->size
            ];

            $product_uoms = $order_product->product_uoms;
            foreach($product_uoms as $uom) {
                $order_data['items'][$order_product->product_id]['data'][$uom->uom] = [
                    'quantity' => $uom->quantity,
                    'total' => $uom->uom_total,
                    'discount' => 0,
                    'discounted' => $uom->uom_total_less_disc
                ];
            }
            $order_data['items'][$order_product->product_id]['product_total'] = $order_product->total_sales;
            $order_data['items'][$order_product->product_id]['product_quantity'] = $order_product->total_quantity;
        }

        $order_data['total_quantity'] = $sales_order->total_quantity;
        $order_data['total'] = $sales_order->total_sales;
        $order_data['grand_total'] = $sales_order->grand_total;
        $order_data['po_value'] = $sales_order->po_value;

        Session::put('order_data', $order_data);

        return view('sales-orders.edit')->with([
            'sales_order' => $sales_order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesOrderRequest  $request
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesOrderRequest $request, $id)
    {
        $logged_account = Session::get('logged_account');
        $order_data = Session::get('order_data');

        if(empty($order_data['items'])) {
            return back()->with([
                'message_error' => 'Please add items first.',
                'shipping_address_id' => $request->shipping_address_id,
                'po_number' => $request->po_number,
                'paf_number' => $request->paf_number,
                'ship_date' => $request->ship_date,
                'shipping_instruction' => $request->shipping_instruction,
                'ship_to_name' => $request->ship_to_name,
                'ship_to_building' => $request->ship_to_address1,
                'ship_to_street' => $request->ship_to_address2,
                'ship_to_city' => $request->ship_to_address3,
                'ship_to_postal' => $request->postal_code,
            ]);
        }

        if(empty($order_data['po_value']) || $order_data['po_value'] <= 0) {
            return back()->with([
                'message_error' => 'PO value is required.',
                'shipping_address_id' => $request->shipping_address_id,
                'po_number' => $request->po_number,
                'paf_number' => $request->paf_number,
                'ship_date' => $request->ship_date,
                'shipping_instruction' => $request->shipping_instruction,
                'ship_to_name' => $request->ship_to_name,
                'ship_to_building' => $request->ship_to_address1,
                'ship_to_street' => $request->ship_to_address2,
                'ship_to_city' => $request->ship_to_address3,
                'ship_to_postal' => $request->postal_code,
            ]);
        }

        $shipping_address_id = $request->shipping_address_id == 'default' ? NULL : $request->shipping_address_id;

        $sales_order = SalesOrder::findOrFail($id);

        $changes_arr['old'] = $sales_order->getOriginal();

        $sales_order->update([
            'shipping_address_id' => $shipping_address_id,
            'po_number' => $request->po_number,
            'paf_number' => $request->paf_number,
            'ship_date' => $request->ship_date,
            'shipping_instruction' => $request->shipping_instruction,
            'ship_to_name' => $request->ship_to_name,
            'ship_to_building' => $request->ship_to_address1,
            'ship_to_street' => $request->ship_to_address2,
            'ship_to_city' => $request->ship_to_address3,
            'ship_to_postal' => $request->postal_code,
            'status' => $request->status,
            'total_quantity' => $order_data['total_quantity'],
            'total_sales' => $order_data['total'],
            'grand_total' => $order_data['grand_total'],
            'po_value' => $order_data['po_value'],
        ]);
        
        $num = 0;
        $part = 1;
        $limit = $logged_account->account->company->order_limit ?? $this->setting->sales_order_limit;
        $curr_limit = $limit;
        $sales_order->order_products()->forceDelete();
        
        foreach($order_data['items'] as $product_id => $items) {
            $num++;

            // divide by parts
            if($num > $curr_limit) {
                $curr_limit += $limit;
                $part++;
            }

            $sales_order_product = new SalesOrderProduct([
                'sales_order_id' => $sales_order->id,
                'product_id' => $product_id,
                'part' => $part,
                'total_quantity' => $items['product_quantity'],
                'total_sales' => $items['product_total'],
            ]);
            $sales_order_product->save();

            foreach($items['data'] as $uom => $data) {
                $sales_order_product_uom = new SalesOrderProductUom([
                    'sales_order_product_id' => $sales_order_product->id,
                    'uom' => $uom,
                    'quantity' => $data['quantity'],
                    'uom_total' => $data['total'],
                    'uom_total_less_disc' => $data['discounted']
                ]);
                $sales_order_product_uom->save();
            }
        }

        $changes_arr['changes'] = $sales_order->getChanges();

        // logs
        activity('update')
        ->performedOn($sales_order)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated sales order :subject.control_number :subject.po_number .');

        if($sales_order->status == 'draft') {
            return back()->with([
                'message_success' => 'Sales order '.$sales_order->control_number.' was updated.'
            ]);
        } else {
            return redirect()->route('sales-order.index')->with([
                'message_success' => 'Sales order '.$sales_order->control_number.' was updated.'
            ]);
        }
        
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
