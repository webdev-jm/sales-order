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

class SODashboardExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize
{
    protected $year, $month, $days;

    public function __construct($year, $month, $days) {
        $this->year = $year;
        $this->month = $month;
        $this->days = $days;
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
            'title'          => 'SO Dashboard',
            'description'    => 'List of Sales Orders',
            'subject'        => 'SO Dashboard',
            'keywords'       => 'so dashboard,export,spreadsheet',
            'category'       => 'Dashboard',
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

        $date = '';

        // check if days was selected
        if(!empty($this->year) && !empty($this->month) && empty($this->days)) { // no days selected
            $date_string = $this->year.'-'.$this->month;

            $date = date('F Y', strtotime($date_string.'-01'));

            $sales_orders = SalesOrder::where('order_date', 'like', $date_string.'%')
            ->where('status', 'finalized')
            ->where('upload_status', 1)
            ->get();

        } else if(!empty($this->days)) { // days selected
            $dates = [];
            foreach($this->days as $year => $months) {
                foreach($months as $month => $days) {
                    foreach($days as $day) {
                        $dates[] = $year.'-'.$month.'-'.$day;
                    }
                }
            }

            if(!empty($dates)) {

                $date = implode(', ', $dates);

                $sales_orders = SalesOrder::whereIn('order_date', $dates)
                ->where('status', 'finalized')
                ->where('upload_status', 1)
                ->get();
            } else {
                if(!empty($this->year) && !empty($this->month)) {
                    $date_string = $this->year.'-'.$this->month;
                } else {
                    $date_string = date('Y-m');
                }

                $date = date('F Y', strtotime($date_string.'-01'));

                $sales_orders = SalesOrder::where('order_date', 'like', $date_string.'-%')
                ->where('status', 'finalized')
                ->where('upload_status', 1)
                ->get();
            }

        } else { // default current date
            $date_string = date('Y-m-d');

            $date = $date_string;

            $sales_orders = SalesOrder::where('order_date', $date_string)
            ->where('status', 'finalized')
            ->where('upload_status', 1)
            ->get();
        }

        $data = [];
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
            $footer,
        ]);
    }
}
