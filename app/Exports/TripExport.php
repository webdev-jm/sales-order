<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Illuminate\Support\Collection;

use App\Models\ActivityPlanDetailTrip;
use App\Models\Department;
use App\Models\DepartmentStructure;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class TripExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithStrictNullComparison, WithChunkReading
{
    public $search;
    public $date;
    public $user_id;

    public function __construct($search, $date, $user_id) {
        $this->search = $search;
        $this->date = $date;
        $this->user_id = $user_id;
    }

    public function chunkSize(): int
    {
        return 200; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 200; // Number of rows per batch
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
            'title'          => 'SMS Trip Requests',
            'description'    => 'SMS List of branches',
            'subject'        => 'SMS Trip Request List',
            'keywords'       => 'SMS Trips list,export,spreadsheet',
            'category'       => 'Trip Requests',
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
            'TRIP CODE',
            'USER',
            'FROM',
            'TO',
            'DEPARTURE',
            'RETURN',
            'TRIP TYPE',
            'PASSENGER',
            'PURPOSE',
            'AMOUNT',
            'STATUS',
            'SOURCE',
            'CREATED AT',
            'APPROVED BY IMM. SUPERIOR',
            'APPROVED BY FINANCE'
        ];

        if(auth()->user()->can('trip finance approver') || auth()->user()->hasRole('superadmin')) { // for finance view or administrators
            $trips = ActivityPlanDetailTrip::orderBy('id', 'DESC')
                ->when(!empty($this->date), function($query) {
                    $query->where(function($qry) {
                        $qry->where('departure', $this->date)
                            ->orWhere('return', $this->date);
                    });
                })
                ->when(!empty($this->user_id), function($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->when(!empty($this->search), function($query) {
                    $query->where(function($qry) {
                        $qry->where('from', 'like', '%'.$this->search.'%')
                            ->orWhere('to', 'like', '%'.$this->search.'%')
                            ->orWhere('status', 'like', '%'.$this->search.'%')
                            ->orWhere('trip_number', 'like', '%'.$this->search.'%');
                    });
                })
                ->get();
        } else {
            // check if user is admin of a department
            $departments = Department::where('department_admin_id', auth()->user()->id)->get();
            // get all users under departments
            $users_ids = array();
            foreach($departments as $department) {
                $users = $department->users;
                foreach($users as $user) {
                    $users_ids[] = $user->id;
                }
            }
            // get user subordiates
            $subordinate_ids = auth()->user()->getSubordinateIds();
            if(!empty($subordinate_ids)) {
                foreach($subordinate_ids as $level => $ids) {
                    foreach($ids as $id) {
                        $users_ids[] = $id;
                    }
                }
            }

            // get subordinates
            $structures = DepartmentStructure::where('user_id', auth()->user()->id)
                ->get();
            if(!empty($structures)) {
                foreach($structures as $structure) {
                    $structure_sub = DepartmentStructure::whereRaw('FIND_IN_SET('.$structure->id.', reports_to_ids) > 0')
                        ->get();
                    if(!empty($structure_sub)) {
                        foreach($structure_sub as $sub) {
                            $users_ids[] = $sub->user_id;
                        }
                    }
                }
            }

            $users_ids = array_unique($users_ids);

            $trips = ActivityPlanDetailTrip::orderBy('id', 'DESC')
                ->where(function($query) use($users_ids) {
                    $query->where('user_id', auth()->user()->id)
                        ->orWhereIn('user_id', $users_ids);
                })
                ->when(!empty($this->date), function($query) {
                    $query->where(function($qry) {
                        $qry->where('departure', $this->date)
                            ->orWhere('return', $this->date);
                    });
                })
                ->when(!empty($this->search), function($query) {
                    $query->where(function($qry) {
                        $qry->where('from', 'like', '%'.$this->search.'%')
                            ->orWhere('to', 'like', '%'.$this->search.'%')
                            ->orWhere('status', 'like', '%'.$this->search.'%')
                            ->orWhere('trip_number', 'like', '%'.$this->search.'%');
                    });
                })
                ->when(!empty($this->user_id), function($query) {
                    $query->where('user_id', $this->user_id);
                })
                ->get();
        }

        $data = [];
        foreach($trips as $trip) {
            $superior_approval = $trip->approvals()
                ->orderBy('created_at', 'DESC')
                ->where('status', 'approved by imm. superior')
                ->first();

            $finance_approval = $trip->approvals()  
                ->orderBy('created_at', 'DESC')
                ->where('status', 'approved by finance')
                ->first();

            $data[] = [
                $trip->trip_number,
                $trip->user->fullName(),
                $trip->from,
                $trip->to,
                $trip->departure,
                $trip->return,
                $trip->trip_type,
                $trip->passenger,
                $trip->purpose,
                $trip->amount,
                $trip->status,
                $trip->source,
                $trip->created_at,
                $superior_approval->created_at ?? '',
                $finance_approval->created_at ?? '',
            ];
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'], 
            ['TRIP REQUEST LIST'],
            $header,
            $data
        ]);
    }
}
