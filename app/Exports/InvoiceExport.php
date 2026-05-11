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


class InvoiceExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithStrictNullComparison, WithChunkReading
{
    public $po_data;

    public function __construct($po_data) {
        $this->po_data = $po_data;
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
            'title'          => 'SMS PO Invoices',
            'description'    => 'SMS List of po invoice',
            'subject'        => 'SMS PO Invoice',
            'keywords'       => 'SMS po invoice list,export,spreadsheet',
            'category'       => 'PO Invoice',
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
            'PO NUMBER',
            'SYSTEM PO NUMBER',
            'ACCOUNT CODE',
            'INVOICE',
            'SALES ORDER',
            'ORDER DATE',
            'INVOICE DATE',
            'POD DATE',
            'VALUE',
        ];

        $data = [];
        foreach($this->po_data as $po_number => $invoice_data) {
            foreach($invoice_data as $invoice) {
                $data[] = [
                    $po_number,
                    $invoice['po_number'],
                    $invoice['account_code'],
                    $invoice['invoice'],
                    $invoice['sales_order'],
                    $invoice['order_date'],
                    $invoice['invoice_date'],
                    $invoice['pod_date'],
                    $invoice['currency_value']
                ];
            }
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'], 
            ['PO INVOICE'],
            $header,
            $data
        ]);
    }
}
