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

class MCPDashboardExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor
{
    public $year, $month, $company, $search;

    public function __construct($year, $month, $company, $search) {
        $this->year = $year;
        $this->month = $month;
        $this->company = $company;
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
            'GROUP CODE',
            'MCP',
            'VISITED',
            'DEVIATION',
            'PERFORMANCE',
        ];

        $data = [];
        // USERS
        $users = User::orderBy('firstname', 'ASC');
        if(!empty($this->search)) {
            $users->where('firstname', 'like', '%'.$this->search.'%')
            ->orWhere('lastname', 'like', '%'.$this->search.'%')
            ->orWhere('group_code', 'like', '%'.$this->search.'%');
        }
        $users = $users->get();

        $date_string = $this->year.'-'.($this->month < 10 ? '0'.(int)$this->month : $this->month);

        $data = [];
        foreach($users as $user) {
            // MCP
            $schedules = UserBranchSchedule::where('user_id', $user->id)
            ->whereNull('status')
            ->where('date', 'like', $date_string.'%');
            // COMPANY FILTER
            if(!empty($this->company)) {
                $schedules->whereHas('branch', function($query) {
                    $query->whereHas('account', function($qry) {
                        $qry->where('company_id', $this->company);
                    });
                });
            }

            $schedules = $schedules->get();
            // VISITED
            $mcp = 0;
            $visited = 0;
            $deviations_count = 0;
            $schedule_dates = [];
            foreach($schedules as $schedule) {
                $mcp++;
                $schedule_dates[] = $schedule->date;

                // VISITED
                $branch_logins = BranchLogin::where('user_id', $schedule->user_id)
                ->where('branch_id', $schedule->branch_id)
                ->where('time_in', 'like', $schedule->date.'%');

                // COMPANY FILTER
                if(!empty($this->company)) {
                    $branch_logins->whereHas('branch', function($query) {
                        $query->whereHas('account', function($qry) {
                            $qry->where('company_id', $this->company);
                        });
                    });
                }

                $branch_logins = $branch_logins->count();

                if($branch_logins > 0) {
                    $visited++;
                }

                // BRANCH LOGIN NOT IN SCHEDULE
                $deviations = BranchLogin::select('branch_id')->distinct()
                ->where('user_id', $schedule->user_id)
                ->where('time_in', 'like', $schedule->date.'%')
                ->where('branch_id', '<>', $schedule->branch_id);

                // COMPANY FILTER
                if(!empty($this->company)) {
                    $deviations->whereHas('branch', function($query) {
                        $query->whereHas('account', function($qry) {
                            $qry->where('company_id', $this->company);
                        });
                    });
                }

                $deviations_count += $deviations->count('branch_id');
            }

            // DEVIATIONS
            $deviations = BranchLogin::whereNotIn(DB::raw('date(time_in)'), $schedule_dates)
            ->where(DB::raw('date(time_in)'), 'like', $date_string.'%')
            ->where('user_id', $user->id);

            // COMPANY FILTER
            if(!empty($this->company)) {
                $deviations->whereHas('branch', function($query) {
                    $query->whereHas('account', function($qry) {
                        $qry->where('company_id', $this->company);
                    });
                });
            }

            $deviations_count += $deviations->count();

            $performance = 0;
            if($mcp > 0 && $visited > 0) {
                $performance = ($visited / $mcp) * 100;
            }

            $data[] = [
                $user->fullName(),
                $user->group_code,
                (string)$mcp,
                (string)$visited,
                (string)$deviations_count,
                number_format($performance, 1).'%'
            ];
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            [strtoupper(date('F Y', strtotime($date_string.'-01')))],
            $header,
            $data
        ]);
    }
}
