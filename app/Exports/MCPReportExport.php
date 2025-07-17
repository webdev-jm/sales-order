<?php

namespace App\Exports;

use App\Models\User;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;
use App\Models\ActivityPlanDetail;

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
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000');
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
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
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
            'GROUP', 'USERNAME', 'USER', 'DATE', 'ACCOUNT', 'BRANCH CODE', 'BRANCH NAME',
            'LATITUDE', 'LONGITUDE', 'ADDRESS', 'TIME IN', 'TIME OUT', 'STATUS', 'SOURCE',
            'PLANNED ACTIVITIES', 'ACTUAL ACTIVITIES', 'LOCATION', 'TIME OUT ADDRESS'
        ];

        $data = [];
        $date_range = $this->getDates($this->date_from, $this->date_to);
        
        if (empty($date_range)) {
            return new Collection([
                ['SMS - SALES MANAGEMENT SYSTEM'],
                [''],
                $header,
            ]);
        }

        // --- OPTIMIZATION: Fetch all required data upfront ---

        // Get all relevant users in one query
        $userQuery = User::query()
            ->when($this->user_id, function ($query) {
                $query->where('id', $this->user_id);
            })
            ->where(function ($query) use ($date_range) {
                $query->whereHas('schedules', function ($q) use ($date_range) {
                    $q->whereIn('date', $date_range)->whereNull('status');
                })
                ->orWhereHas('branch_logins', function ($q) use ($date_range) {
                    $q->whereIn(DB::raw('CAST(time_in AS DATE)'), $date_range);
                });
            });

        $users = $userQuery->with([
            'schedules' => function ($query) use ($date_range) {
                $query->with(['branch.account'])
                    ->whereIn('date', $date_range)
                    ->whereNull('status')
                    ->whereIn('source', ['activity-plan', 'request', 'deviation']);
            },
            'branch_logins' => function ($query) use ($date_range) {
                $query->with(['branch.account', 'operation_process', 'login_activities.activity'])
                    ->whereIn(DB::raw('CAST(time_in AS DATE)'), $date_range);
            }
        ])->get();

        $userIds = $users->pluck('id');
        
        // **FIX APPLIED HERE (1/2)**
        $activityPlans = ActivityPlanDetail::whereHas('activity_plan', function ($query) use ($userIds) {
            $query->whereIn('user_id', $userIds);
        })
        ->whereIn('date', $date_range)
        ->get()
        ->keyBy(function($plan) { // Changed from groupBy to keyBy
            // Key by date, user, and branch for direct lookup
            return $plan->date . '_' . data_get($plan, 'activity_plan.user_id') . '_' . $plan->branch_id;
        });

        // --- RETAIN ORIGINAL DESIGN: Loop through dates and then users ---
        
        foreach ($date_range as $date) {
            foreach ($users as $user) {
                
                // Filter the pre-fetched schedules for the current user and date
                $schedules_data = $user->schedules->where('date', $date);
                
                // Get all branch logins for the current user and date
                $daily_logins = $user->branch_logins->filter(function($login) use ($date) {
                    return Carbon::parse($login->time_in)->isSameDay($date);
                });
                
                foreach ($schedules_data as $schedule) {
                    $branch_logins = $daily_logins->where('branch_id', $schedule->branch_id);

                    // Find the corresponding activity plan
                    $activity_plan_key = $schedule->date . '_' . $user->id . '_' . $schedule->branch_id;

                    // **FIX APPLIED HERE (2/2)**
                    $activity_plan = $activityPlans->get($activity_plan_key); // Simplified the lookup

                    if ($branch_logins->isNotEmpty()) { // Visited
                        foreach ($branch_logins as $login) {
                            $data[] = $this->formatRowData(
                                'visited', $schedule, $login, $activity_plan, $this->getLoginActivities($login)
                            );
                        }
                    } else { // Not Visited
                        $data[] = $this->formatRowData(
                            'not visited', $schedule, null, $activity_plan, ''
                        );
                    }
                }

                // Get deviated schedules (logins for branches that were not scheduled on that day)
                $scheduled_branch_ids = $schedules_data->pluck('branch_id');
                $deviations_data = $daily_logins->whereNotIn('branch_id', $scheduled_branch_ids);
                
                foreach ($deviations_data as $login) {
                     $data[] = $this->formatRowData(
                        'deviated', null, $login, null, $this->getLoginActivities($login)
                    );
                }
            }
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            [''],
            $header,
            collect($data)
        ]);
    }
    
    private function getLoginActivities($login): string
    {
        if (!$login) return '';
        
        $activities = [];
        if (!empty($login->operation_process)) {
            $activities[] = $login->operation_process->operation_process;
        }

        if ($login->login_activities->isNotEmpty()) {
            if ($login->operation_process_id) { // If it's a standard login with activities
                foreach ($login->login_activities->where('activity_id', '!=', null) as $activity) {
                    // Added a check for the existence of the activity relationship
                    if ($activity->activity) {
                        $activities[] = $activity->activity->description . (!empty($activity->remarks) ? ': ' . $activity->remarks : '');
                    }
                }
            } else { // If it's a deviation or other type with only remarks
                $activities[] = $login->login_activities->first()->remarks;
            }
        }
        
        return implode('; ', $activities);
    }

    private function formatRowData($status, $schedule, $login, $activity_plan, $activity_str): array
    {
        $user = $schedule->user ?? $login->user;
        $branch = $schedule->branch ?? $login->branch;
        $date = $schedule->date ?? Carbon::parse($login->time_in)->toDateString();

        return [
            $user->group_code,
            $user->email,
            $user->fullName(),
            $date,
            $branch->account->short_name,
            $branch->branch_code,
            $branch->branch_name,
            $login->latitude ?? '',
            $login->longitude ?? '',
            ($login && $login->latitude) ? \App\Helpers\AppHelper::instance()->getAddress($login->latitude, $login->longitude) : '',
            !empty($login->time_in) ? date('H:i a', strtotime($login->time_in)) : '',
            !empty($login->time_out) ? date('H:i a', strtotime($login->time_out)) : '',
            $status,
            $schedule->source ?? 'unscheduled',
            $schedule->objective ?? '',
            $activity_str,
            $activity_plan->exact_location ?? '',
            ($login && $login->time_out_latitude) ? \App\Helpers\AppHelper::instance()->getAddress($login->time_out_latitude, $login->time_out_longitude) : '-'
        ];
    }

    private function getDates($start_date, $end_date): array {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        $all_dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $all_dates[] = $date->toDateString();
        }
        return $all_dates;
    }
}