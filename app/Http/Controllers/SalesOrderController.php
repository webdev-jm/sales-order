<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUomPAF;
use App\Models\Product;
use App\Models\SalesOrderCutOff;

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
use App\Services\SalesOrderService;
use App\Jobs\GenerateSalesOrderXml;
use App\Jobs\CheckSalesOrderStatus;


class SalesOrderController extends Controller
{
    use GlobalTrait;
    use SoXmlTrait;

    public $setting;

    public function __construct(protected SalesOrderService $salesOrderService)
    {
        $this->setting = $this->getSettings();
    }

    public function list(Request $request)
    {
        $search     = trim($request->input('search'));
        $status     = trim($request->input('status'));
        $order_date = trim($request->input('order-date'));

        $sales_orders = SalesOrder::query()
            ->orderByDesc('control_number')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('control_number', 'like', "%{$search}%")
                        ->orWhere('po_number', 'like', "%{$search}%")
                        ->orWhere('order_date', 'like', "%{$search}%")
                        ->orWhere('ship_date', 'like', "%{$search}%")
                        ->orWhere('ship_to_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");

                    $q->orWhereHas('account_login.account', function ($subQ) use ($search) {
                        $subQ->where('account_code', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%");
                    })->orWhereHas('account_login.user', function ($subQ) use ($search) {
                        $subQ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%");
                    });
                });
            })
            ->when(!empty($order_date), function ($query) use ($order_date) {
                $query->whereDate('order_date', $order_date);
            })
            ->when(!empty($status), function ($query) use ($status) {
                if ($status === 'uploaded') {
                    $query->where('upload_status', 1);
                } elseif ($status === 'upload_error') {
                    $query->where('upload_status', 0);
                } else {
                    $query->where('status', $status)->whereNull('upload_status');
                }
            })
            ->when(!auth()->user()->hasRole('superadmin'), function ($query) {
                $accountIds = auth()->user()->accounts()->pluck('id');
                $query->whereHas('account_login', function ($q) use ($accountIds) {
                    $q->whereIn('account_id', $accountIds);
                });
            })
            ->with(['account_login.account', 'account_login.user'])
            ->paginate($this->setting->data_per_page)
            ->onEachSide(1)
            ->appends(request()->query());

        return view('pages.sales-orders.list')->with([
            'search'     => $search,
            'status'     => $status,
            'order_date' => $order_date,
            'sales_orders' => $sales_orders,
        ]);
    }

    public function dashboard()
    {
        return view('pages.sales-orders.dashboard');
    }

    public function index(Request $request)
    {
        $logged_account = Session::get('logged_account');

        if (empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order',
            ]);
        }

        Session::forget('order_data');

        $search    = trim($request->input('search'));
        $date_from = trim($request->input('date_from'));
        $date_to   = trim($request->input('date_to'));

        $cut_off = SalesOrderCutOff::where('start_date', '<=', time())
            ->where('end_date', '>=', time())
            ->first();

        $sales_orders = SalesOrder::orderBy('id', 'DESC')
            ->whereHas('account_login', function ($qry) use ($logged_account) {
                $qry->where('account_id', $logged_account->account_id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($qry) use ($search) {
                    $qry->where('control_number', 'like', '%' . $search . '%')
                        ->orWhere('po_number', 'like', '%' . $search . '%')
                        ->orWhere('order_date', 'like', '%' . $search . '%')
                        ->orWhere('ship_date', 'like', '%' . $search . '%')
                        ->orWhere('ship_to_name', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                });
            })
            ->when(!empty($date_from), function ($qry) use ($date_from) {
                $qry->where('order_date', '>=', $date_from);
            })
            ->when(!empty($date_to), function ($qry) use ($date_to) {
                $qry->where('order_date', '<=', $date_to);
            })
            ->paginate($this->setting->data_per_page)
            ->onEachSide(1)
            ->appends(request()->query());

        return view('pages.sales-orders.index')->with([
            'sales_orders' => $sales_orders,
            'search'       => $search,
            'date_from'    => $date_from,
            'date_to'      => $date_to,
            'cut_off'      => $cut_off,
        ]);
    }

    public function create()
    {
        $logged_account = Session::get('logged_account');

        if (empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order',
            ]);
        }

        $process_ship_date = $this->resolveShipDate($logged_account);

        return view('pages.sales-orders.create')->with([
            'logged_account'    => $logged_account,
            'process_ship_date' => $process_ship_date,
        ]);
    }

    public function resubmit($id)
    {
        $logged_account = Session::get('logged_account');

        if (empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order',
            ]);
        }

        $sales_order = SalesOrder::findOrFail($id);
        $sales_order->update(['status' => 'cancelled']);

        Session::forget('order_data');
        Session::put('order_data', $this->buildOrderDataFromSalesOrder($sales_order));
        session()->put('po_number', $sales_order->po_number);

        $process_ship_date = $this->resolveShipDate($logged_account);

        return view('pages.sales-orders.create')->with([
            'logged_account'    => $logged_account,
            'process_ship_date' => $process_ship_date,
        ]);
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $order_data = Session::get('order_data');

        if (empty($order_data['items'])) {
            return back()->with('message_error', 'Please add items first.')->withInput();
        }

        if (empty($order_data['po_value']) || $order_data['po_value'] <= 0) {
            return back()->with('message_error', 'PO value is required.')->withInput();
        }

        $account = Session::get('logged_account')->account;

        try {
            $sales_order = $this->salesOrderService->createOrder($request, $account, $order_data);

            activity('create')
                ->performedOn($sales_order)
                ->log(':causer.firstname :causer.lastname has created sales order :subject.control_number :subject.po_number');

            if ($request->status == 'finalized' && config('sales-order.xml_generation_enabled')) {
                GenerateSalesOrderXml::dispatch($sales_order);
            }

            return redirect()->route('sales-order.index')->with([
                'message_success' => 'Sales Order ' . $sales_order->control_number . ' was created',
            ]);
        } catch (\Exception $e) {
            return back()->with('message_error', 'Error creating order: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $sales_order   = SalesOrder::findOrFail($id);
        $parts         = SalesOrderProduct::select('part')->distinct()->where('sales_order_id', $sales_order->id)->get('part');
        $reference_arr = explode(' ,', $sales_order->reference);

        if (is_null($sales_order->upload_status) && empty($sales_order->reference)) {
            CheckSalesOrderStatus::dispatch($sales_order);
        }

        return view('pages.sales-orders.show')->with([
            'sales_order'   => $sales_order,
            'parts'         => $parts,
            'reference_arr' => $reference_arr,
        ]);
    }

    public function edit($id)
    {
        $logged_account = Session::get('logged_account');

        if (empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order',
            ]);
        }

        $sales_order = SalesOrder::findOrFail($id);

        if ($sales_order->status == 'for optimization') {
            return redirect()->route('sales-order.show', $sales_order->id)->with([
                'message_error' => 'SO cannot be edited once status has been finalized.',
            ]);
        }

        Session::put('order_data', $this->buildOrderDataFromSalesOrder($sales_order));

        return view('pages.sales-orders.edit')->with([
            'sales_order' => $sales_order,
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, $id)
    {
        $logged_account = Session::get('logged_account');
        $order_data     = Session::get('order_data');

        if (empty($order_data['items'])) {
            return back()->with('message_error', 'Please add items first.')->withInput();
        }

        if (empty($order_data['po_value']) || $order_data['po_value'] <= 0) {
            return back()->with('message_error', 'PO value is required.')->withInput();
        }

        $sales_order     = SalesOrder::findOrFail($id);
        $changes_arr['old'] = $sales_order->getOriginal();

        $this->salesOrderService->updateOrder($sales_order, $request, $logged_account->account, $order_data);

        $changes_arr['changes'] = $sales_order->getChanges();

        activity('update')
            ->performedOn($sales_order)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated sales order :subject.control_number :subject.po_number .');

        if ($sales_order->status == 'draft') {
            return back()->with([
                'message_success' => 'Sales order ' . $sales_order->control_number . ' was updated.',
            ]);
        }

        if ($sales_order->status == 'finalized' && config('sales-order.xml_generation_enabled')) {
            GenerateSalesOrderXml::dispatch($sales_order);
        }

        return redirect()->route('sales-order.index')->with([
            'message_success' => 'Sales order ' . $sales_order->control_number . ' was updated.',
        ]);
    }

    public function upload(Request $request)
    {
        $logged_account = Session::get('logged_account');

        if (empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order',
            ]);
        }

        $request->validate([
            'upload_file' => ['required', 'mimes:xlsx,xls'],
        ]);

        $po_number            = '';
        $paf_number           = '';
        $ship_date            = '';
        $shipping_instruction = '';
        $ship_to_address_id   = 'default';
        $po_value             = '';

        $ship_to_name      = $logged_account->account->account_name;
        $ship_to_address_1 = $logged_account->account->ship_to_address1;
        $ship_to_address_2 = $logged_account->account->ship_to_address2;
        $ship_to_address_3 = $logged_account->account->ship_to_address3;
        $postal_code       = $logged_account->account->postal_code;

        $path    = $request->file('upload_file')->store('sales_order');
        $imports = Excel::toArray(new ActivityPlanImport, storage_path('app') . '/' . $path);

        $data    = [];
        $row_num = 0;

        foreach ($imports[0] as $row) {
            $row_num++;

            if ($row_num == 1 && $row[0] == 'PO NUMBER') {
                $po_number = $row[1];
            }
            if ($row_num == 2 && $row[0] == 'PAF NUMBER') {
                $paf_number = $row[1];
            }
            if ($row_num == 3 && $row[0] == 'SHIP DATE') {
                $ship_date = $row[1];
                if (is_int($ship_date)) {
                    $ship_date = Date::excelToDateTimeObject($ship_date)->format('Y-m-d');
                } else {
                    $dateTime = \DateTime::createFromFormat('m-d-Y', $ship_date);
                    if ($dateTime !== false) {
                        $ship_date = $dateTime->format('Y-m-d');
                    }
                }
            }
            if ($row_num == 4 && $row[0] == 'SHIPPING INSTRUCTION') {
                $shipping_instruction = $row[1];
            }
            if ($row_num == 5 && $row[0] == 'SHIP TO ADDRESS') {
                if (!empty($row[1])) {
                    $shipping_address = $logged_account->account->shipping_addresses()->where('address_code', $row[1])->first();
                    if (!empty($shipping_address)) {
                        $ship_to_address_id = $shipping_address->id;
                        $ship_to_name       = $shipping_address->address_code . ' - ' . $shipping_address->ship_to_name;
                        $ship_to_address_1  = $shipping_address->building;
                        $ship_to_address_2  = $shipping_address->street;
                        $ship_to_address_3  = $shipping_address->city;
                        $postal_code        = $shipping_address->postal;
                    }
                }
            }
            if ($row_num == 6 && $row[0] == 'PO VALUE') {
                $po_value = $row[1];
            }

            if ($row_num > 7 && !empty($row[0])) {
                $product = Product::where('stock_code', $row[0])->first();

                if (!empty($product)) {
                    $data[$product->id] = [
                        'product'          => $product,
                        'stock_code'       => $row[0],
                        'description'      => $product->description,
                        'size'             => $product->size,
                        'data'             => [
                            $row[1] => [
                                'quantity'   => $row[2],
                                'total'      => 0,
                                'discount'   => 0,
                                'discounted' => 0,
                            ],
                        ],
                        'product_total'    => 0,
                        'product_quantity' => 0,
                    ];
                }
            }
        }

        $order_data            = $this->salesOrderService->calculateOrderTotals($data, $logged_account->account);
        $order_data['po_value'] = $po_value;

        Session::put('order_data', $order_data);

        return redirect()->route('sales-order.create')->with([
            'po_number'            => $po_number,
            'paf_number'           => $paf_number,
            'ship_date'            => $ship_date,
            'shipping_instruction' => $shipping_instruction,
            'shipping_address_id'  => $ship_to_address_id,
            'po_value'             => $po_value ?? 0,
            'ship_to_name'         => $ship_to_name,
            'ship_to_address1'     => $ship_to_address_1,
            'ship_to_address2'     => $ship_to_address_2,
            'ship_to_address3'     => $ship_to_address_3,
            'postal_code'          => $postal_code,
        ]);
    }

    public function export(Request $request)
    {
        $logged_account = Session::get('logged_account');
        $date_from      = trim($request->input('date_from'));
        $date_to        = trim($request->input('date_to'));
        $search         = trim($request->input('search'));

        return Excel::download(
            new SalesOrderExport($logged_account->account, $date_from, $date_to, $search),
            'sales-orders-' . time() . '.xlsx'
        );
    }

    /**
     * Admin-only debug route: re-dispatch the XML generation job for a specific order.
     * Pass ?id=<sales_order_id> in the query string.
     */
    public function getXml(Request $request)
    {
        $sales_order = SalesOrder::findOrFail($request->query('id'));

        GenerateSalesOrderXml::dispatch($sales_order);

        return back()->with('message_success', 'XML generation dispatched for ' . $sales_order->control_number);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the $order_data array from an existing SalesOrder's persisted products.
     * Used by both edit() and resubmit() to restore session state from the database.
     */
    private function buildOrderDataFromSalesOrder(SalesOrder $sales_order): array
    {
        $order_data = [];

        foreach ($sales_order->order_products as $order_product) {
            $product = $order_product->product;

            $order_data['items'][$order_product->product_id] = [
                'stock_code'  => $product->stock_code,
                'description' => $product->description,
                'size'        => $product->size,
            ];

            foreach ($order_product->product_uoms as $uom) {
                $paf_rows      = [];
                $paf_rows_data = SalesOrderProductUomPAF::where('sales_order_product_uom_id', $uom->id)->get();

                if ($paf_rows_data->isNotEmpty()) {
                    foreach ($paf_rows_data as $paf_row) {
                        $paf_rows[] = [
                            'paf_number' => $paf_row->paf_number,
                            'uom'        => $paf_row->uom,
                            'quantity'   => $paf_row->quantity,
                        ];
                    }
                }

                $order_data['items'][$order_product->product_id]['data'][$uom->uom] = [
                    'quantity'   => $uom->quantity,
                    'total'      => $uom->uom_total,
                    'discount'   => 0,
                    'discounted' => $uom->uom_total_less_disc,
                    'paf_rows'   => $paf_rows,
                ];
            }

            $order_data['items'][$order_product->product_id]['product_total']    = $order_product->total_sales;
            $order_data['items'][$order_product->product_id]['product_quantity'] = $order_product->total_quantity;
        }

        $order_data['total_quantity'] = $sales_order->total_quantity;
        $order_data['total']          = $sales_order->total_sales;
        $order_data['grand_total']    = $sales_order->grand_total;
        $order_data['po_value']       = $sales_order->po_value;

        return $order_data;
    }

    /**
     * Compute the earliest allowed ship date for the logged-in account.
     * Accounts with a po_process_date >= 3 get that many weekdays added;
     * otherwise it defaults to the next calendar day.
     */
    private function resolveShipDate($logged_account): string
    {
        if (!empty($logged_account->account->po_process_date) && $logged_account->account->po_process_date >= 3) {
            return date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $logged_account->account->po_process_date . ' weekdays'));
        }

        return date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
    }
}
