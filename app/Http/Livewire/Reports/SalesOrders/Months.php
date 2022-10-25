<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

class Months extends Component
{
    public $year, $month, $prev_year, $prev_month, $next_year, $next_month;

    public function selectDate($year, $month) {
        $this->year = $year;
        $this->month = $month;

        $this->setPagination();
    }

    public function setPagination() {
        if($this->month == '01') {
            $this->prev_year = $this->year - 1;
            $this->prev_month = '12';

            $this->next_year = $this->year;
            $this->next_month = '02';

        } else if($this->month == '12') {
            $this->prev_year = $this->year;
            $this->prev_month = '11';

            $this->next_year = $this->year + 1;
            $this->next_month = '01';
        } else {
            $prev_month = (int)$this->month - 1;
            $next_month = (int)$this->month + 1;

            $this->prev_year = $this->year;
            $this->prev_month = $prev_month < 10 ? '0'.$prev_month : $prev_month;

            $this->next_year = $this->year;
            $this->next_month = $next_month < 10 ? '0'.$next_month : $next_month;;
        }
    }

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }

        if(empty($this->month)) {
            $this->month = date('m');
        }

        $this->setPagination();
    }

    public function render()
    {
        return view('livewire.reports.sales-orders.months');
    }
}
