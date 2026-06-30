<?php

namespace App\Exports;

use App\Models\PPUForm;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PPUFormExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor
{
    protected $ppu_form, $ppuform_item;

    public function __construct(PPUForm $ppu_form, $ppuform_item) {
        $this->ppu_form = $ppu_form;
        $this->ppuform_item = $ppuform_item;
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
            'title'          => 'PPU Form',
            'description'    => 'Proposal for Pick-Up (PPU) Form',
            'subject'        => 'PPU Form',
            'keywords'       => 'ppu,export,spreadsheet',
            'category'       => 'Reports',
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
            // Info rows
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            // header
            5 => [
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
        $ppu_form = $this->ppu_form;
        $account_login = $ppu_form->account_login;
        $account = $account_login->account ?? null;

        $header = [
            'NO.',
            'RTV/RS NO.',
            'RTV DATE',
            'BRANCH NAME',
            'TOTAL QTY',
            'TOTAL AMOUNT',
            'REMARKS',
        ];

        $data = [];
        foreach ($this->ppuform_item as $index => $item) {
            $data[] = [
                $index + 1,
                $item->rtv_number,
                $item->rtv_date,
                $item->branch_name,
                $item->total_quantity,
                $item->total_amount,
                $item->remarks,
            ];
        }

        $footer = [
            '',
            '',
            '',
            'TOTAL',
            $ppu_form->total_quantity,
            $ppu_form->total_amount,
            '',
        ];

        return new Collection([
            ['PROPOSAL FOR PICK-UP (PPU) FORM'],
            [
                'Customer Name:',
                !empty($account) ? '[' . $account->account_code . '] ' . $account->short_name : '',
                'PPU No:',
                $ppu_form->control_number,
            ],
            [
                'Prepared By:',
                isset($account_login->user) ? $account_login->user->fullName() : '',
                'Date Submitted:',
                $ppu_form->date_submitted,
            ],
            [
                'Date Prepared:',
                $ppu_form->date_prepared,
                'Propose Pick-Up Date:',
                $ppu_form->pickup_date,
            ],
            $header,
            $data,
            $footer,
        ]);
    }
}
