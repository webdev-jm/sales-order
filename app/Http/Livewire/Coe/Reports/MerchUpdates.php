<?php

namespace App\Http\Livewire\Coe\Reports;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

class MerchUpdates extends Component
{
    public $user_data, $account_data;

    protected $listeners = [
        'setUser' => 'setUser', 
        'setAccount' => 'setAccount',
    ];

    public function setUser($user_data) {
        $this->user_data = $user_data;
    }

    public function setAccount($account_data) {
        $this->account_data = $account_data;
    }

    public function render()
    {
        $results = DB::table('channel_operations as co')
            ->select(
                'a.short_name',
                DB::raw('SUM(comu.actual) as actual'),
                DB::raw('SUM(comu.target) as target'),
                DB::raw('COUNT(DISTINCT
                    CASE
                        WHEN comu.status = "ON BOARD" THEN bl.branch_id
                        WHEN comu.status = "ON BOARD" THEN DATE(bl.time_in)
                        WHEN comu.status = "ON BOARD" THEN bl.user_id
                    END
                ) as actual_doors'),
                DB::raw('COUNT(DISTINCT bl.branch_id, DATE(bl.time_in), bl.user_id) as target_doors')
            )
            ->leftJoin('channel_operation_merch_updates as comu', 'comu.channel_operation_id', '=', 'co.id')
            ->leftJoin('branch_logins as bl', 'bl.id', '=', 'co.branch_login_id')
            ->leftJoin('users as u', 'u.id', '=', 'bl.user_id')
            ->leftJoin('branches as b', 'b.id', '=', 'bl.branch_id')
            ->leftJoin('accounts as a', 'a.id', '=', 'b.account_id')
            ->groupBy('a.short_name')
            ->orderBy('actual', 'DESC')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('u.id', $this->user_data);
            })
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('a.id', $this->account_data);
            })
            ->get();

        $data = [];
        $totals = [];
        foreach($results as $result) {
            $hc_deployed = 0;
            if(!empty($result->actual) && !empty($result->target)) {
                $hc_deployed = ($result->actual / $result->target) * 100;
            }

            $doors_manned = 0;
            if(!empty($result->actual_doors) && !empty($result->target_doors)) {
                $doors_manned = ($result->actual_doors / $result->target_doors) * 100;
            }

            $data[$result->short_name] = [
                'actual' => $result->actual,
                'target' => $result->target,
                'actual_doors' => $result->actual_doors,
                'target_doors' => $result->target_doors,
                'hc_deployed' => $hc_deployed,
                'doors_manned' => $doors_manned
            ];

            $totals['actual'] = isset($totals['actual']) ? $totals['actual'] + $result->actual : $result->actual;
            $totals['target'] = isset($totals['target']) ? $totals['target'] + $result->target : $result->target;
            $totals['actual_doors'] = isset($totals['actual_doors']) ? $totals['actual_doors'] + $result->actual_doors : $result->actual_doors;
            $totals['target_doors'] = isset($totals['target_doors']) ? $totals['target_doors'] + $result->target_doors : $result->target_doors;
        }
        
        $hc_deployed_total = 0;
        if(!empty($totals['actual']) && !empty($totals['target'])) {
            $hc_deployed_total = ($totals['actual'] / $totals['target']) * 100;
        }

        $doors_manned_total = 0;
        if(!empty($totals['actual_doors']) && !empty($totals['target_doors'])) {
            $doors_manned_total = ($totals['actual_doors'] / $totals['target_doors']) * 100;
        }

        $totals['hc_deployed'] = $hc_deployed_total;
        $totals['doors_manned'] = $doors_manned_total;

        return view('livewire.coe.reports.merch-updates')->with([
            'data' => $data,
            'totals' => $totals
        ]);
    }
}
