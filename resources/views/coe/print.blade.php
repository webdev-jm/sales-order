<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Channel Operation Executive</title>

    <style>
        .text-center {
            text-align: center !important;
        }
        .align-middle {
            vertical-align: middle;
        }
        .align-bottom {
            vertical-align: bottom;
        }
        .align-top {
            vertical-align: top;
        }
        

        /* borders */
        .border-0 {
            border: 0 !important;
        }
        /* table */
        .table {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: collapse;
        }
        .table thead {
            display: table-header-group;
            vertical-align: top;
        }
        .table tbody {
            display: table-row-group;
            vertical-align: middle;
        }
        .table.no-border tbody td,.table.no-border tbody th {
            border: 0 !important;
        }
        .table tr {
            display: table-row;
        }
        .table th, td {
            border: .5px solid;
            padding: 4px;
            font-size: 14px;
            text-align: left;
        }
        .table-sm td, th {
            padding: 0.3rem;
        }

        .table-title {
            border-bottom: 3px solid red !important;
            border-top: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            font-size: 20px !important;
            background-color: beige;
        }

        .logo {
            height: 50px;
            vertical-align: middle;
            margin-top: 5px;
        }
        .bevi-logo {
            height: 40px;
            margin-right: 20px;
            vertical-align: middle;
            
        }

        .wleft {
            width: 160px;
        }
        .main-title {
            font-size: 30px !important;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <table class="table">
        <thead>
            <tr>
                <th class="wleft">
                    <img src="{{public_path('/assets/images/logo.jpg')}}" alt="logo" class="bevi-logo align-middle">
                    <img src="{{public_path('/assets/images/bevi-logo.png')}}" alt="logo" class="logo align-middle">
                </th>
                <th class="text-center align-middle main-title">COE REPORT</th>
            </tr>
        </thead>
    </table>
    

    <table class="table no-border">
        <thead>
            <tr>
                <th class="table-title" colspan="2">COE REPORT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>NAME:</th>
                <td class="text-uppercase">{{$branch_login->user->fullName() ?? '-'}}</td>
            </tr>
            <tr>
                <th>ACCOUNT:</th>
                <td>{{$branch_login->branch->account->account_name ?? '-'}}</td>
            </tr>
            <tr>
                <th>BRANCH:</th>
                <td>{{$branch_login->branch->branch_code ?? '-'}} {{$branch_login->branch->branch_name ?? '-'}}</td>
            </tr>
            <tr>
                <th>DATE:</th>
                <td>{{$channel_operation->date}}</td>
            </tr>
            <tr>
                <th>STORE IN CHARGE:</th>
                <td>{{$channel_operation->store_in_charge}}</td>
            </tr>
            <tr>
                <th>POSITION:</th>
                <td>{{$channel_operation->position}}</td>
            </tr>

            <tr>
                <td colspan="2"></td>
            </tr>
        </tbody>

        <thead>
            <tr>
                <th class="table-title" colspan="2">MERCH UPDATE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>MERTCH STATUS:</th>
                <td>{{$merch_updates->status ?? '-'}}</td>
            </tr>
            <tr>
                <th>ACTUAL:</th>
                <td>{{$merch_updates->actual ?? '-'}}</td>
            </tr>
            <tr>
                <th>TARGET:</th>
                <td>{{$merch_updates->target ?? '-'}}</td>
            </tr>
            <tr>
                <th>DAYS OF GAPS:</th>
                <td>{{$merch_updates->days_of_gaps ?? '-'}}</td>
            </tr>
            <tr>
                <th>SALES OPPORTUNITIES:</th>
                <td>{{$merch_updates->sales_opportunities ?? '-'}}</td>
            </tr>
            <tr>
                <th>REMARKS:</th>
                <td>{{$merch_updates->remarks ?? '-'}}</td>
            </tr>
            <tr>
                <td colspan="2"></td>
            </tr>
        </tbody>

        <thead>
            <tr>
                <th class="table-title" colspan="2">TRADE DISPLAY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>PLANOGRAM</th>
                <td>{{$trade_displays->planogram ?? '-'}}</td>
            </tr>
            <tr>
                <th>BEVI PRICING</th>
                <td>{{$trade_displays->bevi_pricing ?? '-'}}</td>
            </tr>
            <tr>
                <th rowspan="3" class="align-top">ON SHELVES AVAILABILITY - BATH</th>
                <td><b>ACTUAL:</b> {{$trade_displays->osa_bath_actual ?? '-'}}</td>
            </tr>
            <tr>
                <td><b>TARGET:</b> {{$trade_displays->osa_bath_target ?? '-'}}</td>
            </tr>
            <tr>
                @php 
                $percent = 0;
                if(!empty($trade_displays->osa_bath_actual) && !empty($trade_displays->osa_bath_target)) {
                    $percent = ($trade_displays->osa_bath_actual / $trade_displays->osa_bath_target) * 100;
                }
                @endphp
                <td><b>PERCENT:</b> {{number_format($percent, 2)}}%</td>
            </tr>
            <tr>
                <th rowspan="3" class="align-top">ON SHELVES AVAILABILITY - FACE</th>
                <td><b>ACTUAL:</b> {{$trade_displays->osa_face_actual ?? '-'}}</td>
            </tr>
            <tr>
                <td><b>TARGET:</b> {{$trade_displays->osa_face_target ?? '-'}}</td>
            </tr>
            <tr>
                @php 
                $percent = 0;
                if(!empty($trade_displays->osa_face_actual) && !empty($trade_displays->osa_face_target)) {
                    $percent = ($trade_displays->osa_face_actual / $trade_displays->osa_face_target) * 100;
                }
                @endphp
                <td><b>PERCENT:</b> {{number_format($percent, 2)}}%</td>
            </tr>
            <tr>
                <th rowspan="3" class="align-top">ON SHELVES AVAILABILITY - BODY</th>
                <td><b>ACTUAL:</b> {{$trade_displays->osa_body_actual ?? '-'}}</td>
            </tr>
            <tr>
                <td><b>TARGET:</b> {{$trade_displays->osa_body_target ?? '-'}}</td>
            </tr>
            <tr>
                @php 
                $percent = 0;
                if(!empty($trade_displays->osa_body_actual) && !empty($trade_displays->osa_body_target)) {
                    $percent = ($trade_displays->osa_body_actual / $trade_displays->osa_body_target) * 100;
                }
                @endphp
                <td><b>PERCENT:</b> {{number_format($percent, 2)}}%</td>
            </tr>
        </tbody>
    </table>

    <table class="table no-border">
        <thead>
            <tr>
                <th class="table-title" colspan="2">TRADE MARKETING ACTIVITIES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>PAF NUMBER</th>
                <td>{{$trade_marketing_activities->paf_number ?? '-'}}</td>
            </tr>
            <tr>
                <th>PROGRAM TITLE</th>
                <td>{{$paf->title ?? '-'}}</td>
            </tr>
            <tr>
                <th>DURATION</th>
                <td>{{$paf->start_date ?? '-'}} to {{$paf->end_date ?? '-'}}</td>
            </tr>
            <tr>
                <th>TYPE OF ACTIVITY</th>
                <td>{{$paf->support_type ?? '-'}}</td>
            </tr>
            <tr>
                <th colspan="2">REMARKS</th>
            </tr>
            <tr>
                <td colspan="2">
                    {{$trade_marketing_activities->remarks ?? '-'}}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>SKU CODE</th>
                <th>SKU DESCRIPTION</th>
                <th>BRAND</th>
                <th>ACTUAL</th>
                <th>TARGET MAXCAP</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($trade_marketing_activities->skus))
                @foreach($trade_marketing_activities->skus as $sku)
                <tr>
                    <td>{{$sku->sku_code ?? '-'}}</td>
                    <td>{{$sku->sku_description ?? '-'}}</td>
                    <td>{{$sku->brand ?? '-'}}</td>
                    <td>{{$sku->actual ?? '-'}}</td>
                    <td>{{$sku->target_maxcap ?? '-'}}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <table class="table no-border">
        <thead>
            <tr>
                <th class="table-title" colspan="2">EXTRA DISPLAY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>DISPLAY LOCATION</th>
                <td>{{$extra_displays->location ?? '-'}}</td>
            </tr>
            <tr>
                <th>RATE PER MONTH IF RENTED</th>
                <td>{{$extra_displays->rate_per_month ?? '-'}}</td>
            </tr>
            <tr>
                <th>AMOUNT OF BEVI PRODUCTS DISPLAYED</th>
                <td>{{$extra_displays->amount ?? '-'}}</td>
            </tr>
        </tbody>
    </table>

    <table class="table competetive-table">
        <thead>
            <tr>
                <th class="table-title" colspan="5">COMPETETIVE REPORTS</th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th>COMPANY NAME</th>
                <th>PRODUCT DESCRIPTION</th>
                <th>SRP</th>
                <th>TYPE OF PROMOTION</th>
                <th>IMPACT TO OUR PRODUCTS</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($competetive_reports->count()))
                @foreach($competetive_reports as $report)
                <tr>
                    <td>{{$report->company_name ?? ''}}</td>
                    <td>{{$report->product_description ?? ''}}</td>
                    <td>{{$report->srp ?? ''}}</td>
                    <td>{{$report->type_of_promotion ?? ''}}</td>
                    <td>{{$report->impact_to_our_product}}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    
    <table class="table no-border">
        <thead>
            <tr>
                <th class="table-title">TOTAL FINDINGS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{$channel_operation->total_findings}}
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>