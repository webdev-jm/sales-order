<?php

namespace App\Http\Livewire\Coe\Reports;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

class TradeDisplays extends Component
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
        $trade_reports = [
            'PLANOGRAM',
            'BEVI PRICING',
            'OSA - BATH',
            'OSA - FACE',
            'OSA - BODY',
        ];

        $result = DB::table('channel_operation_trade_displays as cotd')
            ->select(
                DB::raw('COUNT(IF(cotd.planogram = "IMPLEMENTED", cotd.planogram, NULL)) as planogram_actual'),
                DB::raw('COUNT(cotd.planogram) as planogram_target'),
                DB::raw('COUNT(IF(cotd.bevi_pricing = "FOLLOW SRP", cotd.bevi_pricing, NULL)) as pricing_actual'),
                DB::raw('COUNT(cotd.bevi_pricing) as pricing_target'),
                DB::raw('SUM(cotd.osa_bath_actual) as bath_actual'),
                DB::raw('SUM(cotd.osa_bath_target) as bath_target'),
                DB::raw('SUM(cotd.osa_face_actual) as face_actual'),
                DB::raw('SUM(cotd.osa_face_target) as face_target'),
                DB::raw('SUM(cotd.osa_body_actual) as body_actual'),
                DB::raw('SUM(cotd.osa_body_target) as body_target')
            )
            ->join('channel_operations as co', 'co.id', '=', 'cotd.channel_operation_id')
            ->join('branch_logins as bl', 'bl.id', '=', 'co.branch_login_id')
            ->join('branches as b', 'b.id', '=', 'bl.branch_id')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('bl.user_id', $this->user_data);
            })
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('b.account_id', $this->account_data);
            })
            ->first();

        $data = array();
        $total_actual = 0;
        $total_target = 0;

        $actual_data = [];
        $target_data = [];
        foreach($trade_reports as $report) {

            $actual = 0;
            $target = 0;
            switch($report) {
                case 'PLANOGRAM':
                    $actual = $result->planogram_actual;
                    $target = $result->planogram_target;
                    break;
                case 'BEVI PRICING':
                    $actual = $result->pricing_actual;
                    $target = $result->pricing_target;
                    break;
                case 'OSA - BATH':
                    $actual = $result->bath_actual;
                    $target = $result->bath_target;
                    break;
                case 'OSA - FACE':
                    $actual = $result->face_actual;
                    $target = $result->face_target;
                    break;
                case 'OSA - BODY':
                    $actual = $result->body_actual;
                    $target = $result->body_target;
                    break;
            }

            $vs_target = 0;
            if(!empty($actual) && !empty($target)) {
                $vs_target = ($actual / $target) * 100;
            }

            $data[$report] = [
                'actual' => $actual,
                'target' => $target,
                'vs_target' => $vs_target,
            ];

            $total_actual += $actual;
            $total_target += $target;

            $actual_data[] = (int)$actual;
            $target_data[] = (int)$target;
        }

        $total_vs_target = 0;
        if(!empty($total_actual) && !empty($total_target)) {
            $total_vs_target = ($total_actual / $total_target) * 100;
        }

        $totals = [
            'actual' => $total_actual,
            'target' => $total_target,
            'vs_target' => $total_vs_target
        ];

        $chart_data = [
            [
                'name' => 'actual',
                'data' => $actual_data
            ],
            [
                'name' => 'target',
                'data' => $target_data
            ],
        ];

        $this->dispatchBrowserEvent('setChart', [
            'category' => $trade_reports,
            'data' => $chart_data
        ]);

        return view('livewire.coe.reports.trade-displays')->with([
            'trade_reports' => $trade_reports,
            'data' => $data,
            'totals' => $totals,
            'chart_data' => $chart_data
        ]);
    }
}
