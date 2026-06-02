<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesOrderTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private array $rows;

    private array $columns = [
        'po_date', 'po_number', 'store_name', 'store_code', 'delivery_date',
        'cancellation_date', 'depot', 'del_loc', 'po_remarks', 'raw_sku',
        'sku_code', 'description', 'qty', 'list_price', 'amount',
        'internal_sku', 'product_name',
        'shipping_name', 'shipping_address', 'lookup_status',
    ];

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return [
            'PO Date', 'PO Number', 'Store Name', 'Store Code', 'Delivery Date',
            'Cancellation Date', 'Depot', 'Del Loc', 'PO Remarks', 'Raw SKU',
            'SKU Code', 'Description', 'Qty', 'List Price', 'Amount',
            'Internal SKU', 'Product Name',
            'Shipping Name', 'Shipping Address', 'Lookup Status',
        ];
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return array_map(fn($col) => $row[$col] ?? '', $this->columns);
        }, $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color'    => ['argb' => 'FF4472C4'],
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            ],
        ];
    }
}
