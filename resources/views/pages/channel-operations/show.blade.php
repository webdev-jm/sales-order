@extends('adminlte::page')

@section('title')
    COE REPORT
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>COE REPORT</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('channel-operation.list')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">COE REPORT</h3>
        <div class="card-tools">
            <a href="{{route('channel-operation.print', $channel_operation->id)}}" class="btn btn-info btn-sm" target="_blank"><i class="fa fa-print mr-1"></i>PRINT</a>
        </div>
    </div>
    <div class="card-body">

        <div class="row">

            <div class="col-lg-2">

            </div>

            <div class="col-lg-8">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">COE HEADER</h3>
                        <div class="card-tools">
                            <button class="btn btn-sm {{$channel_operation->status == 'finalized' ? 'btn-success' : 'btn-secondary'}}">{{$channel_operation->status}}</button>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- NAME --}}
                        <strong>
                            <i class="fas fa-address-card"></i>
                            NAME
                        </strong>
                        <p class="text-muted">
                            {{$channel_operation->branch_login->user->fullName() ?? '-'}}
                        </p>
                        <hr>
                        {{-- ACCOUNT --}}
                        <strong>
                            <i class="fas fa-building"></i>
                            ACCOUNT
                        </strong>
                        <p class="text-muted">
                            {{$channel_operation->branch_login->branch->account->short_name ?? '-'}}
                        </p>
                        {{-- BRANCH --}}
                        <hr>
                        <strong>
                            <i class="fas fa-code-branch"></i>
                            BRANCH
                        </strong>
                        <p class="text-muted">
                            [{{$channel_operation->branch_login->branch->branch_code ?? '-'}}] {{$channel_operation->branch_login->branch->branch_name ?? '-'}}
                        </p>
                        {{-- DATE --}}
                        <hr>
                        <strong>
                            <i class="fas fa-calendar-day"></i>
                            DATE
                        </strong>
                        <p class="text-muted">
                            {{$channel_operation->date ?? '-'}}
                        </p>
                        {{-- STORE IN CHARGE --}}
                        <hr>
                        <strong>
                            <i class="fas fa-user-shield"></i>
                            STORE IN CHARGE
                        </strong>
                        <p class="text-muted">
                            {{$channel_operation->store_in_charge ?? '-'}}
                        </p>
                        {{-- POSITION --}}
                        <hr>
                        <strong>
                            <i class="fas fa-user-tag"></i>
                            POSITION
                        </strong>
                        <p class="text-muted">
                            {{$channel_operation->position ?? '-'}}
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">MERCH UPDATE</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $merch_updates = $channel_operation->merch_updates->first();
                        @endphp
                        {{-- STATUS --}}
                        <strong>
                            <i class="fas fa-tag"></i>
                            MERCH STATUS
                        </strong>
                        <p class="text-muted">
                            {{$merch_updates['status'] ?? '-'}}
                        </p>
                        {{-- ACTUAL --}}
                        <hr>
                        <strong>
                            <i class="fas fa-check-circle"></i>
                            ACTUAL
                        </strong>
                        <p class="text-muted">
                            {{$merch_updates['actual'] ?? '-'}}
                        </p>
                        {{-- TARGET --}}
                        <hr>
                        <strong>
                            <i class="fas fa-bullseye"></i>
                            TARGET
                        </strong>
                        <p class="text-muted">
                            {{$merch_updates['target'] ?? '-'}}
                        </p>
                        {{-- DAYS OF GAPS --}}
                        <hr>
                        <strong>
                            <i class="fas fa-cloud-sun"></i>
                            DAYS OF GAPS
                        </strong>
                        <p class="text-muted">
                            {{$merch_updates['days_of_gaps'] ?? '-'}}
                        </p>
                        {{-- SALES OPPORTUNITIES --}}
                        <hr>
                        <strong>
                            <i class="fas fa-money-check-alt"></i>
                            SALES OPPORTUNITIES
                        </strong>
                        <p class="text-muted">
                            {{$merch_updates['sales_opportunities'] ?? '-'}}
                        </p>
                        {{-- REMARKS --}}
                        <hr>
                        <strong>
                            <i class="fas fa-comment-dots"></i>
                            REMARKS
                        </strong>
                        <p class="text-muted">
                            {{$merch_updates['remarks'] ?? '-'}}
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">TRADE DISPLAY</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $trade_displays = $channel_operation->trade_displays->first();
                        @endphp
                        {{-- PLANOGRAM --}}
                        <strong>
                            <i class="fas fa-ruler-horizontal"></i>
                            PLANOGRAM
                        </strong>
                        <p class="text-muted">
                            {{$trade_displays['planogram'] ?? '-'}}
                        </p>
                        {{-- BEVI PRICING --}}
                        <hr>
                        <strong>
                            <i class="fas fa-tags"></i>
                            BEVI PRICING
                        </strong>
                        <p class="text-muted">
                            {{$trade_displays['bevi_pricing'] ?? '-'}}
                        </p>
                        {{-- ON SHELVES AVAILABILITY - BATH --}}
                        <hr>
                        <strong>
                            <i class="fas fa-shower"></i>
                            ON SHELVES AVAILABILITY - BATH
                        </strong>
                        <p class="text-muted">
                            <b class="">ACTUAL: </b> {{$trade_displays['osa_bath_actual'] ?? '-'}}
                            <br>
                            <b class="">TARGET: </b> {{$trade_displays['osa_bath_target'] ?? '-'}}
                            <br>
                            @php
                                $percent = 0;
                                if(!empty($trade_displays['osa_bath_actual']) && !empty($trade_displays['osa_bath_target'])) {
                                    $percent = ($trade_displays['osa_bath_actual'] / $trade_displays['osa_bath_target']) * 100;
                                }
                            @endphp
                            <b>PERCENT: {{number_format($percent, 1)}} %</b>
                        </p>
                        {{-- ON SHELVES AVAILABILITY - FACE --}}
                        <hr>
                        <strong>
                            <i class="fas fa-grin-tears"></i>
                            ON SHELVES AVAILABILITY - FACE
                        </strong>
                        <p class="text-muted">
                            <b class="">ACTUAL: </b> {{$trade_displays['osa_face_actual'] ?? '-'}}
                            <br>
                            <b class="">TARGET: </b> {{$trade_displays['osa_face_target'] ?? '-'}}
                            <br>
                            @php
                                $percent = 0;
                                if(!empty($trade_displays['osa_face_actual']) && !empty($trade_displays['osa_face_target'])) {
                                    $percent = ($trade_displays['osa_face_actual'] / $trade_displays['osa_face_target']) * 100;
                                }
                            @endphp
                            <b>PERCENT: {{number_format($percent, 1)}} %</b>
                        </p>
                        {{-- ON SHELVES AVAILABILITY - BODY --}}
                        <hr>
                        <strong>
                            <i class="fas fa-child"></i>
                            ON SHELVES AVAILABILITY - BODY
                        </strong>
                        <p class="text-muted">
                            <b class="">ACTUAL: </b> {{$trade_displays['osa_body_actual'] ?? '-'}}
                            <br>
                            <b class="">TARGET: </b> {{$trade_displays['osa_body_target'] ?? '-'}}
                            <br>
                            @php
                                $percent = 0;
                                if(!empty($trade_displays['osa_body_actual']) && !empty($trade_displays['osa_body_target'])) {
                                    $percent = ($trade_displays['osa_body_actual'] / $trade_displays['osa_body_target']) * 100;
                                }
                            @endphp 
                            <b>PERCENT: {{number_format($percent, 1)}} %</b>
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">TRADE MARKETING ACTIVITIES</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $trade_marketing_activities = $channel_operation->trade_marketing_activities->first();
                            $paf = \App\Models\Paf::where('PAFNo', $trade_marketing_activities->paf_number ?? '')
                                ->first();
                        @endphp
                        {{-- PAF NUMBER --}}
                        <strong>
                            <i class="fas fa-code"></i>
                            PAF NUMBER
                        </strong>
                        <p class="text-muted">
                            {{$trade_marketing_activities['paf_number'] ?? 'NONE'}}
                        </p>
                        {{-- PROGRAM TITLE --}}
                        <hr>
                        <strong>
                            <i class="fas fa-tasks"></i>
                            PROGRAM TITLE
                        </strong>
                        <p class="text-muted">
                            {{$paf->title ?? ' - '}}
                        </p>
                        {{-- DURATION --}}
                        <hr>
                        <strong>
                            <i class="fas fa-history"></i>
                            DURATION
                        </strong>
                        <p class="text-muted">
                            {{$paf['start_date'] ?? ' - '}} to {{$paf['end_date'] ?? ' - '}}
                        </p>
                        {{-- TYPE OF ACTIVITY --}}
                        <hr>
                        <strong>
                            <i class="fas fa-atom"></i>
                            TYPE OF ACTIVITY
                        </strong>
                        <p class="text-muted">
                            {{$paf['support_type'] ?? ' - '}}
                        </p>

                        {{-- SKUs --}}
                        <hr>
                        <strong>
                            <i class="fas fa-box-open"></i>
                            PARTICIPATING SKUS
                        </strong>
                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="align-middle">SKU CODE</th>
                                            <th class="align-middle">SKU DESCRIPTION</th>
                                            <th class="align-middle">BRAND</th>
                                            <th class="align-middle">ACTUAL</th>
                                            <th class="align-middle">TARGET MAXCAP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($trade_marketing_activities->skus) && !empty($trade_marketing_activities->skus->count()))
                                            @foreach($trade_marketing_activities->skus as $sku)
                                            <tr class="text-center">
                                                <td>{{$sku['sku_code'] ?? ' - '}}</td>
                                                <td>{{$sku['sku_description'] ?? ' - '}}</td>
                                                <td>{{$sku['brand'] ?? ' - '}}</td>
                                                <td>{{$sku['actual'] ?? ' - '}}</td>
                                                <td>{{$sku['target_maxcap'] ?? ' - '}}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center" colspan="5">No available data.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- REMARKS --}}
                        <hr>
                        <strong>
                            <i class="fas fa-comment-dots"></i>
                            REMARKS
                        </strong>
                        <p class="text-muted">
                            {{$trade_marketing_activities['remarks'] ?? ' - '}}
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">DISPLAY RENTALS</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $display_rentals = $channel_operation->display_rentals->first();
                        @endphp
                        {{-- DRA STATUS --}}
                        <strong>
                            <i class="fas fa-tag"></i>
                            DRA STATUS
                        </strong>
                        <p class="text-muted">
                            {{$display_rentals['status'] ?? '-'}}
                        </p>
                        {{-- DRA LOCATION --}}
                        <hr>
                        <strong>
                            <i class="fas fa-thumbtack"></i>
                            DRA LOCATION
                        </strong>
                        <p class="text-muted">
                            {{$display_rentals['location'] ?? '-'}}
                        </p>
                        {{-- STOCKS DISPLAYED --}}
                        <hr>
                        <strong>
                            <i class="fas fa-cubes"></i>
                            % STOCKS DISPLAYED
                        </strong>
                        <p class="text-muted">
                            {{$display_rentals['stocks_displayed'] ?? '-'}}
                        </p>
                        {{-- REMARKS --}}
                        <hr>
                        <strong>
                            <i class="fas fa-comment-dots"></i>
                            REMARKS
                        </strong>
                        <p class="text-muted">
                            {{$display_rentals['remarks'] ?? '-'}}
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">EXTRA DISPLAY</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $extra_displays = $channel_operation->extra_displays->first();
                        @endphp
                        {{-- EXTRA DISPLAY --}}
                        <strong>
                            <i class="fas fa-map-marker-alt"></i>
                            DISPLAY LOCATION
                        </strong>
                        <p class="text-muted">
                            {{$extra_displays['location'] ?? '-'}}
                        </p>
                        {{-- RATE PER MONTH --}}
                        <hr>
                        <strong>
                            <i class="fas fa-percent"></i>
                            RATE PER MONTH IF RENTED
                        </strong>
                        <p class="text-muted">
                            {{$extra_displays['rate_per_month'] ?? '-'}}
                        </p>
                        {{-- AMOUNT --}}
                        <hr>
                        <strong>
                            <i class="fas fa-sort-amount-up"></i>
                            AMOUNT OF BEVI PRODUCTS DISPLAYED
                        </strong>
                        <p class="text-muted">
                            {{$extra_displays['amount'] ?? '-'}}
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">COMPETETIVE REPORTS</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $competetive_reports = $channel_operation->competetive_reports;
                        @endphp
                        {{-- PRODUCTS --}}
                        <strong>
                            <i class="fas fa-pallet"></i>
                            PRODUCTS
                        </strong>
                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="align-middle">COMPANY NAME</th>
                                            <th class="align-middle">PRODUCT DESCRIPTION</th>
                                            <th class="align-middle">SRP</th>
                                            <th class="align-middle">TYPE OF PROMOTION</th>
                                            <th class="align-middle">IMPACT TO OUR PRODUCTS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($competetive_reports as $report)
                                        <tr class="text-center">
                                            <td>{{$report['company_name'] ?? '-'}}</td>
                                            <td>{{$report['product_description'] ?? '-'}}</td>
                                            <td>{{$report['srp'] ?? '-'}}</td>
                                            <td>{{$report['type_of_promotion'] ?? '-'}}</td>
                                            <td>{{$report['impact_to_our_product'] ?? '-'}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        {{-- TOTAL FINDINGS --}}
                        <hr>
                        <strong>
                            <i class="fas fa-chart-line"></i>
                            TOTAL FINDINGS
                        </strong>
                        <p class="text-muted">
                            {{$channel_operation->total_findings ?? '-'}}
                        </p>
                    </div>
                </div>

            </div>

            <div class="col-lg-2">

            </div>

        </div>

    </div>
</div>
@endsection

@section('js')
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection