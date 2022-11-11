<?php

namespace App\Http\Livewire\SalesOrder\Dashboard;

use Livewire\Component;

use App\Exports\SODashboardExport;
use Maatwebsite\Excel\Facades\Excel;

class Months extends Component
{
    public $year, $month, $days, $total_days, $prev_year, $prev_month, $next_year, $next_month;

    public function export() {
        return Excel::download(new SODashboardExport($this->year, $this->month, $this->days), 'SO Dashboard'.time().'.xlsx');
    }

    public function selectDate($year, $month) {
        $this->year = $year;
        $this->month = $month;

        $this->total_days = date('t', strtotime($year.'-'.$month.'-01'));

        $this->setPagination();
        $this->emit('setBoxDate', $year, $month, $this->days);
        $this->emit('setOrderList', $year, $month, $this->days);
    }

    public function selectDay($day) {
        if(isset($this->days[$this->year][(int)$this->month]) && in_array($day, $this->days[$this->year][(int)$this->month])) {
            unset($this->days[$this->year][(int)$this->month][array_search($day, $this->days[$this->year][(int)$this->month])]);
        } else {
            $this->days[$this->year][(int)$this->month][] = $day;
        }

        $this->emit('setBoxDate', $this->year, $this->month, $this->days);
        $this->emit('setOrderList', $this->year, $this->month, $this->days);
    }

    public function clearDay() {
        $this->reset('days');
        $this->emit('setBoxDate', $this->year, $this->month, $this->days);
        $this->emit('setOrderList', $this->year, $this->month, $this->days);
    }

    public function setPagination() {
        if((int)$this->month == 1) {
            $this->prev_year = $this->year - 1;
            $this->prev_month = 12;

            $this->next_year = $this->year;
            $this->next_month = 2;

        } else if($this->month == 12) {
            $this->prev_year = $this->year;
            $this->prev_month = 11;

            $this->next_year = $this->year + 1;
            $this->next_month = 1;
        } else {
            $prev_month = (int)$this->month - 1;
            $next_month = (int)$this->month + 1;

            $this->prev_year = $this->year;
            $this->prev_month = $prev_month;

            $this->next_year = $this->year;
            $this->next_month = $next_month;
        }
    }

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }

        if(empty($this->month)) {
            $this->month = date('m');
        }

        if(empty($this->day)) {
            $this->days[$this->year][(int)$this->month][] = date('d');
        }

        $month = $this->month < 10 ? '0-'.$this->month : $this->month;
        $this->total_days = date('t', strtotime($this->year.'-'.$month.'-01'));

        $this->setPagination();
    }

    public function render()
    {
        return view('livewire.sales-order.dashboard.months');
    }
}
