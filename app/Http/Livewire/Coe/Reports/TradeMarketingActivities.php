<?php

namespace App\Http\Livewire\Coe\Reports;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\DB;

class TradeMarketingActivities extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

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
        $this->pageReset();
    }

    public function setAccount($account_data) {
        $this->account_data = $account_data;
        $this->pageReset();
    }

    public function setDateFrom($date_from) {
        $this->date_from = $date_from;
        $this->pageReset();
    }

    public function setDateTo($date_to) {
        $this->date_to = $date_to;
        $this->pageReset();
    }

    public function pageReset() {
        $this->resetPage('trade-marketing-activity-page');
    }
    
    public function render()
    {

        DB::statement('SET sql_mode=(SELECT REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY",""));');

        $results = DB::table('channel_operation_trade_marketing_activities as cotma')
            ->select(
                'pafs.PAFNo',
                'pafs.title',
                DB::raw('CONCAT(pafs.start_date, " to ", pafs.end_date) as duration'),
                'pafs.support_type',
                DB::raw('CONCAT(cotmas.sku_code," ", cotmas.sku_description) as sku'),
                DB::raw('SUM(cotmas.actual) as actual'),
                DB::raw('SUM(cotmas.target_maxcap) as target'),
                DB::raw('((SUM(cotmas.actual) / SUM(cotmas.target_maxcap)) * 100) as percent')
            )
            ->join('pafs', 'pafs.PAFNo', '=', 'cotma.paf_number')
            ->join('channel_operation_trade_marketing_activity_skus as cotmas', 'cotmas.channel_operation_trade_marketing_activity_id', '=', 'cotma.id')
            ->join('channel_operations as co', 'co.id', '=', 'cotma.channel_operation_id')
            ->join('branch_logins as bl', 'bl.id', '=', 'co.branch_login_id')
            ->join('branches as b', 'b.id', '=', 'bl.branch_id')
            ->groupBy('pafs.PAFNo', 'pafs.title', 'pafs.support_type', 'sku')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('bl.user_id', $this->user_data);
            })
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('b.account_id', $this->account_data);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('date', '<=', $this->date_to);
            })
            ->paginate(10, ['*'], 'trade-marketing-activity-page');

        return view('livewire.coe.reports.trade-marketing-activities')->with([
            'results' => $results
        ]);
    }
}
