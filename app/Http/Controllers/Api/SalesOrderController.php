<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use App\Models\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public $product_ids = [
        227, // 350ml
        228, // 500ml
        229, // 1000ml
    ];

    public function index(Request $request) {

        $data = Account::orderBy('account_code', 'ASC')
        ->whereHas('account_logins', function($query) {
            $query->whereHas('sales_orders', function($qry) {
                $qry->where('status', '<>', 'draft')->whereHas('order_products', function($qry1) {
                    $qry1->whereIn('product_id', $this->product_ids);
                });
            });
        })->paginate($request->get('limit'), ['*'], 'page', $request->get('page'));

        return response()->json($data, 200);
    }

    public function getDetails(Request $request, $id) {
        $account = Account::findOrFail($id);
        $data['account'] = $account;

        $sales_orders = SalesOrder::orderBy('control_number', 'DESC')
        ->where('status', '<>', 'draft')
        ->whereHas('account_login', function($query) use($account) {
            $query->where('account_id', $account->id);
        })->get();
        // ->paginate($request->get('limit'), ['*'], 'page', $request->get('page'));

        foreach($sales_orders as $sales_order) {

            $order_products = $sales_order->order_products;
            foreach($order_products as $order_product) {
                $product = $order_product->product;
                $uoms = $order_product->product_uoms;
            }

            $data['sales_orders'][] = $sales_order;
        }

        return response()->json($data, 200);
    }
}
