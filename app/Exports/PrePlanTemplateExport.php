<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class PrePlanTemplateExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties
{
    public function properties(): array
    {
        return [
            'creator'        => 'Sales Management System',
            'lastModifiedBy' => 'SMS',
            'title'          => 'Pre Plan Upload Template',
            'description'    => 'Template for uploading pre plan data',
            'subject'        => 'Pre Plan Upload Template',
            'keywords'       => 'pre plan,upload,template',
            'category'       => 'Pre Plan',
            'manager'        => 'SMS Application',
            'company'        => 'BEVI',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'DDFFFD'],
                ],
            ],
        ];
    }

    public function collection(): Collection
    {
        return new Collection([
            [
                'PRE PLAN NUMBER',
                'YEAR',
                'COMPANY',
                'ACCOUNT CODE',
                'ACCOUNT NAME',
                'START DATE',
                'END DATE',
                'TITLE',
                'SUPPORT TYPE',
                'CONCEPT',
                'DETAIL TYPE',
                'COMPONENTS',
                'STOCK CODE',
                'DESCRIPTION',
                'PRICE CODE',
                'BRAND',
                'QUANTITY',
                'BRANCH',
                'GL CODE',
                'IO',
                'AMOUNT',
            ],
        ]);
    }
}
