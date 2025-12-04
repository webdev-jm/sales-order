<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    ShouldAutoSize,
    WithStyles,
    WithProperties,
    WithBackgroundColor,
    WithChunkReading,
    WithStrictNullComparison
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use App\Models\{
    ActivityPlanDetailTrip,
    Department,
    DepartmentStructure
};

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize', '1000000');
ini_set('pdo_sqlsrv.client_buffer_max_kb_size', '1000000');

class TripExport implements
    FromCollection,
    ShouldAutoSize,
    WithStyles,
    WithProperties,
    WithBackgroundColor,
    WithStrictNullComparison,
    WithChunkReading
{
    protected $search;
    protected $date_from;
    protected $date_to;
    protected $user_id;
    protected $company;

    public function __construct($search, $date_from, $date_to, $user_id, $company)
    {
        $this->search = trim($search);
        $this->date_from = trim($date_from);
        $this->date_to = trim($date_to);
        $this->user_id = trim($user_id);
        $this->company = trim($company);
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function batchSize(): int
    {
        return 200;
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
            3 => [
                'font' => ['bold' => true, 'size' => 12],
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

    public function collection()
    {
        $user = auth()->user();
        $canSeeInvoice = $user->can('trip invoice');

        $header = [
            'YEAR', 'TRIP CODE', 'USER', 'FROM', 'TO', 'DEPARTURE', 'RETURN', 'TRIP TYPE',
            'PASSENGER', 'PURPOSE', 'AMOUNT', 'STATUS', 'SOURCE', 'CREATED AT',
            'APPROVED BY IMM. SUPERIOR', 'APPROVED BY FINANCE'
        ];

        if ($canSeeInvoice) {
            array_splice($header, 11, 0, ['INVOICE NUMBER', 'SUPPLIER']);
        }

        $trips = $this->getTrips();

        $data = [];
        foreach ($trips as $trip) {
            $superior_approval = $trip->approvals()
                ->where('status', 'approved by imm. superior')
                ->latest('created_at')
                ->first();

            $finance_approval = $trip->approvals()
                ->where('status', 'approved by finance')
                ->latest('created_at')
                ->first();

            $row = [
                date('Y', strtotime($trip->departure)),
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

            if ($canSeeInvoice) {
                array_splice($row, 11, 0, [$trip->invoice_number, $trip->supplier]);
            }

            $data[] = $row;
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            ['TRIP REQUEST LIST'],
            $header,
            ...$data
        ]);
    }

    protected function getTrips()
    {
        $user = auth()->user();
        $query = ActivityPlanDetailTrip::query()->orderByDesc('id');

        if ($user->can('trip finance approver') || $user->hasRole('finance') || $user->hasRole('superadmin')) {
            $this->applyFilters($query);
        } else {
            $users_ids = $this->getSubordinateUserIds($user);
            $query->where(function ($q) use ($user, $users_ids) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('user_id', $users_ids);
            });
            $this->applyFilters($query, false);
        }

        return $query->get();
    }

    protected function applyFilters($query, $includeUserId = true)
    {
        if (!empty($this->date_from) && !empty($this->date_to)) {
            $query->where(function ($q) {
                $q->whereBetween('departure', [$this->date_from, $this->date_to])
                  ->orWhereBetween('return', [$this->date_from, $this->date_to]);
            });
        } elseif (!empty($this->date_from)) {
            $query->where(function ($q) {
                $q->where('departure', '>=', $this->date_from)
                  ->orWhere('return', '>=', $this->date_from);
            });
        } elseif (!empty($this->date_to)) {
            $query->where(function ($q) {
                $q->where('departure', '<=', $this->date_to)
                  ->orWhere('return', '<=', $this->date_to);
            });
        }

        if ($includeUserId && !empty($this->user_id)) {
            $query->where('user_id', $this->user_id);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('from', 'like', '%' . $this->search . '%')
                  ->orWhere('to', 'like', '%' . $this->search . '%')
                  ->orWhere('status', 'like', '%' . $this->search . '%')
                  ->orWhere('trip_number', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->company)) {
            $group_codes = [];
            if ($this->company === 'bevi') {
                $group_codes = ['CMD', 'NKA'];
            } elseif ($this->company === 'beva') {
                $group_codes = ['RD'];
            }
            if ($group_codes) {
                $query->whereHas('user', function ($q) use ($group_codes) {
                    $q->whereIn('group_code', $group_codes);
                });
            }
        }
    }

    protected function getSubordinateUserIds($user)
    {
        $users_ids = [];

        // Department admin users
        $departments = Department::where('department_admin_id', $user->id)->get();
        foreach ($departments as $department) {
            foreach ($department->users as $deptUser) {
                $users_ids[] = $deptUser->id;
            }
        }

        // User subordinates
        $subordinate_ids = $user->getSubordinateIds();
        foreach ($subordinate_ids as $ids) {
            foreach ($ids as $id) {
                $users_ids[] = $id;
            }
        }

        // Subordinates via department structure
        $structures = DepartmentStructure::where('user_id', $user->id)->get();
        foreach ($structures as $structure) {
            $structure_subs = DepartmentStructure::whereRaw('FIND_IN_SET(' . $structure->id . ', reports_to_ids) > 0')->get();
            foreach ($structure_subs as $sub) {
                $users_ids[] = $sub->user_id;
            }
        }

        return array_unique($users_ids);
    }
}
