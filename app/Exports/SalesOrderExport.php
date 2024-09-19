<?php

namespace App\Exports;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\SalesOrder;

class SalesOrderExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize
{
    protected $account, $date_from, $date_to, $search;

    public function __construct($account, $date_from, $date_to, $search) {
        $this->account = $account;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
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
            'title'          => 'SO Reports',
            'description'    => 'List of Sales Orders',
            'subject'        => 'SO Reports',
            'keywords'       => 'mcp reports,export,spreadsheet',
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
            // header
            2 => [
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

    public function chunkSize(): int
    {
        return 500;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // header
        $header = [
            'ACCOUNT CODE',
            'SHORT NAME',
            'ACCOUNT NAME',
            'CONTROL NUMBER',
            'PO NUMBER',
            'ORDER DATE',
            'SHIP DATE',
            'SHIPPING INSTRUCTION',
            'STATUS',
            'REFERENCE',
        ];

        $data = [];
        $sales_orders = SalesOrder::orderBy('id', 'DESC')
            ->whereHas('account_login', function($qry) {
                $qry->where('account_id', $this->account->id);
            })
            ->when(!empty($this->search), function($query) {
                $query->where(function($qry) {
                    $qry->where('control_number', 'like', '%'.$this->search.'%')
                        ->orWhere('po_number', 'like', '%'.$this->search.'%')
                        ->orWhere('order_date', 'like', '%'.$this->search.'%')
                        ->orWhere('ship_date', 'like', '%'.$this->search.'%')
                        ->orWhere('ship_to_name', 'like', '%'.$this->search.'%')
                        ->orWhere('status', 'like', '%'.$this->search.'%');
                });
            })
            ->when(!empty($this->date_from), function($qry) {
                $qry->where('order_date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($qry) {
                $qry->where('order_date', '<=', $this->date_to);
            })
            ->get();
        
        foreach($sales_orders as $sales_order) {
            $data[] = [
                $this->account->account_code,
                $this->account->short_name,
                $this->account->account_name,
                $sales_order->control_number,
                $sales_order->po_number,
                $sales_order->order_date,
                $sales_order->ship_date,
                $sales_order->ship_to_name,
                $sales_order->status,
                $sales_order->reference,
            ];
        }

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            $header,
            $data,
        ]);
    }
}
