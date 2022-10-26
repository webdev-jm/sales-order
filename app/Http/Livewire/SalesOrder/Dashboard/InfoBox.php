<?php

namespace App\Http\Livewire\SalesOrder\Dashboard;

use Livewire\Component;
use App\Models\SalesOrder;

class InfoBox extends Component
{

    public $sales_orders_count, $grand_total, $grand_total_discounted;
    public $month, $year, $days;

    protected $listeners = [
        'setBoxDate' => 'setDate'
    ];

    public function setDate($year, $month, $days) {
        $this->year = $year;
        $this->month = $month;
        $this->days = $days;

        $this->mount();
    }

    public function mount() {
        if(!empty($this->year) && !empty($this->month) && empty($this->days)) {
            $date_string = $this->year.'-'.$this->month;

            $sales_order = SalesOrder::where('order_date', 'like', $date_string.'-%')
            ->where('status', 'finalized')
            ->where('upload_status', 1);
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
                $sales_order = SalesOrder::whereIn('order_date', $dates)
                ->where('status', 'finalized')
                ->where('upload_status', 1);
            } else {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                } else {
                    $date_string = date('Y-m');
                }

                $sales_order = SalesOrder::where('order_date', 'like', $date_string.'-%')
                ->where('status', 'finalized')
                ->where('upload_status', 1);
            }

        } else {
            $date_string = date('Y-m-d');

            $sales_order = SalesOrder::where('order_date', $date_string)
            ->where('status', 'finalized')
            ->where('upload_status', 1);
        }

        $this->sales_orders_count = $sales_order->count();
        $this->grand_total = $sales_order->sum('total_sales');
        $this->grand_total_discounted = $sales_order->sum('grand_total');
    }

    public function render()
    {
        return view('livewire.sales-order.dashboard.info-box');
    }
}
