<?php

namespace App\Http\Livewire\Reports\SalesOrders;

use Livewire\Component;

use App\Models\User;

class Months extends Component
{
    public $year, $month, $prev_year, $prev_month, $next_year, $next_month;
    public $group_code;

    protected $queryString = [
        'year',
        'month',
        'group_code'
    ];

    public function selectGroup($code) {
        if($this->group_code == $code) {
            $this->reset('group_code');
        } else {
            $this->group_code = $code;
        }

        return redirect()->route('report.sales-order', [
            'year' => $this->year,
            'month' => $this->month,
            'group_code' => $this->group_code
        ]);
        
    }

    public function selectDate($year, $month) {
        $this->year = $year;
        $this->month = $month;

        $this->setPagination();

        return redirect()->route('report.sales-order', [
            'year' => $this->year,
            'month' => $this->month,
            'group_code' => $this->group_code
        ]);
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
        $group_codes = User::select('group_code')->distinct()
        ->whereNotNull('group_code')
        ->orderBy('group_code', 'ASC')
        ->whereHas('account_logins', function($query) {
            $query->whereHas('sales_orders');
        })
        ->get();

        return view('livewire.reports.sales-orders.months')->with([
            'group_codes' => $group_codes
        ]);
    }
}
