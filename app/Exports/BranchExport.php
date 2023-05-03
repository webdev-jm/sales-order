<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Illuminate\Support\Collection;

use App\Models\Branch;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class BranchExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor
{
    public $search;

    public function __construct($search) {
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
            'title'          => 'SMS Braches',
            'description'    => 'SMS List of branches',
            'subject'        => 'SMS Branch List',
            'keywords'       => 'SMS branch list,export,spreadsheet',
            'category'       => 'Branches',
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
            'VENDOR',
            'ACCOUNT CODE',
            'SHORT NAME',
            'ACCOUNT NAME',
            'BRANCH CODE',
            'BRANCH NAME',
            'CLASSIFICATION CODE',
            'CLASSIFICATION NAME',
            'REGION',
            'AREA CODE',
            'AREA NAME',
        ];

        $data = [];

        if(!empty($this->search)) {
            $branches = Branch::with('account')
            ->orderBy('account_id', 'ASC')
            ->where(function($query) {
                $query->whereHas('account', function($qry) {
                    $qry->where('account_code', 'like', '%'.$this->search.'%')
                    ->orWhere('short_name', 'like', '%'.$this->search.'%')
                    ->orWhere('account_name', 'like', '%'.$this->search.'%');
                })
                ->orWhere('branch_code', 'like', '%'.$this->search.'%')
                ->orWhere('branch_name', 'like ', '%'.$this->search.'%');
            })
            ->get();
        } else {
            $branches = Branch::with('account')
            ->orderBy('account_id', 'ASC')
            ->get();
        }

        foreach($branches as $branch) {
            $account = $branch->account;
            $classification = $branch->classification;
            $region = $branch->region;
            $area = $branch->area;

            $data[] = [
                $account->company->name,
                $account->account_code,
                $account->short_name,
                $account->account_name,
                $branch->branch_code,
                $branch->branch_name,
                $classification->classification_code,
                $classification->classification_name,
                $region->region_name,
                $area->area_code,
                $area->area_name
            ];
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            ['BRANCH LIST'],
            $header,
            $data
        ]);
    }
}
