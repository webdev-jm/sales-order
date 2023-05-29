<?php

namespace App\Http\Livewire\ProductivityReport;

use Livewire\Component;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class Report extends Component
{
    public $date_from, $date_to;

    public function getNumberOfWeeksInMonth($year, $month) {
        // Create the start and end dates of the month
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('last day of this month');

        // Calculate the number of days in the month
        $daysInMonth = $endDate->format('j');

        // Calculate the number of weeks
        $numOfWeeks = ceil($daysInMonth / 7);

        return $numOfWeeks;
    }

    public function mount() {
        $this->date_from = date('Y-m-d');
    }

    public function render()
    {
        DB::statement('SET sql_mode=(SELECT REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY",""));');

        $results = DB::table('productivity_report_data AS prd')
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('CEIL((DAYOFMONTH(date) - WEEKDAY(date)) / 7) AS week'),
                'date',
                'a.account_name',
                'c.classification_name',
                DB::raw("CONCAT(s.code, ' (', s.name, ')') as name"),
                DB::raw('COUNT(DISTINCT prd.branch_id) as planned'),
                DB::raw('SUM(prd.visited) as visited'),
                DB::raw('SUM(prd.sales) as sales'),
                DB::raw('COUNT(DISTINCT IF(prd.sales > 0, prd.branch_id, NULL)) AS productive_calls'),
            )
            ->leftJoin('branches AS b', 'b.id', '=', 'prd.branch_id')
            ->leftJoin('accounts AS a', 'a.id', '=', 'b.account_id')
            ->leftJoin('classifications AS c', 'c.id', '=', 'b.classification_id')
            ->leftJoin('salesmen AS s', 's.id', '=', 'prd.salesman_id')
            ->when(!empty($this->date_from), function($query) {
                $query->where('date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('date', '<=', $this->date_to);
            })
            ->groupBy('month', 'week', 'date', 'a.account_name', 'c.classification_name', 'name')
            ->get();

        $data_arr = array();
        foreach ($results as $result) {
            $week = $result->week;
            $name = $result->name;
            $val2 = [
                'date' => $result->date,
                'account_name' => $result->account_name,
                'classification' => $result->classification_name,
                'planned' => $result->planned,
                'visited' => $result->visited,
                'sales' => $result->sales,
                'productive_calls' => $result->productive_calls
            ];

            $date = Carbon::create($val2['date']);
            $weekday = $date->format('l');

            $data_arr[$week][$name]['account'] = $val2['account_name'];
            $data_arr[$week][$name]['planned'] = ($data_arr[$week][$name]['planned'] ?? 0) + $val2['planned'];
            $data_arr[$week][$name][$weekday] = ($data_arr[$week][$name][$weekday] ?? 0) + $val2['visited'];
            $data_arr[$week][$name]['total_visited'] = ($data_arr[$week][$name]['total_visited'] ?? 0) + $val2['visited'];
            $data_arr[$week][$name]['productive_calls'] = ($data_arr[$week][$name]['productive_calls'] ?? 0) + $val2['productive_calls'];
        }

        foreach($data_arr as $week => $data) {
            foreach($data as $name => $val) {
                $call_rate = 0;
                if(!empty($val['planned']) && !empty($val['total_visited'])) {
                    $call_rate = ($val['total_visited'] / $val['planned']) * 100;
                }

                $hit_calls = 0;
                if(!empty($val['productive_calls']) && !empty($val['total_visited'])) {
                    $hit_calls = ($val['productive_calls'] / $val['total_visited']) * 100;
                }

                $data_arr[$week][$name]['call_rate'] = $call_rate;
                $data_arr[$week][$name]['hit_calls'] = $hit_calls;
                $data_arr[$week][$name]['gaps_vs_planned'] = $val['planned'] - $val['productive_calls'];
            }
        }

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Usage example
        $year = 2023;
        $month = 5; // May

        $numOfWeeks = $this->getNumberOfWeeksInMonth($year, $month);

        return view('livewire.productivity-report.report')->with([
            'week_number' => $numOfWeeks,
            'days_of_week' => $daysOfWeek,
            'data' => $data_arr
        ]);
    }
}
