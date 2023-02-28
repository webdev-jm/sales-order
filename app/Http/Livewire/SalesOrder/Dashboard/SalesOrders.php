<?php

namespace App\Http\Livewire\SalesOrder\Dashboard;

use App\Models\SalesOrder;

use Livewire\Component;
use Livewire\WithPagination;

class SalesOrders extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;
    public $year, $month, $days;

    protected $listeners = [
        'setOrderList' => 'setDate'
    ];

    public function updatingSearch()
    {
        $this->resetPage('order-page');
    }

    public function setDate($year, $month, $days) {
        $this->year = $year;
        $this->month = $month < 10 ? '0'.$month : $month;
        $this->days = $days;

        $this->resetPage('order-page');
    }

    public function render()
    {
        if(!empty($this->year) && !empty($this->month) && empty($this->days)) {
            $date_string = $this->year.'-'.($this->month < 10 ? '0'.(int)$this->month : $this->month);

            if($this->search != '') {
                $sales_orders = SalesOrder::where('order_date', 'like', $date_string.'%')
                ->where('status', 'finalized')
                ->where('upload_status', 1)
                ->where(function($query) {
                    $query->where('control_number', 'like', '%'.$this->search.'%')
                    ->orWhere('po_number', 'like', '%'.$this->search.'%')
                    ->orWhere('control_number', 'like', '%'.$this->search.'%')
                    ->orWhere('order_date', 'like', '%'.$this->search.'%')
                    ->orWhere('total_sales', 'like', '%'.$this->search.'%')
                    ->orWhere('grand_total', 'like', '%'.$this->search.'%')
                    ->orWhereHas('account_login', function($qry) {
                        $qry->whereHas('user', function($qry1) {
                            $qry1->where('email', 'like', '%'.$this->search.'%')
                            ->orWhere('firstname', 'like', '%'.$this->search.'%')
                            ->orWhere('lastname', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('account', function($qry1) {
                            $qry1->where('account_code', 'like', '%'.$this->search.'%')
                            ->orWhere('short_name', 'like', '%'.$this->search.'%');
                        });
                    });
                });
            } else {
                $sales_orders = SalesOrder::where('order_date', 'like', $date_string.'%')
                ->where('status', 'finalized')
                ->where('upload_status', 1);
            }

        } else if(!empty($this->days)) {

            $dates = [];
            foreach($this->days as $year => $months) {
                foreach($months as $month => $days) {
                    foreach($days as $day) {
                        $dates[] = $year.'-'.$month.'-'.$day;
                    }
                }
            }

            if(!empty($dates)) {

                if($this->search != '') {
                    $sales_orders = SalesOrder::whereIn('order_date', $dates)
                    ->where('status', 'finalized')
                    ->where('upload_status', 1)
                    ->where(function($query) {
                        $query->where('control_number', 'like', '%'.$this->search.'%')
                        ->orWhere('po_number', 'like', '%'.$this->search.'%')
                        ->orWhere('control_number', 'like', '%'.$this->search.'%')
                        ->orWhere('order_date', 'like', '%'.$this->search.'%')
                        ->orWhere('total_sales', 'like', '%'.$this->search.'%')
                        ->orWhere('grand_total', 'like', '%'.$this->search.'%')
                        ->orWhereHas('account_login', function($qry) {
                            $qry->whereHas('user', function($qry1) {
                                $qry1->where('email', 'like', '%'.$this->search.'%')
                                ->orWhere('firstname', 'like', '%'.$this->search.'%')
                                ->orWhere('lastname', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('account', function($qry1) {
                                $qry1->where('account_code', 'like', '%'.$this->search.'%')
                                ->orWhere('short_name', 'like', '%'.$this->search.'%');
                            });
                        });
                    });
                } else {
                    $sales_orders = SalesOrder::whereIn('order_date', $dates)
                    ->where('status', 'finalized')
                    ->where('upload_status', 1);
                }

            } else {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                } else {
                    $date_string = date('Y-m');
                }

                if($this->search != '') {
                    $sales_orders = SalesOrder::where('order_date', 'like', $date_string.'-%')
                    ->where('status', 'finalized')
                    ->where('upload_status', 1)->where(function($query) {
                        $query->where('control_number', 'like', '%'.$this->search.'%')
                        ->orWhere('po_number', 'like', '%'.$this->search.'%')
                        ->orWhere('control_number', 'like', '%'.$this->search.'%')
                        ->orWhere('order_date', 'like', '%'.$this->search.'%')
                        ->orWhere('total_sales', 'like', '%'.$this->search.'%')
                        ->orWhere('grand_total', 'like', '%'.$this->search.'%')
                        ->orWhereHas('account_login', function($qry) {
                            $qry->whereHas('user', function($qry1) {
                                $qry1->where('email', 'like', '%'.$this->search.'%')
                                ->orWhere('firstname', 'like', '%'.$this->search.'%')
                                ->orWhere('lastname', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('account', function($qry1) {
                                $qry1->where('account_code', 'like', '%'.$this->search.'%')
                                ->orWhere('short_name', 'like', '%'.$this->search.'%');
                            });
                        });
                    });
                } else {
                    $sales_orders = SalesOrder::where('order_date', 'like', $date_string.'-%')
                    ->where('status', 'finalized')
                    ->where('upload_status', 1);
                }

                
            }

        } else {
            $date_string = date('Y-m-d');

            if($this->search != '') {
                $sales_orders = SalesOrder::where('order_date', $date_string)
                ->where('status', 'finalized')
                ->where('upload_status', 1)
                ->where(function($query) {
                    $query->where('control_number', 'like', '%'.$this->search.'%')
                    ->orWhere('po_number', 'like', '%'.$this->search.'%')
                    ->orWhere('control_number', 'like', '%'.$this->search.'%')
                    ->orWhere('order_date', 'like', '%'.$this->search.'%')
                    ->orWhere('total_sales', 'like', '%'.$this->search.'%')
                    ->orWhere('grand_total', 'like', '%'.$this->search.'%')
                    ->orWhereHas('account_login', function($qry) {
                        $qry->whereHas('user', function($qry1) {
                            $qry1->where('email', 'like', '%'.$this->search.'%')
                            ->orWhere('firstname', 'like', '%'.$this->search.'%')
                            ->orWhere('lastname', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('account', function($qry1) {
                            $qry1->where('account_code', 'like', '%'.$this->search.'%')
                            ->orWhere('short_name', 'like', '%'.$this->search.'%');
                        });
                    });
                });
            } else {
                $sales_orders = SalesOrder::where('order_date', $date_string)
                ->where('status', 'finalized')
                ->where('upload_status', 1);
            }

        }

        $sales_orders = $sales_orders->paginate(10, ['*'], 'order-page')->onEachSide(1);

        return view('livewire.sales-order.dashboard.sales-orders')->with([
            'sales_orders' => $sales_orders
        ]);
    }
}
