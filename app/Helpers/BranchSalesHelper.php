<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class BranchSalesHelper
{
    public static function getAverageSales($branch_code, $year) {
        $result = DB::connection('stt_db')
            ->table('branch_sales as bs')
            ->select(
                DB::raw('(SUM(bs.total_net_sales) / COUNT(DISTINCT IF(bs.total_net_sales > 0, bs.month, NULL))) as average')
            )
            ->leftJoin('branches as b', 'b.id', '=', 'bs.branch_id')
            ->where('b.branch_code', $branch_code)
            ->where('bs.year', $year)
            ->where('bs.type', 'STT')
            ->first();

        $average = 0;
        if(!empty($result)) {
            $average = $result->average;
        }

        return $average;
    }
}