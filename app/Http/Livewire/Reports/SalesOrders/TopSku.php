<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

use Illuminate\Support\Facades\DB;
use App\Models\SalesOrderProduct;
use App\Models\Product;

class TopSku extends Component
{
    public $year, $month, $group_code;
    protected $queryString = [
        'year',
        'month',
        'group_code'
    ];

    public function render()
    {
        if(!empty($this->group_code)) {
            $sales_order_products = SalesOrderProduct::select('product_id', DB::raw('sum(total_sales) as total'))
            ->groupBy('product_id')
            ->orderBy('total', 'DESC')
            ->whereHas('sales_order', function($query) {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                    $query->whereHas('account_login', function($qry) {
                        $qry->whereHas('user', function($qry1) {
                            $qry1->where('group_code', $this->group_code);
                        });
                    })
                    ->where('order_date', 'like', $date_string.'%');
                } else {
                    $query->whereHas('account_login', function($qry) {
                        $qry->whereHas('user', function($qry1) {
                            $qry1->where('group_code', $this->group_code);
                        });
                    });
                }
            })
            ->limit(10)->get();
        } else {
            if(!empty($this->year) && !empty($this->month)) {
                $date_string = $this->year.'-'.$this->month;
                $sales_order_products = SalesOrderProduct::select('product_id', DB::raw('sum(total_sales) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'DESC')
                ->whereHas('sales_order', function($query) use ($date_string) {
                    $query->where('order_date', 'like', $date_string.'%');
                })
                ->limit(10)->get();
            } else {
                $sales_order_products = SalesOrderProduct::select('product_id', DB::raw('sum(total_sales) as total'))
                ->groupBy('product_id')
                ->orderBy('total', 'DESC')
                ->limit(10)->get();
            }
        }

        $categories = [];
        $data = [];
        foreach($sales_order_products as $order_product) {
            $product = $order_product->product;
            $categories[] = $product->stock_code.' '.$product->description.' '.$product->size;
            $data[] = (float)$order_product->total;
        }

        return view('livewire.reports.sales-orders.top-sku')->with([
            'categories' => $categories,
            'data' => $data
        ]);
    }
}
