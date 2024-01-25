<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\Product;
use App\Models\SalesOrderCutOff;
use App\Models\PriceCode;
use App\Models\Discount;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ActivityPlanImport;

use PhpOffice\PhpSpreadsheet\Shared\Date;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

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

            $date = time();

            // check if theres cut-off today
            $cut_off = SalesOrderCutOff::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

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
        
        $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
        if(!empty($logged_account->account->po_process_date) && $logged_account->account->po_process_date >= 3) {
            $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$logged_account->account->po_process_date.' days'));
        }

        return view('sales-orders.create')->with([
            'control_number' => $control_number,
            'logged_account' => $logged_account,
            'process_ship_date' => $process_ship_date
        ]);
    }

    // resubmit sales order
    public function resubmit($id) {
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

        $sales_order = SalesOrder::findOrFail($id);

        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        Session::forget('order_data');

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
        $order_data['po_value'] = '';

        Session::put('order_data', $order_data);

        $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
        if(!empty($logged_account->account->po_process_date) && $logged_account->account->po_process_date >= 3) {
            $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$logged_account->account->po_process_date.' days'));
        }

        return view('sales-orders.create')->with([
            'control_number' => $control_number,
            'logged_account' => $logged_account,
            'process_ship_date' => $process_ship_date,
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
        // check
        $check = SalesOrder::where('control_number', $request->control_number)->first();
        if(!empty($check)) {
            $date_code = date('Ymd', time());
            $control_number = $request->control_number;
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
                $request->control_number = implode('-', $control_number_arr);
            }
        }

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
            'po_value' => $order_data['po_value'] ?? 0
        ]);
        $sales_order->save();

        $num = 0;
        $part = 1;
        $limit = $account->company->order_limit ?? $this->setting->sales_order_limit;
        // CUSTOM LIMIT FOR WATSON
        if($account->short_name == 'WATSONS') {
            $curr_limit = 23;
        } else {
            $curr_limit = $limit;
        }
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

        // if($sales_order->status == 'finalized') {
        //     $this->generateXml($sales_order);
        // }

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
        if($sales_order->status == 'for optimization') {
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
            'po_value' => $order_data['po_value'] ?? 0,
        ]);
        
        $num = 0;
        $part = 1;
        $limit = $logged_account->account->company->order_limit ?? $this->setting->sales_order_limit;
        $curr_limit = $limit;
        // CUSTOM LIMIT FOR WATSON
        if($logged_account->account->short_name == 'WATSONS') {
            $curr_limit = 23;
        } else {
            $curr_limit = $limit;
        }

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
            $this->generateXml($sales_order);

            return redirect()->route('sales-order.index')->with([
                'message_success' => 'Sales order '.$sales_order->control_number.' was updated.'
            ]);
        }
        
    }

    public function upload(Request $request) {
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        $request->validate([
            'upload_file' => [
                'mimes:xlsx,xls',
                'required'
            ]
        ]);

        $po_number = '';
        $paf_number = '';
        $ship_date = '';
        $shipping_instruction = '';
        $ship_to_address_id = 'default';
        $po_value = '';

        $ship_to_name = $logged_account->account->account_name;
        $ship_to_address_1 = $logged_account->account->ship_to_address1;
        $ship_to_address_2 = $logged_account->account->ship_to_address2;
        $ship_to_address_3 = $logged_account->account->ship_to_address3;
        $postal_code = $logged_account->account->postal_code;

        $path1 = $request->file('upload_file')->store('sales_order');
        $path = storage_path('app').'/'.$path1;
        $imports = Excel::toArray(new ActivityPlanImport, $path);
        $data = array();
        $row_num = 0;
        foreach($imports[0] as $row) {
            $row_num++;
            
            // PO NUMBER
            if($row_num == 1 && $row[0] == 'PO NUMBER') {
                $po_number = $row[1];
            }
            // PAF NUMBER
            if($row_num == 2 && $row[0] == 'PAF NUMBER') {
                $paf_number = $row[1];
            }
            // SHIP DATE
            if($row_num == 3 && $row[0] == 'SHIP DATE') {
                $ship_date = $row[1];
                if (is_int($ship_date)) {
                    // Convert the value to a date instance if it looks like a date.
                    $ship_date = Date::excelToDateTimeObject($ship_date)->format('Y-m-d');
                }
            }
            // SHIPPING INSTRUCTIONS
            if($row_num == 4 && $row[0] == 'SHIPPING INSTRUCTION') {
                $shipping_instruction = $row[1];
            }
            // SHIP TO ADDRESS
            if($row_num == 5 && $row[0] == 'SHIP TO ADDRESS') {
                if(!empty($row[1])) {
                    // check shipping address
                    $shipping_address = $logged_account->account->shipping_addresses()->where('address_code', $row[1])->first();
                    if(!empty($shipping_address)) {
                        $ship_to_address_id = $shipping_address->id;
                        $ship_to_name = $shipping_address->address_code.' - '.$shipping_address->ship_to_name;
                        $ship_to_address_1 = $shipping_address->building;
                        $ship_to_address_2 = $shipping_address->street;
                        $ship_to_address_3 = $shipping_address->city;
                        $postal_code = $shipping_address->postal;
                    }
                }
            }
            // PO VALUE
            if($row_num == 6 && $row[0] == 'PO VALUE') {
                $po_value = $row[1];
            }

            if($row_num > 7) {
                if(!empty($row[0])) {
                    $product = Product::where('stock_code', $row[0])
                        ->first();

                    if(!empty($product)) {
                        $data[$product->id] = [
                            'product' => $product,
                            'stock_code' => $row[0],
                            'description' => $product->description,
                            'size' => $product->size,
                            'data' => [
                                $row[1] => [
                                    'quantity' => $row[2],
                                    'total' => 0,
                                    'discount' => 0,
                                    'discounted' => 0,
                                ]
                            ],
                            'product_total' => 0,
                            'product_quantity' => 0,
                        ];
                    }

                }
            }

        }

        $order_data = $this->processData($data);

        $order_data['po_value'] = $po_value;

        Session::put('order_data', $order_data);

        return redirect()->route('sales-order.create')->with([
            'po_number' => $po_number,
            'paf_number' => $paf_number,
            'ship_date' => $ship_date,
            'shipping_instruction' => $shipping_instruction,
            'shipping_address_id' => $ship_to_address_id,
            'po_value' => $po_value ?? 0,
            'ship_to_name' => $ship_to_name,
            'ship_to_address1' => $ship_to_address_1,
            'ship_to_address2' => $ship_to_address_2,
            'ship_to_address3' => $ship_to_address_3,
            'postal_code' => $postal_code,
        ]);
    }
    
    private function processData($data) {
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        $discount = $logged_account->account->discount;

        // process data
        $orders = [];
        $total = 0;
        $total_quantity = 0;
        if(!empty($data)) {

            $line_discount = Discount::where('discount_code', $logged_account->account->line_discount_code)
                ->where('company_id', $logged_account->account->company_id)
                ->first();

            foreach($data as $product_id => $details) {
                $product = $details['product'];
                $orders['items'][$product_id] = [
                    'stock_code' => $product->stock_code,
                    'description' => $product->description,
                    'size' => $product->size,
                ];

                // check price code
                if($product->special_product) {
                    $special_product = $logged_account->account->products()
                        ->where('product_id', $product->id)
                        ->first();

                    $code = $special_product->pivot->price_code ?? $logged_account->account->price_code;
                } else {
                    $code = $logged_account->account->price_code;
                }

                $price_code = PriceCode::where('company_id', $logged_account->account->company_id)
                    ->where('product_id', $product->id)
                    ->where('code', $code)
                    ->first();

                $product_total = 0;
                $product_quantity = 0;
                if(!empty($price_code)) {
                    foreach($details['data'] as $uom => $val) {
                        // get price
                        $selling_price = $price_code->selling_price;
                        $price_basis = $price_code->price_basis;

                        // convert selling price to stock uom price
                        if($price_basis == 'A') {
                            if($product->order_uom_operator == 'M') { // Multiply
                                $selling_price = $selling_price / $product->order_uom_conversion;
                            }
                            if($product->order_uom_operator == 'D') { // Divide
                                $selling_price = $selling_price * $product->order_uom_conversion;
                            }
                        } else if($price_basis == 'O') {
                            // check operation
                            if($product->other_uom_operator == 'M') { // Multiply
                                $selling_price = $selling_price / $product->other_uom_conversion;
                            }
                            if($product->other_uom_operator == 'D') { // Divide
                                $selling_price = $selling_price * $product->other_uom_conversion;
                            }
                        }

                        $quantity = (float)$val['quantity'];

                        // check account sales order UOM
                        if(!empty($logged_account->account->sales_order_uom) && $uom != $logged_account->account->sales_order_uom) {
                            if($product->order_uom == $logged_account->account->sales_order_uom && $uom != $product->order_uom) {
                                if($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                } elseif($uom == $product->other_uom) {
                                    // check operation
                                    if($product->other_uom_operator == 'M') { // Multiply
                                        // convert to stock uom first
                                        $quantity = $quantity * $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    } elseif($product->other_uom_operator == 'D') { // Divide
                                        // convert to stock uom first
                                        $quantity = $quantity / $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->order_uom;
                            } else if($product->other_uom == $logged_account->account->sales_order_uom && $uom != $product->other_uom) {
                                if($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                } else if($uom == $product->order_uom) {
                                    if($product->order_uom_operator == 'M') {
                                        // convert to stock uom
                                        $quantity = $quantity * $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    } elseif($product->order_uom_operator == 'D') {
                                        $quantity = $quantity / $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->other_uom;
                            } else if($product->stock_uom == $logged_account->account->sales_order_uom && $uom != $product->stock_uom) {
                                if($uom == $product->order_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = false);
                                } else if($uom == $product->other_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = false);
                                }

                                $uom = $product->stock_uom;
                            }
                        }

                        // get total
                        $uom_total = 0;
                        if(strtoupper($uom) == strtoupper($product->stock_uom)) {
                            $uom_total += $quantity * $selling_price;
                        } else if($uom == $product->order_uom) { // order UOM
                            // check operator
                            if($product->order_uom_operator == 'M') { // Multiply
                                $uom_total += ($quantity * $product->order_uom_conversion) * $selling_price;
                            }
                            if($product->order_uom_operator == '') { // Divide
                                $uom_total += ($quantity / $product->order_uom_conversion) * $selling_price;
                            }
                        } else if($uom == $product->other_uom) { // other UOM
                            // check operator
                            if($product->other_uom_operator == 'M') { // Multiply
                                $uom_total += ($quantity * $product->other_uom_conversion) * $selling_price;
                            }
                            if($product->other_uom_operator == 'D') { // Divide
                                $uom_total += ($quantity / $product->other_uom_conversion) * $selling_price;
                            }
                        }

                        // apply line discount
                        $uom_discounted = $uom_total;
                        if(!empty($line_discount)) {
                            $discounted = $total;
                            if($line_discount->discount_1 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_1) / 100);
                            }
                            if($line_discount->discount_2 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_2) / 100);
                            }
                            if($line_discount->discount_3 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_3) / 100);
                            }
                        }

                        if($uom_total > 0) {
                            $orders['items'][$product->id]['data'][$uom] = [
                                'quantity' => $quantity,
                                'total' => $uom_total,
                                'discount' => $line_discount->description ?? '0',
                                'discounted' => $uom_discounted,
                            ];
                        }

                        $product_total += $uom_discounted;
                        $product_quantity += $quantity;
                    }
                }

                if($product_total > 0) {
                    $orders['items'][$product->id]['product_total'] = $product_total;
                    $orders['items'][$product->id]['product_quantity'] = $product_quantity;
                } else {
                    unset($orders['items'][$product->id]);
                }
                
                $total += $product_total;
                $total_quantity += $product_quantity;
            }
        }

        // apply inventory discount
        $discounted = $total;
        if(!empty($discount)) {
            if($discount->discount_1 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_1) / 100);
            }
            if($discount->discount_2 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_2) / 100);
            }
            if($discount->discount_3 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_3) / 100);
            }
        }

        $orders['total_quantity'] = $total_quantity;
        $orders['total'] = $total;
        $orders['discount_id'] = $discount->id ?? NULL;
        $orders['grand_total'] = $discounted;
        $orders['po_value'] = '';

        return $orders;
    }

    private function quantityConversion($quantity, $conversion, $operator, $reverse = false) {
        if($operator == 'M') { // mutiply
            if($reverse) {
                return $quantity / $conversion;
            } else {
                return $quantity * $conversion;
            }
        } elseif($operator == 'D') { // divide
            if($reverse) {
                return $quantity * $conversion;
            } else {
                return $quantity / $conversion;
            }
        }

        return $quantity;
    }

    private function isExcelDate(Cell $cell) {
        return Date::isDateTime($cell);
    }

    public function generateXml($sales_order) {
        // $parts = $this->convertData($sales_order);
        // foreach($parts as $key => $data) {
        //     $part = $key + 1;
        //     $xml = $this->arrayToXml($data);
            
        //     // Save the XML to the storage disk (e.g., 'public', 'local', etc.)
        //     Storage::disk('public')->put('sales-orders/'.$sales_order->po_number.'-'.$part.'.xml', $xml);

        //     // change connection for each accounts
        //     if($sales_order->account_login->account->company->name == 'BEVI') {
        //         $ftp = Storage::disk('ftp_bevi');
        //         $ftp->put($sales_order->po_number.'-'.$part.'.xml', $xml);
        //     } else if($sales_order->account_login->account->company->name == 'BEVA') {
        //         $ftp = Storage::disk('ftp_beva');
        //         $ftp->put($sales_order->po_number.'-'.$part.'.xml', $xml);
        //     }
        // }

        // return $sales_order->po_number.'-'.$part.'.xml file created successfully.';
    }

    private function arrayToXml($data) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;  // Enable formatting and indentation

        $salesOrders = $dom->createElement('SalesOrders');
        $salesOrders->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema-instance');
        $salesOrders->setAttribute('xsd:noNamespaceSchemaLocation', 'SORTOIDOC.XSD');
        $dom->appendChild($salesOrders);

        $this->arrayToXmlHelper($data, $dom, $salesOrders);

        return $dom->saveXML();
    }

    private function arrayToXmlHelper($data, $dom, &$parent) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($key === 'StockLine') {
                    // Special handling for repeated 'StockLine' key within 'OrderDetails'
                    foreach ($value as $item) {
                        $subNode = $dom->createElement($key);
                        $parent->appendChild($subNode);
                        $this->arrayToXmlHelper($item, $dom, $subNode);
                    }
                } else {
                    $subNode = $dom->createElement($key);
                    $parent->appendChild($subNode);
                    $this->arrayToXmlHelper($value, $dom, $subNode);
                }
            } else {
                $child = $dom->createElement("$key", htmlspecialchars("$value"));
                $parent->appendChild($child);
            }
        }
    }

    private function convertData($sales_order) {

        $company = '';
        // CHECK CUSTOMER
        $customer = DB::connection('beva_db')
            ->table('ArCustomer')
            ->where('Customer', $sales_order->account_login->account->account_code)
            ->first();
        if(empty($customer)) {
            $customer = DB::connection('bevi_db')
                ->table('ArCustomer')
                ->where('Customer', $sales_order->account_login->account->account_code)
                ->first();

            $company = 'BEVI';
        } else {
            $company = 'BEVA';
        }

        $details = $sales_order->order_products;
        $parts = array_unique($details->pluck('part')->toArray());

        $so_parts = array();
        foreach($parts as $part) {
            $details = $details->where('part', $part);

            $trade_discounts = $this->getTradeDiscounts($company, $details, $customer);

            $data = [
                'Orders' => [
                    'OrderHeader' => [
                        'CustomerPoNumber'              => $sales_order->po_number.'-'.$part,
                        'Customer'                      => $sales_order->account_login->account->account_code,
                        'OrderDate'                     => $sales_order->order_date,
                        'ShippingInstrs'                => $sales_order->shipping_instruction ?? '',
                        'RequestedShipDate'             => $sales_order->ship_date ?? '',
                        'OrderComments'                 => $sales_order->control_number,
                        'OrderDiscPercent1'             => $trade_discounts[0],
                        'OrderDiscPercent2'             => $trade_discounts[0],
                        'OrderDiscPercent3'             => $trade_discounts[0],
                        'SalesOrderPromoQualityAction'  => 'W',
                        'SalesOrderPromoSelectAction'   => 'A',
                        'MultiShipCode'                 => $sales_order->shipping_address ?? '',
                    ],
                ]
            ];

            $num = 0;
            foreach($details as $detail) {
                foreach($detail->product_uoms as $uom) {
                    $num++;

                    $price_code = $this->getPriceCode($company, $detail->product->stock_code);

                    $data['Orders']['OrderDetails']['StockLine'][] = [
                        'CustomerPoLine'    => $num,
                        'Warehouse'         => $customer->SalesWarehouse,
                        'StockCode'         => $detail->product->stock_code,
                        'OrderQty'          => $uom->quantity,
                        'OrderUom'          => $uom->uom,
                        'PriceUom'          => $uom->uom,
                        'PriceCode'         => $price_code,
                    ];
                }
            }

            $so_parts[] = $data;
        }

        return $so_parts;
    }

    private function getTradeDiscounts($company, $details, $customer) {
        $trade_disc1 = '';
        $trade_disc2 = '';
        $trade_disc3 = '';
        if($company == 'BEVA') {
            $check = Product::where('product_class', 'CRS')
                ->whereIn('id', $details->pluck('product_id')->toArray())
                ->first();
            // get trade discount
            if($check) {
                $trade_disc1 = 30;
                $trade_disc2 = 0;
                $trade_disc3 = 0;
            } else {
                $check2 = Product::whereIn('stock_code', ['KS01046', 'KS01047'])
                    ->whereIn('id', $details->pluck('product_id')->toArray())
                    ->first();
                if($check2) {
                    $trade_disc1 = 12;
                    $trade_disc2 = 0;
                    $trade_disc3 = 0;
                }
            }
        } else {
            // get trade discount
            if($customer->Customer == '1200008') {
                $check = Product::where('product_class', 'DEF')
                    ->where('category', 'ALCOHOL')
                    ->whereIn('id', $details->pluck('product_id')->toArray())
                    ->first();

                if($check) {
                    $trade_disc1 = 15;
                }
            }
        }

        return [$trade_disc1, $trade_disc2, $trade_disc3];
    }

    private function getPriceCode($company, $stock_code) {
        $price_code = '';
        if($company == 'BEVA') {
            $product = DB::connection('beva_db')
                ->table('InvMaster')
                ->where('StockCode', $stock_code)
                ->first();

            // get price code
            if(!empty($product)) {
                switch($product->ProductClass) {
                    case 'BHW':
                        $price_code = 'A';
                        break;
                    case 'CRS':
                        $price_code = 'X';
                        break;
                }
            }
        } else { // BEVI
            $product = DB::connection('bevi_db')
                ->table('InvMaster')
                ->where('StockCode', $stock_code)
                ->first();
            // get price code
            if(!empty($product)) {
                switch($product->AlternateKey1) {
                    case 'PURIFIED WATER':
                        $price_code = 'A';
                        break;
                }
            }
        }

        return $price_code;
    }
}