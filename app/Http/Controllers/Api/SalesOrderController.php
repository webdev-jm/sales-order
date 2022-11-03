<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\Account;
use App\Models\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public $product_ids = [
        228,
        229,
        230,
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
        ->whereHas('order_products', function($query) {
            $query->whereIn('product_id', $this->product_ids);
        })
        ->whereHas('account_login', function($query) use($account) {
            $query->where('account_id', $account->id);
        })
        ->get();
        // ->paginate($request->get('limit'), ['*'], 'page', $request->get('page'));

        foreach($sales_orders as $sales_order) {

            $order_product_data = [];
            $order_products = SalesOrderProduct::where('sales_order_id', $sales_order->id)->whereIn('product_id', $this->product_ids)->get();
            foreach($order_products as $order_product) {
                $product = Product::find($order_product->product_id);
                $uoms = SalesOrderProductUom::where('sales_order_product_id', $order_product->id)->first();

                $order_product_data[] = [
                    $order_product,
                    'product' => $product,
                    'uom' => $uoms
                ];
            }

            $order_data = [
                'sales_order' => $sales_order,
                'order_products' => $order_product_data
            ];

            $data['sales_orders'][] = $order_data;
        }

        return response()->json($data, 200);
    }
}
