<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use App\Models\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    private function apiProductIds(): array
    {
        return config('sales-order.api_product_ids');
    }

    public function index(Request $request) {
        $productIds = $this->apiProductIds();

        $data = Account::orderBy('account_code', 'ASC')
        ->whereHas('account_logins', function($query) use($productIds) {
            $query->whereHas('sales_orders', function($qry) use($productIds) {
                $qry->where('status', '<>', 'draft')->whereHas('order_products', function($qry1) use($productIds) {
                    $qry1->whereIn('product_id', $productIds);
                });
            });
        })->paginate($request->input('limit', 15), ['*'], 'page', $request->input('page', 1));

        return response()->json($data, 200);
    }

    public function getDetails(Request $request, $id) {
        $account    = Account::findOrFail($id);
        $productIds = $this->apiProductIds();
        $data['account'] = $account;

        $sales_orders = SalesOrder::orderBy('control_number', 'DESC')
        ->where('status', '<>', 'draft')
        ->whereHas('order_products', function($query) use($productIds) {
            $query->whereIn('product_id', $productIds);
        })
        ->whereHas('account_login', function($query) use($account) {
            $query->where('account_id', $account->id);
        })
        ->with([
            'order_products' => fn($q) => $q->whereIn('product_id', $productIds),
            'order_products.product',
            'order_products.product_uoms',
        ])
        ->paginate($request->input('limit', 15), ['*'], 'page', $request->input('page', 1));

        foreach($sales_orders as $sales_order) {
            $order_product_data = [];
            foreach($sales_order->order_products as $order_product) {
                $order_product_data[] = [
                    'order_product' => $order_product,
                    'product'       => $order_product->product,
                    'uom'           => $order_product->product_uoms->first(),
                ];
            }

            $data['sales_orders'][] = [
                'sales_order'    => $sales_order,
                'order_products' => $order_product_data,
            ];
        }

        return response()->json($data, 200);
    }
}
