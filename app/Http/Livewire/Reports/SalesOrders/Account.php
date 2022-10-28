<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

use AccountModel;
use App\Models\SalesOrder;

class Account extends Component
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
            $accounts = AccountModel::whereHas('account_logins', function($query) {
                $query->whereHas('sales_orders', function($qry1) {
                    if(!empty($this->year) && !empty($this->month)) {
                        $date_string = $this->year.'-'.$this->month;
                        $qry1->where('status', 'finalized')
                        ->where('upload_status', 1)
                        ->where('order_date', 'like', $date_string.'%');
                    } else {
                        $qry1->where('status', 'finalized')
                        ->where('upload_status', 1);
                    }
                })
                ->whereHas('user', function($qry1) {
                    $qry1->where('group_code', $this->group_code);
                });
            })
            ->get();
        } else {
            $accounts = AccountModel::whereHas('account_logins', function($query) {
                $query->whereHas('sales_orders', function($qry1) {
                    if(!empty($this->year) && !empty($this->month)) {
                        $date_string = $this->year.'-'.$this->month;
                        $qry1->where('status', 'finalized')
                        ->where('upload_status', 1)
                        ->where('order_date', 'like', $date_string.'%');
                    } else {
                        $qry1->where('status', 'finalized')
                        ->where('upload_status', 1);
                    }
                });
            })->get();
        }

        $chart_data = [];
        $grand_total = 0;
        foreach($accounts as $account) {
            if(!empty($this->group_code)) {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                    $total = SalesOrder::where('status', 'finalized')
                    ->where('upload_status', 1)
                    ->where('order_date', 'like', $date_string.'%')
                    ->whereHas('account_login', function($query) use($account) {
                        $query->where('account_id', $account->id)
                        ->whereHas('user', function($qry) {
                            $qry->where('group_code', $this->group_code);
                        });
                    })->sum('total_sales');
                } else {
                    $total = SalesOrder::where('status', 'finalized')
                    ->where('upload_status', 1)
                    ->whereHas('account_login', function($query) use($account) {
                        $query->where('account_id', $account->id)
                        ->whereHas('user', function($qry) {
                            $qry->where('group_code', $this->group_code);
                        });
                    })->sum('total_sales');
                }
                
            } else {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                    $total = SalesOrder::where('status', 'finalized')
                    ->where('upload_status', 1)
                    ->where('order_date', 'like', $date_string.'%')
                    ->whereHas('account_login', function($query) use($account) {
                        $query->where('account_id', $account->id);
                    })->sum('total_sales');

                } else {
                    $total = SalesOrder::where('status', 'finalized')
                    ->where('upload_status', 1)
                    ->whereHas('account_login', function($query) use($account) {
                        $query->where('account_id', $account->id);
                    })->sum('total_sales');
                }
            }

            $chart_data[] = [
                $account->short_name,
                (float)$total
            ];

            $grand_total += (float)$total;
        }

        $chart = [];
        foreach($chart_data as $data) {
            $percent = ($data[1] / $grand_total) * 100;
            $chart[] = [
                $data[0].' ('.number_format($percent, 2).'%)',
                $data[1]
            ];
        }

        return view('livewire.reports.sales-orders.account')->with([
            'chart_data' => $chart,
            'grand_total' => $grand_total
        ]);
    }
}
