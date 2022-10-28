<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

use App\Models\User;
use App\Models\SalesOrder;

class Salesman extends Component
{
    public $year, $month, $group_code;
    public $chart_data, $grand_total;

    protected $queryString = [
        'year',
        'month',
        'group_code'
    ];

    protected $listeners = [
        'setSalesmanFilter' => 'setFilter'
    ];
    

    public function setFilter($year, $month, $group_code) {
        $this->year = $year;
        $this->month = $month;
        $this->group_code = $group_code;

        $this->mount();
        $this->emitSelf('updateChart');
    }

    public function mount() {
        if(!empty($this->group_code)) {
            $users = User::whereHas('account_logins', function($query) {
                    $query->whereHas('sales_orders', function($qry) {
                        if(!empty($this->year) && !empty($this->month)) {
                            $date_string = $this->year.'-'.$this->month;

                            $qry->where('status', 'finalized')
                            ->where('upload_status', 1)
                            ->where('order_date', 'like', $date_string.'%');
                        } else {
                            $qry->where('status', 'finalized')
                            ->where('upload_status', 1);
                        }
                    });
            })
            ->where('group_code', $this->group_code)
            ->get();
        } else {
            $users = User::whereHas('account_logins', function($query) {
                $query->whereHas('sales_orders', function($qry) {
                    if(!empty($this->year) && !empty($this->month)) {
                        $date_string = $this->year.'-'.$this->month;
                        $qry->where('status', 'finalized')
                        ->where('upload_status', 1)
                        ->where('order_date', 'like', $date_string.'%');
                    } else {
                        $qry->where('status', 'finalized')
                        ->where('upload_status', 1);
                    }
                });
            })->get();
        }
        
        $chart_data = [];
        $grand_total = 0;
        foreach($users as $user) {
            if(!empty($this->year) && !empty($this->month)) {
                $date_string = $this->year.'-'.$this->month;
                $sales_orders_total = SalesOrder::where('status', 'finalized')
                ->where('upload_status', 1)
                ->where('order_date', 'like', $date_string.'%')
                ->whereHas('account_login', function($query) use($user) {
                    $query->where('user_id', $user->id);
                })->sum('total_sales');
            } else {
                $sales_orders_total = SalesOrder::where('status', 'finalized')
                ->where('upload_status', 1)
                ->whereHas('account_login', function($query) use($user) {
                    $query->where('user_id', $user->id);
                })->sum('total_sales');
            }

            $chart_data[] = [
                $user->firstname.' '.$user->lastname,
                (float)$sales_orders_total
            ];

            $grand_total += (float)$sales_orders_total;
        }

        $chart = [];
        foreach($chart_data as $data) {
            $percent = ($data[1] / $grand_total) * 100;
            $chart[] = [
                $data[0].' ('.number_format($percent, 2).'%)',
                $data[1]
            ];
        }

        $this->chart_data = $chart;
        $this->grand_total = $grand_total;
    }


    public function render()
    {
        return view('livewire.reports.sales-orders.salesman');
    }
}
