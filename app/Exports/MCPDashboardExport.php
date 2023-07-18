<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\User;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class MCPDashboardExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor
{
    public $year, $month, $search;

    public function __construct($year, $month, $search) {
        $this->year = $year;
        $this->month = $month;
        $this->search = $search;
    }

    public function backgroundColor()
    {
        return null;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'Sales Management System',
            'lastModifiedBy' => 'SMS',
            'title'          => 'MCP Dashboard Reports',
            'description'    => 'List of users and MCP performance',
            'subject'        => 'MCP Dashboard Reports',
            'keywords'       => 'mcp dashboard reports,export,spreadsheet',
            'category'       => 'Schedules',
            'manager'        => 'SMS Application',
            'company'        => 'BEVI',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'E7FDEC']
                ]
            ],
            // header
            3 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'ddfffd']
                ]
            ],
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $header = [
            'USER',
            'MCP',
            'VISITED',
            'DEVIATION',
            'SCHEDULE REQUEST',
            'PERFORMANCE',
        ];

        $date_string = $this->year.'-'.($this->month < 10 ? '0'.(int)$this->month : $this->month);

        $query = UserBranchSchedule::query()
                ->selectRaw('u.id as uid')
                ->selectRaw('CONCAT(u.firstname, " ", u.lastname) as name')
                ->selectRaw('COUNT(IF(status IS NULL, user_branch_schedules.id, NULL)) as schedule_count')
                ->selectRaw('COUNT(IF(status IS NULL AND source = "deviation", user_branch_schedules.id, NULL)) as deviation_count')
                ->selectRaw('COUNT(IF(status IS NULL AND source = "request", user_branch_schedules.id, NULL)) as request_count')
            ->join('users as u', 'u.id', '=', 'user_branch_schedules.user_id')
            ->where(DB::raw('MONTH(date)'), $this->month)
            ->where(DB::raw('YEAR(date)'), $this->year)
            ->orderBy('name')
            ->groupBy(['uid', 'name']);

        $schedule_results = $query->get();

        $data = array();
        foreach($schedule_results as $result) {
            $branch_logins = BranchLogin::query()
                ->selectRaw('DISTINCT branch_id, DATE(time_in) as date')
                ->where('user_id', $result->uid)
                ->where(DB::raw('MONTH(time_in)'), $this->month)
                ->where(DB::raw('YEAR(time_in)'), $this->year)
                ->get();
    
            $visited_count = $branch_logins->filter(function ($branch_login) use($result) {
                return UserBranchSchedule::where('user_id', $result->uid)
                    ->where('branch_id', $branch_login->branch_id)
                    ->where('date', $branch_login->date)
                    ->whereNull('status')
                    ->exists();
            })->count();

            $user_data[$result->uid]['visited'] = $visited_count;
            $unscheduled_count = $branch_logins->count() - $visited_count;

            $deviation = $result->deviation_count + $unscheduled_count;
            $user_data[$result->uid]['deviation'] = $deviation;

            
            $performance = 0;
            if(!empty($result->schedule_count) && !empty($visited_count)) {
                $performance = ($visited_count / $result->schedule_count) * 100;
            }

            $data[] = [
                $result->name,
                (string)$result->schedule_count,
                (string)$visited_count,
                (string)$deviation,
                (string)$result->request_count,
                number_format($performance, 1).'%'
            ];
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            ['MCP PERFORMANCE FOR '.strtoupper(date('F Y', strtotime($date_string.'-01')))],
            $header,
            $data
        ]);
    }
}
