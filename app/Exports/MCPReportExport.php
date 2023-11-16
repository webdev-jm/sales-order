<?php

namespace App\Exports;

use App\Models\User;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Carbon\Carbon;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class MCPReportExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithStrictNullComparison, WithChunkReading
{

    protected $user_id, $date_from, $date_to;

    public function __construct($user_id, $date_from, $date_to) {
        $this->user_id = $user_id;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
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
            'title'          => 'MCP Reports',
            'description'    => 'List of scheduled branches and visit informations',
            'subject'        => 'MCP Reports',
            'keywords'       => 'mcp reports,export,spreadsheet',
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
            'USERNAME',
            'USER',
            'DATE',
            'ACCOUNT',
            'BRANCH CODE',
            'BRANCH NAME',
            'LATITUDE',
            'LONGITUDE',
            'ADDRESS',
            'TIME IN',
            'TIME OUT',
            'STATUS',
            'SOURCE',
        ];

        $data = [];

        $date_arr = $this->getDates($this->date_from, $this->date_to);
        
        if(!empty($date_arr)) {
            foreach($date_arr as $date) {
                $users_arr = User::where(function($query) use($date) {
                        $query->whereHas('schedules', function($query) use($date) {
                            $query->whereNull('status')
                                ->where('date', $date);
                        })
                        ->orWhereHas('branch_logins', function($query) use($date) {
                            $query->where(DB::raw('date(time_in)'), $date);
                        });
                    })
                    ->when(!empty($this->user_id), function($query) {
                        $query->where('id', $this->user_id);
                    })
                    ->get();

                if(!empty($users_arr)) {
                    foreach($users_arr as $user) {
                        // get schedules
                        $schedules_data = UserBranchSchedule::with('branch', 'branch.account')
                            ->where('date', $date)
                            ->whereNull('status')
                            ->where(function($query) {
                                $query->where('source', 'activity-plan')
                                    ->orWhere('source', 'request')
                                    ->orWhere('source', 'deviation');
                            })
                            ->where('user_id', $user->id)
                            ->get();
                        
                        foreach($schedules_data as $schedule) {
                            // get actual branch sign-in
                            $branch_logins = BranchLogin::where('user_id', $schedule->user_id)
                                ->where('branch_id', $schedule->branch_id)
                                ->where(DB::raw('date(time_in)'), $schedule->date)
                                ->get();

                            if(!empty($branch_logins->count())) {
                                foreach($branch_logins as $login) {
                                    $data[] = [
                                        $schedule->user->email,
                                        $schedule->user->fullName(),
                                        $date,
                                        $schedule->branch->account->short_name,
                                        $schedule->branch->branch_code,
                                        $schedule->branch->branch_name,
                                        $login->latitude,
                                        $login->longitude,
                                        \App\Helpers\AppHelper::instance()->getAddress($login->latitude, $login->longitude),
                                        $login->time_in,
                                        $login->time_out,
                                        'visited',
                                        $schedule->source
                                    ];
                                }
                                
                            } else { // unvisited
                                $data[] = [
                                    $schedule->user->email,
                                    $schedule->user->fullName(),
                                    $schedule->date,
                                    $schedule->branch->account->short_name,
                                    $schedule->branch->branch_code,
                                    $schedule->branch->branch_name,
                                    '',
                                    '',
                                    '',
                                    '',
                                    '',
                                    'not visited',
                                    $schedule->source
                                ];
                            }
                        }

                        // get deviated schedules
                        $deviations_data = BranchLogin::with('user', 'branch', 'branch.account')
                            ->orderBy('time_in', 'ASC')
                            ->where('user_id', $user->id)
                            ->where('time_in', 'like', $date.'%')
                            ->whereNotIn('branch_id', $schedules_data->pluck('branch_id'))
                            ->get();
                        
                        foreach($deviations_data as $login) {
                            $data[] = [
                                $login->user->email,
                                $login->user->fullName(),
                                date('Y-m-d', strtotime($login->time_in)),
                                $login->branch->account->short_name,
                                $login->branch->branch_code,
                                $login->branch->branch_name,
                                $login->latitude,
                                $login->longitude,
                                \App\Helpers\AppHelper::instance()->getAddress($login->latitude, $login->longitude),
                                $login->time_in,
                                $login->time_out,
                                'deviated',
                                'unscheduled',
                            ];
                        }
                    }
                }
            }
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            [''],
            $header,
            $data
        ]);
    }

    private function getDates($start_date, $end_date) {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        $all_dates = array();
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $all_dates[] = $date->toDateString();
        }

        return $all_dates;
    }
}
