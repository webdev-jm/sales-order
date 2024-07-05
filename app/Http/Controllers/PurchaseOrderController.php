<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\SalesOrderCutOff;

class PurchaseOrderController extends Controller
{
    public function index(Request $request) {
        $search = $request->get('search');
        Session::forget('selectedPO');
        $logged_account = Session::get('logged_account');
        if(isset($logged_account)) {

            $date = time();

            // check if theres cut-off today
            $cut_off = SalesOrderCutOff::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            return view('purchase-orders.index')->with([
                'cut_off' => $cut_off,
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }
    }

    public function show($id) {
        $logged_account = Session::get('logged_account');
        if(isset($logged_account)) {
            $purchase_order = PurchaseOrder::findOrFail($id);

            return view('purchase-orders.show')->with([
                'purchase_order' => $purchase_order,
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }
    }

    public function create(Request $request) {
        $logged_account = Session::get('logged_account');
        if(isset($logged_account)) {
            $selectedPO = Session::get('selectedPO');
            if(!empty($selectedPO)) {

                return view('purchase-orders.create')->with([
                    'selectedPO' => $selectedPO,
                ]);
            } else {
                return redirect()->route('purchase-orders.index')->with([
                    'message_error' => 'please select purchase order to continue creating SO.'
                ]);
            }
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }
    }
}
