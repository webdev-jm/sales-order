<?php

namespace App\Exports;

use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MCPReportExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor
{

    protected $user_id, $date_from, $date_to;

    public function __construct($user_id, $date_from, $date_to) {
        $this->user_id = $user_id;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
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
            'BRANCH CODE',
            'BRANCH NAME',
            'LATITUDE',
            'LONGITUDE',
            'ADDRESS',
            'TIME IN',
            'TIME OUT',
            'STATUS'
        ];

        $data = [];

        // get schedule dates of user
        if(!empty($this->user_id) || !empty($this->date_from) || !empty($this->date_to)) {
            $schedules_dates = UserBranchSchedule::orderBy('user_id', 'ASC')
            ->orderBy('date', 'ASC')
            ->select('date', 'user_id')->distinct()
            ->whereNull('status');

            // get branch login not in schedule
            $deviation_logins_date = BranchLogin::orderBy('user_id', 'ASC')
            ->orderBy(DB::raw("date(time_in)"), 'ASC')
            ->select(DB::raw("date(time_in) as date"), 'user_id')->distinct();

            if(!empty($this->user_id)) {
                $schedules_dates->where('user_id', $this->user_id);
                $deviation_logins_date->where('user_id', $this->user_id);
            }

            if(!empty($this->date_from)) {
                $schedules_dates->where('date', '>=', $this->date_from);
                $deviation_logins_date->where(DB::raw("date(time_in)"), '>=', $this->date_from);
            }
            
            if(!empty($this->date_to)) {
                $schedules_dates->where('date', '<=', $this->date_to);
                $deviation_logins_date->where(DB::raw("date(time_in)"), '<=', $this->date_to);
            }

            $schedules_dates = $schedules_dates->get();

            $deviation_logins_date = $deviation_logins_date->whereNotIn(DB::raw("date(time_in)"), $schedules_dates->pluck('date'))
            ->get();

        } else {
            $schedules_dates = UserBranchSchedule::orderBy('user_id', 'ASC')
            ->orderBy('date', 'ASC')
            ->select('date', 'user_id')->distinct()
            ->whereNull('status')
            ->get();

            // get branch login not in schedule
            $deviation_logins_date = BranchLogin::orderBy('user_id', 'ASC')
            ->orderBy(DB::raw("date(time_in)"), 'ASC')
            ->select(DB::raw("date(time_in) as date"), 'user_id')->distinct()
            ->whereNotIn(DB::raw("date(time_in)"), $schedules_dates->pluck('date'))
            ->get();
        }

        // get branch login not in schedule
        $deviation_logins = [];
        foreach($deviation_logins_date as $deviation_login) {
            $logins = BranchLogin::where(DB::raw('date(time_in)'), $deviation_login->date)
            ->where('user_id', $deviation_login->user_id)->get();

            foreach($logins as $login) {
                $deviation_logins[$deviation_login->user_id][$deviation_login->date][$login->branch_id]['data'][] = $login;
                $deviation_logins[$deviation_login->user_id][$deviation_login->date][$login->branch_id]['branch_code'] = $login->branch->branch_code;
                $deviation_logins[$deviation_login->user_id][$deviation_login->date][$login->branch_id]['branch_name'] = $login->branch->branch_name;
            }
        }

        $prev_date = '';
        foreach($schedules_dates as $schedule_date) {

            if(isset($deviation_logins[$schedule_date->user_id])) {
                foreach($deviation_logins[$schedule_date->user_id] as $date => $logins) {
                    if(($prev_date == '' || $prev_date < $date) && $schedule_date->date > $date) {
                        foreach($logins as $branch_id => $login) {
                            foreach($login['data'] as $actual) {
                                $data[] = [
                                    $schedule_date->user->email,
                                    $schedule_date->user->firstname.' '.$schedule_date->user->lastname,
                                    $date,
                                    $login['branch_code'],
                                    $login['branch_name'],
                                    $actual->latitude,
                                    $actual->longitude,
                                    \App\Helpers\AppHelper::instance()->getAddress($actual->latitude, $actual->longitude),
                                    $actual->time_in,
                                    $actual->time_out,
                                    'deviated'
                                ];
                            }
                        }

                        // remove data
                        unset($deviation_logins[$schedule_date->user_id][$date]);
                    }
                }
            }

            // get schedules
            $schedules = UserBranchSchedule::where('user_id', $schedule_date->user_id)
            ->where('date', $schedule_date->date)
            ->whereNull('status')
            ->get();

            foreach($schedules as $schedule) {
                // get actual branch sign-in
                $branch_logins = BranchLogin::where('user_id', $schedule->user_id)
                ->where('branch_id', $schedule->branch_id)
                ->where('time_in', 'like', $schedule->date.'%')
                ->get();

                
                if(!empty($branch_logins->count())) {
                    foreach($branch_logins as $branch_login) {
                        $data[] = [
                            $schedule_date->user->email,
                            $schedule_date->user->firstname.' '.$schedule_date->user->lastname,
                            $schedule_date->date,
                            $schedule->branch->branch_code,
                            $schedule->branch->branch_name,
                            $branch_login->latitude,
                            $branch_login->longitude,
                            \App\Helpers\AppHelper::instance()->getAddress($branch_login->latitude, $branch_login->longitude),
                            $branch_login->time_in,
                            $branch_login->time_out,
                            'visited'
                        ];
                    }
                } else {
                    $data[] = [
                        $schedule_date->user->email,
                        $schedule_date->user->firstname.' '.$schedule_date->user->lastname,
                        $schedule_date->date,
                        $schedule->branch->branch_code,
                        $schedule->branch->branch_name,
                        '',
                        '',
                        '',
                        '',
                        '',
                        'not visited'
                    ];
                }
                
            }

            // get deviated schedules
            $deviations_data = BranchLogin::orderBy('time_in', 'ASC')
            ->where('user_id', $schedule_date->user_id)
            ->where('time_in', 'like', $schedule_date->date.'%')
            ->whereNotIn('branch_id', $schedules->pluck('branch_id'))
            ->get();

            foreach($deviations_data as $key => $deviation) {

                $data[] = [
                    $schedule_date->user->email,
                    $schedule_date->user->firstname.' '.$schedule_date->user->lastname,
                    date('Y-m-d', strtotime($deviation->time_in)),
                    $deviation->branch->branch_code,
                    $deviation->branch->branch_name,
                    $deviation->latitude,
                    $deviation->longitude,
                    \App\Helpers\AppHelper::instance()->getAddress($deviation->latitude, $deviation->longitude),
                    $deviation->time_in,
                    $deviation->time_out,
                    'diavated'
                ];

            }

        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            [''],
            $header,
            $data
        ]);
    }
}
