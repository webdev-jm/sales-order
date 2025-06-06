<?php

namespace App\Http\Livewire\Coe\Reports;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

class ExtraDisplays extends Component
{
    public $user_data, $account_data;
    public $date_from, $date_to;

    protected $listeners = [
        'setUser' => 'setUser', 
        'setAccount' => 'setAccount',
        'setDateFrom' => 'setDateFrom',
        'setDateTo' => 'setDateTo',
    ];

    public function setUser($user_data) {
        $this->user_data = $user_data;
    }

    public function setAccount($account_data) {
        $this->account_data = $account_data;
    }

    public function setDateFrom($date_from) {
        $this->date_from = $date_from;
    }

    public function setDateTo($date_to) {
        $this->date_to = $date_to;
    }

    public function render()
    {
        $results = DB::table('channel_operation_extra_displays as coed')
            ->select(
                'a.short_name',
                DB::raw('COUNT(DISTINCT bl.branch_id, DATE(bl.time_in), bl.user_id) as doors'),
                DB::raw('SUM(coed.rate_per_month) as rate'),
                DB::raw('SUM(coed.amount) as amount')
            )
            ->join('channel_operations as co', 'co.id', '=', 'coed.channel_operation_id')
            ->join('branch_logins as bl', 'bl.id', '=', 'co.branch_login_id')
            ->join('branches as b', 'b.id', '=', 'bl.branch_id')
            ->join('accounts as a', 'a.id', '=', 'b.account_id')
            ->groupBy('a.short_name')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('bl.user_id', $this->user_data);
            })
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('a.id', $this->account_data);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('date', '<=', $this->date_to);
            })
            ->get();

        return view('livewire.coe.reports.extra-displays')->with([
            'results' => $results
        ]);
    }
}
