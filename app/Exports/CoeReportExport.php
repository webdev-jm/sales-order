<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoeReportExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize
{
    public $user_data, $account_data, $date_from, $date_to;

    public function __construct($user_data, $account_data, $date_from, $date_to) {
        $this->user_data = $user_data;
        $this->account_data = $account_data;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
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
            'title'          => 'COE Reports',
            'description'    => 'List of COE Submissions',
            'subject'        => 'SO Reports',
            'keywords'       => 'coe reports,export,spreadsheet',
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
            'ACCOUNT NAME',
            'BRANCH CODE',
            'BRANCH NAME',
            'DATE',
            'STORE IN CHARGE',
            'POSITION',
            'STATUS',
            'ACTUAL',
            'TARGET',
            'DAYS OF GAPS',
            'SALES OPPORTUNITIES',
            'REMARKS',
            'PLANOGRAM',
            'BEVI PRICING',
            'OSA BATH ACTUAL',
            'OSA BATH TARGET',
            'OSA FACE ACTUAL',
            'OSA FACE TARGET',
            'OSA BODY ACTUAL',
            'OSA BODY ACTUAL',
            'REMARKS',
            'PAF NUMBER',
            'REMARKS',
            'SKU CODE',
            'SKU DESCRIPTION',
            'BRAND',
            'ACTUAL',
            'TARGET MAXCAP',
            'STATUS',
            'LOCATION',
            'STOCKS DISPLAYED',
            'REMARKS',
            'LOCATION',
            'RATE PER MONTH',
            'AMOUNT',
            'COMPANY NAME',
            'PRODUCT DESCRIPTION',
            'SRP',
            'TYPE OF PROMOTION',
            'IMPACT TO OUR PRODUCT',
            'TOTAL FINDINGS'
        ];

        $results = DB::table('channel_operations as co')
            ->select(
                'a.account_code',
                'a.short_name',
                'b.branch_code',
                'b.branch_name',
                'co.date',
                'co.store_in_charge',
                'co.position',
                'comu.status as comu_status',
                'comu.actual as comu_actual',
                'comu.target as comu_target',
                'comu.days_of_gaps',
                'comu.sales_opportunities',
                'comu.remarks as comu_remarks',
                'cotd.planogram',
                'cotd.bevi_pricing',
                'cotd.osa_bath_actual',
                'cotd.osa_bath_target',
                'cotd.osa_face_actual',
                'cotd.osa_face_target',
                'cotd.osa_body_actual',
                'cotd.osa_body_target',
                'cotd.remarks as cotd_remarks',
                'cotma.paf_number',
                'cotma.remarks as cotma_remarks',
                'cotmas.sku_code',
                'cotmas.sku_description',
                'cotmas.brand',
                'cotmas.actual as cotmas_actual',
                'cotmas.target_maxcap',
                'codr.status as codr_status',
                'codr.location as codr_location',
                'codr.stocks_displayed',
                'codr.remarks as codr_remarks',
                'coed.location as coed_location',
                'coed.rate_per_month',
                'coed.amount',
                'cocr.company_name',
                'cocr.product_description',
                'cocr.srp',
                'cocr.type_of_promotion',
                'cocr.impact_to_our_product',
                'co.total_findings'
            )
            ->leftJoin('branch_logins as bl', 'bl.id', '=', 'co.branch_login_id')
            ->leftJoin('branches as b', 'bl.branch_id', '=', 'b.id')
            ->leftJoin('accounts as a', 'b.account_id', '=', 'a.id')
            ->leftJoin('channel_operation_merch_updates as comu', 'co.id', '=', 'comu.channel_operation_id')
            ->leftJoin('channel_operation_trade_displays as cotd', 'co.id', '=', 'cotd.channel_operation_id')
            ->leftJoin('channel_operation_trade_marketing_activities as cotma', 'co.id', '=', 'cotma.channel_operation_id')
            ->leftJoin('channel_operation_trade_marketing_activity_skus as cotmas', 'cotma.id', '=', 'cotmas.channel_operation_trade_marketing_activity_id')
            ->leftJoin('channel_operation_display_rentals as codr', 'co.id', '=', 'codr.channel_operation_id')
            ->leftJoin('channel_operation_extra_displays as coed', 'co.id', '=', 'coed.channel_operation_id')
            ->leftJoin('channel_operation_competetive_reports as cocr', 'co.id', '=', 'cocr.channel_operation_id')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('bl.user_id', $this->user_data);
            })
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('b.account_id', $this->account_data);
            })
            ->when(!empty($this->date_from), function($query) {
                $query->where('co.date', '>=', $this->date_from);
            })
            ->when(!empty($this->date_to), function($query) {
                $query->where('co.date', '<=', $this->date_to);
            })
            ->get();

        $results = $results->toArray();

        $data = array();
        foreach($results as $result) {
            $data[] = [
                $result->account_code,
                $result->short_name,
                $result->branch_code,
                $result->branch_name,
                $result->date,
                $result->store_in_charge,
                $result->position,
                $result->comu_status,
                $result->comu_actual,
                $result->comu_target,
                $result->days_of_gaps,
                $result->sales_opportunities,
                $result->comu_remarks,
                $result->planogram,
                $result->bevi_pricing,
                $result->osa_bath_actual,
                $result->osa_bath_target,
                $result->osa_face_actual,
                $result->osa_face_target,
                $result->osa_body_actual,
                $result->osa_body_target,
                $result->cotd_remarks,
                $result->paf_number,
                $result->cotma_remarks,
                $result->sku_code,
                $result->sku_description,
                $result->brand,
                $result->cotmas_actual,
                $result->target_maxcap,
                $result->codr_status,
                $result->codr_location,
                $result->stocks_displayed,
                $result->codr_remarks,
                $result->coed_location,
                $result->rate_per_month,
                $result->amount,
                $result->company_name,
                $result->product_description,
                $result->srp,
                $result->type_of_promotion,
                $result->impact_to_our_product,
                $result->total_findings
            ];
        }

        return new Collection([
            ['SMS - COE REPORTS'],
            $header,
            $data
        ]);
    }
}
