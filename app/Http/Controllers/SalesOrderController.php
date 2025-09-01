<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\SalesOrderProductUomPaf;
use App\Models\Product;
use App\Models\SalesOrderCutOff;
use App\Models\PriceCode;
use App\Models\Discount;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

use Illuminate\Support\Facades\Session;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ActivityPlanImport;
use App\Exports\SalesOrderExport;

use PhpOffice\PhpSpreadsheet\Shared\Date;

use App\Http\Traits\SoXmlTrait;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class SalesOrderController extends Controller
{
    use GlobalTrait;
    use SoXmlTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function list(Request $request) {
        $search = trim($request->get('search'));
        $status = trim($request->get('status'));
        $order_date = trim($request->get('order-date'));

        $sales_orders = SalesOrder::query()
            ->orderByDesc('control_number')
            // Filter by related account or user using search
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // Search on SalesOrder fields
                    $q->where('control_number', 'like', "%{$search}%")
                        ->orWhere('po_number', 'like', "%{$search}%")
                        ->orWhere('order_date', 'like', "%{$search}%")
                        ->orWhere('ship_date', 'like', "%{$search}%")
                        ->orWhere('ship_to_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");

                    // Search on related account/user
                    $q->orWhereHas('account_login.account', function ($subQ) use ($search) {
                        $subQ->where('account_code', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%");
                    })->orWhereHas('account_login.user', function ($subQ) use ($search) {
                        $subQ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%");
                    });
                });
            })
            // Filter by order date
            ->when(!empty($order_date), function ($query) use ($order_date) {
                $query->whereDate('order_date', $order_date);
            })
            // Filter by upload or order status
            ->when(!empty($status), function ($query) use ($status) {
                if ($status === 'uploaded') {
                    $query->where('upload_status', 1);
                } elseif ($status === 'upload_error') {
                    $query->where('upload_status', 0);
                } else {
                    $query->where('status', $status)->whereNull('upload_status');
                }
            })
            // Filter by authenticated user's related accounts (if not superadmin)
            ->when(!auth()->user()->hasRole('superadmin'), function ($query) {
                $accountIds = auth()->user()->accounts()->pluck('id');
                $query->whereHas('account_login', function ($q) use ($accountIds) {
                    $q->whereIn('account_id', $accountIds);
                });
            })
            // Optional eager loading to reduce N+1 issues
            ->with(['account_login.account', 'account_login.user'])
            ->paginate($this->setting->data_per_page)
            ->onEachSide(1)
            ->appends(request()->query());

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
    public function index(Request $request) {
        $logged_account = Session::get('logged_account');
        $search = trim($request->get('search'));
        $date_from = trim($request->get('date_from'));
        $date_to = trim($request->get('date_to'));

        // $this->checkSalesOrderStatus();
        
        if(isset($logged_account)) {

            Session::forget('order_data');

            $date = time();

            // check if theres cut-off today
            $cut_off = SalesOrderCutOff::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            // $sales_orders = SalesOrder::SalesOrderSearch($search, $logged_account,$this->setting->data_per_page);
            $sales_orders = SalesOrder::orderBy('id', 'DESC')
                ->whereHas('account_login', function($qry) use($logged_account) {
                    $qry->where('account_id', $logged_account->account_id);
                })
                ->when(!empty($search), function($query) use($search) {
                    $query->where(function($qry) use($search) {
                        $qry->where('control_number', 'like', '%'.$search.'%')
                            ->orWhere('po_number', 'like', '%'.$search.'%')
                            ->orWhere('order_date', 'like', '%'.$search.'%')
                            ->orWhere('ship_date', 'like', '%'.$search.'%')
                            ->orWhere('ship_to_name', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%');
                    });
                })
                ->when(!empty($date_from), function($qry) use($date_from) {
                    $qry->where('order_date', '>=', $date_from);
                })
                ->when(!empty($date_to), function($qry) use($date_to) {
                    $qry->where('order_date', '<=', $date_to);
                })
                ->paginate($this->setting->data_per_page)->onEachSide(1)
                ->appends(request()->query());

            return view('sales-orders.index')->with([
                'sales_orders' => $sales_orders,
                'search' => $search,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'cut_off' => $cut_off,
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }
        
    }

    private function generateControlNumber() {
        $date_code = date('Ymd');

        do {
            $control_number = 'SO-'.$date_code.'-001';
            // get the most recent sales order
            $sales_order = SalesOrder::withTrashed()->orderBy('control_number', 'DESC')
                ->first();
            if(!empty($sales_order)) {
                $latest_control_number = $sales_order->control_number;
                list(, $prev_date, $last_number) = explode('-', $latest_control_number);

                // Increment the number based on the date
                $number = ($date_code == $prev_date) ? ((int)$last_number + 1) : 1;

                // Format the number with leading zeros
                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                // Construct the new control number
                $control_number = "SO-$date_code-$formatted_number";
            }

        } while(SalesOrder::withTrashed()->where('control_number', $control_number)->exists());

        return $control_number;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        $control_number = $this->generateControlNumber();
        
        $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
        if(!empty($logged_account->account->po_process_date) && $logged_account->account->po_process_date >= 3) {
            $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$logged_account->account->po_process_date.' weekdays'));
        }

        return view('sales-orders.create')->with([
            'control_number' => $control_number,
            'logged_account' => $logged_account,
            'process_ship_date' => $process_ship_date
        ]);
    }

    // resubmit sales order
    public function resubmit($id) {
        $control_number = $this->generateControlNumber();

        $sales_order = SalesOrder::findOrFail($id);
        // change status to cancelled
        $sales_order->update([
            'status' => 'cancelled'
        ]);

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

        session()->put('po_number', $sales_order->po_number);

        Session::put('order_data', $order_data);

        $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
        if(!empty($logged_account->account->po_process_date) && $logged_account->account->po_process_date >= 3) {
            $process_ship_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$logged_account->account->po_process_date.' weekdays'));
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
    public function store(StoreSalesOrderRequest $request) {
        $request->control_number = $this->generateControlNumber();

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

        // check account po prefix
        $po_number = $request->po_number;
        if(!empty($logged_account->account->po_prefix)) {
            $po_number = $logged_account->account->po_prefix.''.$po_number;
        }

        $sales_order = new SalesOrder([
            'account_login_id' => $logged_account->id,
            'shipping_address_id' => $shipping_address_id,
            'control_number' => $request->control_number,
            'po_number' => $po_number,
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

        // get CRISTALINO [CSP10001, CSP10002, CSP10003]
        $cristalino_prod_ids = Product::whereIn('stock_code', ['CSP10001', 'CSP10002', 'CSP10003'])->get()->pluck('id')->toArray();
        // get KS01046
        $sku_restictions = ['KS01046', 'KS01047'];
        $sku_ks_restrictions = Product::whereIn('stock_code', $sku_restictions)->get()->pluck('id')->toArray();

        $cristalino_data = array();
        $ks_1046_data = array();
        foreach($order_data['items'] as $product_id => $items) {
            
            // separate Cristalino
            if(in_array($product_id, $cristalino_prod_ids)) {
                $cristalino_data[$product_id] = $items;
            } else if(in_array($product_id, $sku_ks_restrictions)) { // separate KS01046
                $ks_1046_data[$product_id] = $items;
            } else {
                // $num++;
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
                        'uom_total_less_disc' => $data['discounted'],
                        // 'warehouse' => $data['warehouse'],
                    ]);
                    $sales_order_product_uom->save();

                    // check if there's a PAF row
                    if(isset($data['paf_rows']) && !empty($data['paf_rows'])) {
                        foreach($data['paf_rows'] as $paf_row) {
                            $sales_order_product_uom_paf = new SalesOrderProductUomPaf([
                                'sales_order_product_uom_id' => $sales_order_product_uom->id,
                                'paf_number' => $paf_row['paf_number'],
                                'uom' => $paf_row['uom'],
                                'quantity' => $paf_row['quantity'],
                            ]);
                            $sales_order_product_uom_paf->save();
                        }

                    }
                }
            }
        }

        // save cristalino in other as other parts
        if(!empty($cristalino_data)) {
            $part = $part + 1;
            foreach($cristalino_data as $product_id => $items) {
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
                        'uom_total_less_disc' => $data['discounted'],
                        // 'warehouse' => $data['warehouse'],
                    ]);
                    $sales_order_product_uom->save();

                    // check if there's a PAF row
                    if(isset($data['paf_rows']) && !empty($data['paf_rows'])) {
                        foreach($data['paf_rows'] as $paf_row) {
                            $sales_order_product_uom_paf = new SalesOrderProductUomPaf([
                                'sales_order_product_uom_id' => $sales_order_product_uom->id,
                                'paf_number' => $paf_row['paf_number'],
                                'uom' => $paf_row['uom'],
                                'quantity' => $paf_row['quantity'],
                            ]);
                            $sales_order_product_uom_paf->save();
                        }

                    }
                }
            }
        }

        // save KS01046
        if(!empty($sku_ks_restrictions)) {
            $part = $part + 1;
            foreach($ks_1046_data as $product_id => $items) {
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
                        'uom_total_less_disc' => $data['discounted'],
                        // 'warehouse' => $data['warehouse'],
                    ]);
                    $sales_order_product_uom->save();

                    // check if there's a PAF row
                    if(isset($data['paf_rows']) && !empty($data['paf_rows'])) {
                        foreach($data['paf_rows'] as $paf_row) {
                            $sales_order_product_uom_paf = new SalesOrderProductUomPaf([
                                'sales_order_product_uom_id' => $sales_order_product_uom->id,
                                'paf_number' => $paf_row['paf_number'],
                                'uom' => $paf_row['uom'],
                                'quantity' => $paf_row['quantity'],
                            ]);
                            $sales_order_product_uom_paf->save();
                        }
                    }
                }
            }
        }

        if($sales_order->status == 'finalized') {
            // $this->generateXml($sales_order);
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
    public function show($id) {
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
    public function edit($id) {
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

                // check if there's a PAF row
                $paf_rows = [];
                $paf_rows_data = SalesOrderProductUomPaf::where('sales_order_product_uom_id', $uom->id)->get();
                if(!$paf_rows_data->isEmpty()) {
                    foreach($paf_rows_data as $paf_row) {
                        $paf_rows[] = [
                            'paf_number' => $paf_row->paf_number,
                            'uom' => $paf_row->uom,
                            'quantity' => $paf_row->quantity,
                        ];
                    }
                }

                $order_data['items'][$order_product->product_id]['data'][$uom->uom] = [
                    'quantity' => $uom->quantity,
                    'total' => $uom->uom_total,
                    'discount' => 0,
                    'discounted' => $uom->uom_total_less_disc,
                    'paf_rows' => $paf_rows,
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
    public function update(UpdateSalesOrderRequest $request, $id) {
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

        // check account po prefix
        $po_number = $request->po_number;
        if(!empty($logged_account->account->po_prefix)) {
            $po_number = $logged_account->account->po_prefix.''.$po_number;
        }

        $sales_order->update([
            'shipping_address_id' => $shipping_address_id,
            'po_number' => $po_number,
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

        // get CRISTALINO [CSP10001, CSP10002, CSP10003]
        $cristalino_prod_ids = Product::whereIn('stock_code', ['CSP10001', 'CSP10002', 'CSP10003'])->get()->pluck('id')->toArray();
        // get KS01046
        $ks_1046 = Product::where('stock_code', 'KS01046')->first();

        $cristalino_data = array();
        $ks_1046_data = array();

        $sales_order->order_products()->forceDelete();
        foreach($order_data['items'] as $product_id => $items) {

            // separate Cristalino
            if(in_array($product_id, $cristalino_prod_ids)) {
                $cristalino_data[$product_id] = $items;
            } else if($product_id == $ks_1046->id) { // separate KS01046
                $ks_1046_data[$product_id] = $items;
            } else {

                // $num++;
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
                        'uom_total_less_disc' => $data['discounted'],
                        // 'warehouse' => $data['warehouse'],
                    ]);
                    $sales_order_product_uom->save();
                }
            }
        }

        // save cristalino in other as other parts
        if(!empty($cristalino_data)) {
            $part = $part + 1;
            foreach($cristalino_data as $product_id => $items) {
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
                        'uom_total_less_disc' => $data['discounted'],
                        // 'warehouse' => $data['warehouse'],
                    ]);
                    $sales_order_product_uom->save();
                }
            }
        }

        // save KS01046
        if(!empty($ks_1046_data)) {
            $part = $part + 1;
            foreach($ks_1046_data as $product_id => $items) {
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
                        'uom_total_less_disc' => $data['discounted'],
                        // 'warehouse' => $data['warehouse'],
                    ]);
                    $sales_order_product_uom->save();
                }
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
            // $this->generateXml($sales_order);

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
                } else {
                    $dateTime = \DateTime::createFromFormat('m-d-Y', $ship_date);
                    if ($dateTime === false) {
                        $ship_date = $ship_date;
                    } else {
                        $ship_date = $dateTime->format('Y-m-d');
                    }
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

    private function quantityConversion($quantity, $conversion, $operator, $reverse = false)
    {
        // Avoid division by zero
        if ($conversion == 0) {
            return (float) $quantity;
        }

        if ($operator == 'M') { // multiply
            if ($reverse) {
                return $quantity / $conversion;
            } else {
                return $quantity * $conversion;
            }
        } elseif ($operator == 'D') { // divide
            if ($reverse) {
                return $quantity * $conversion;
            } else {
                return $quantity / $conversion;
            }
        }

        return (float) $quantity;
    }

    private function uomQuantityAllocation($quantity, $product_id, $uom)
    {
        // If the UOM is already 'CS', just return the quantity as a float.
        if ($uom === 'CS') {
            return (float) $quantity;
        }
        
        // Assuming you are using Laravel's Eloquent to find the product.
        // Replace with your actual data-fetching method.
        $product = Product::findOrFail($product_id);

        // --- Logic to find the 'CS' unit and its conversion details ---

        $cs_uom_column = null;
        $conversion = 1;
        $operator = 'M';

        // 1. Identify which UOM field holds 'CS'.
        if ($product->stock_uom == 'CS') {
            $cs_uom_column = 'stock_uom';
        } elseif ($product->order_uom == 'CS') {
            $cs_uom_column = 'order_uom';
        } elseif ($product->other_uom == 'CS') {
            $cs_uom_column = 'other_uom';
        }

        // 2. If no 'CS' unit is defined, return the original quantity.
        if (is_null($cs_uom_column)) {
            return (float) $quantity;
        }

        // 3. Get the conversion details based on which field was 'CS'.
        if ($cs_uom_column === 'stock_uom') {
            // Rule: If 'stock_uom' is the case unit, its conversion is 1.
            // This is now handled directly in the code without needing database columns.
            $conversion = 1;
            $operator = 'M';
        } elseif ($cs_uom_column === 'order_uom') {
            $conversion = $product->order_uom_conversion;
            $operator = $product->order_uom_operator;
        } elseif ($cs_uom_column === 'other_uom') {
            $conversion = $product->other_uom_conversion;
            $operator = $product->other_uom_operator;
        }

        // --- Perform the conversion and return the decimal value ---

        // Call the helper function with reverse=true to convert to the 'CS' unit.
        $cs_quantity = $this->quantityConversion(
            $quantity,
            $conversion,
            $operator,
            true // Reverse is true to convert from pieces to cases
        );

        return (float) $cs_quantity;
    }

    private function isExcelDate(Cell $cell) {
        return Date::isDateTime($cell);
    }

    public function export(Request $request) {
        $logged_account = Session::get('logged_account');
        $date_from = trim($request->get('date_from'));
        $date_to = trim($request->get('date_to'));
        $search = trim($request->get('search'));

        $account = $logged_account->account;

        return Excel::download(new SalesOrderExport($account, $date_from, $date_to, $search), 'sales-orders-'.time().'.xlsx');
    }
}