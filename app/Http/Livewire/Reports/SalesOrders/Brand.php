<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

use App\Models\Product;
use App\Models\SalesOrderProduct;

class Brand extends Component
{
    public $year, $month, $group_code;
    protected $queryString = [
        'year',
        'month',
        'group_code'
    ];
    
    public function render()
    {
        $products = Product::select('brand')->distinct()
        ->whereHas('sales_order_product', function($query) {
            $query->whereHas('sales_order', function($qry1) {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                    $qry1->where('order_date', 'like', $date_string.'%')
                    ->whereHas('account_login', function($qry2) {
                        if(!empty($this->group_code)) {
                            $qry2->whereHas('user', function($qry3) {
                                $qry3->where('group_code', $this->group_code);
                            });
                        } else {
                            $qry2->whereHas('user');
                        }
                    });
                } else {
                    $qry1->whereHas('account_login', function($qry2) {
                        if(!empty($this->group_code)) {
                            $qry2->whereHas('user', function($qry3) {
                                $qry3->where('group_code', $this->group_code);
                            });
                        } else {
                            $qry2->whereHas('user');
                        }
                    });
                }
            });
        })
        ->get();

        $chart_data = [];
        $grand_total = 0;
        foreach($products as $product) {
            $brand_total = SalesOrderProduct::whereHas('product', function($query) use ($product) {
                $query->where('brand', $product->brand);
            })
            ->whereHas('sales_order', function($query) {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                    $query->where('order_date', 'like', $date_string.'%')
                    ->whereHas('account_login', function($qry) {
                        if(!empty($this->group_code)) {
                            $qry->whereHas('user', function($qry1) {
                                $qry1->where('group_code', $this->group_code);
                            });
                        } else {
                            $qry->whereHas('user');
                        }
                    });
                } else {
                    $query->whereHas('account_login', function($qry) {
                        if(!empty($this->group_code)) {
                            $qry->whereHas('user', function($qry1) {
                                $qry1->where('group_code', $this->group_code);
                            });
                        } else {
                            $qry->whereHas('user');
                        }
                    });
                }
            })
            ->sum('total_sales');

            if($brand_total > 0) {
                $chart_data[] = [
                    $product->brand,
                    (float)$brand_total
                ];

                $grand_total += (float)$brand_total;
            }
        }

        return view('livewire.reports.sales-orders.brand')->with([
            'chart_data' => $chart_data,
            'grand_total' => $grand_total
        ]);
    }
}
