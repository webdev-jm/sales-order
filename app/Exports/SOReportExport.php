<?php

namespace App\Exports;

use App\Models\SalesOrder;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class SOReportExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize
{

    protected $year, $month, $group_code;

    public function __construct($year, $month, $group_code) {
        $this->year = $year;
        $this->month = $month;
        $this->group_code = $group_code;
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
            // date
            2 => [
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
            'GROUP CODE',
            'USERNAME',
            'NAME',
            'COMPANY',
            'ACCOUNT CODE',
            'SHORT NAME',
            'ACCOUNT NAME',
            'CONTROL NUMBER',
            'PO NUMBER',
            'ORDER DATE',
            'SHIP DATE',
            'SHIPPING INSTRUCTION',
            'ADDRESS',
            'STATUS',
            'REFERENCE',
            'PART',
            'STOCK CODE',
            'DESCRIPTION',
            'SIZE',
            'UOM',
            'QUANTITY',
            'TOTAL',
        ];

        // query date
        $date_string = $this->year.'-'.$this->month;

        $date = date('F Y', strtotime($date_string.'-01'));

        $data = [];
        // get sales orders
        if(!empty($this->group_code)) {
            $date .= ' - '.$this->group_code;

            $sales_orders = SalesOrder::orderBy('control_number', 'ASC')
            ->where('order_date', 'like', $date_string.'%')
            ->whereHas('account_login', function($query) {
                $query->whereHas('user', function($qry) {
                    $qry->where('group_code', $this->group_code);
                });
            })
            ->get();
        } else {
            $sales_orders = SalesOrder::orderBy('control_number', 'ASC')
            ->where('order_date', 'like', $date_string.'%')
            ->get();
        }

        $total_quantity = 0;
        $total_amount = 0;
        foreach($sales_orders as $sales_order) {
            $account_login = $sales_order->account_login;
            $account = $account_login->account;
            
            $address_arr = [
                $sales_order->ship_to_name,
                $sales_order->ship_to_building,
                $sales_order->ship_to_street,
                $sales_order->ship_to_city,
                $sales_order->ship_to_postal
            ];

            $address = implode(', ', array_filter($address_arr));

            // products
            $order_products = $sales_order->order_products;
            foreach($order_products as $order_product) {
                $product = $order_product->product;

                // uoms
                $product_uoms = $order_product->product_uoms;
                foreach($product_uoms as $product_uom) {
                    $data[] = [
                        $account_login->user->group_code,
                        $account_login->user->email,
                        $account_login->user->fullName(),
                        $account->company->name,
                        $account->account_code,
                        $account->short_name,
                        $account->account_name,
                        $sales_order->control_number,
                        $sales_order->po_number,
                        $sales_order->order_date,
                        $sales_order->ship_date,
                        $sales_order->shipping_instruction,
                        $address,
                        $sales_order->status,
                        $sales_order->reference,
                        $order_product->part,
                        $product->stock_code,
                        $product->description,
                        $product->size,
                        $product_uom->uom,
                        $product_uom->quantity,
                        $product_uom->uom_total
                    ];

                    $total_quantity += $product_uom->quantity;
                    $total_amount += $product_uom->uom_total;
                }

            }

        }

        // footer
        $footer = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL',
            $total_quantity,
            $total_amount,
        ];

        return new Collection([
            ['SMS - SALES MANAGEMENT SYSTEM'],
            [$date],
            $header,
            $data,
            $footer
        ]);
    }
}
