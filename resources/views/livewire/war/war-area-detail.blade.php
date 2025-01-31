<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Activities</h4>
            <span wire:loading class="float-right"><i class="fa fa-spinner fa-spin"></i></span>
        </div>
        <div class="modal-body">
            
            <h3 class="badge badge-success text-lg">{{$date}}</h3>
            
            @if(!empty($schedules->count()))
                <h4>PLANNED ACTIVITIES</h4>
                <ul class="list-group">
                    @foreach($schedules as $schedule)
                        <li class="list-group-item">
                            {{$schedule->objective}}
                        </li>
                    @endforeach
                </ul>

                <div class="row my-2">
                    <div class="col-12">
                        {{$schedules->links()}}
                    </div>
                </div>
            @endif

            <!-- ACTUAL ACTIVITIES -->
            @if(!empty($branch_logins))
                <h4>ACTUAL ACTIVITIES</h4>
                @if($branch_logins->count() > 0)
                    <div class="list-group">
                        @foreach($branch_logins as $login)
                            <div class="list-group-item">
                                <h2>[{{$login->branch->account->account_code}}] {{$login->branch->account->short_name}}</h2>
                                <h3>[{{$login->branch->branch_code}}] {{$login->branch->branch_name}}</h3>
                                
                                @if(!empty($login->operation_process_id))
                                    <h4>Activities</h4>

                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label>{{$login->operation_process->operation_process}}</label>
                                            @php
                                                $branch_activities = $login->login_activities()->whereNotNull('activity_id')->get()
                                            @endphp
                                            <ol>
                                                @foreach($branch_activities as $activity)
                                                <li>{{$activity->activity->description}}
                                                    @if(!empty($activity->remarks))
                                                    <ul class="list-unstyled">
                                                        <li><b>Remarks: </b>{{$activity->remarks}}</li>
                                                    </ul>
                                                    @endif
                                                </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                @elseif(!empty($login->login_activities()->count()))
                                    <h4>Remarks</h4>
                                    <p>{{$login->login_activities()->first()->remarks}}</p>
                                @endif

                                @if(is_dir(public_path().'/uploads/branch-login/'.$login->user_id.'/'.$login->id.'/0'))
                                    <label>Picture/s:</label>
                                    @php
                                        $dirs = glob(public_path().'/uploads/branch-login/'.$login->user_id.'/'.$login->id.'/*', GLOB_ONLYDIR);
                                    @endphp
                                    <div class="mb-3">
                                        @for($i = 0; $i < count($dirs); $i++)
                                            <a href="{{ asset('/uploads/branch-login/'.$login->user_id.'/'.$login->id).'/'.$i.'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                                                <img src="{{asset('/uploads/branch-login/'.$login->user_id.'/'.$login->id.'/'.$i.'/small.jpg')}}" alt="picture" class="mx-1">
                                            </a>
                                        @endfor
                                    </div>
                                @elseif(file_exists(public_path().'/uploads/branch-login/'.$login->user_id.'/'.$login->id.'/small.jpg'))
                                    <label>Picture/s:</label>
                                    <div class="mb-3">
                                        <a href="{{ asset('/uploads/branch-login/'.$login->user_id.'/'.$login->id).'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                                            <img src="{{asset('/uploads/branch-login/'.$login->user_id.'/'.$login->id.'/small.jpg')}}" alt="picture" class="mx-1">
                                        </a>
                                    </div>
                                @endif

                                @php
                                $channel_operation = $login->channel_operations()->first();
                                @endphp

                                @if(!empty($channel_operation))
                                    {{-- COE REPORT --}}
                                    <div class="card card-outline card-primary">
                                        <div class="card-header align-middle">
                                            <h3 class="card-title">COE REPORT</h3>
                                            <div class="card-tools">
                                                <button type="button" class="btn btn-sm btn-{{$channel_operation->status == 'finalized' ? 'success' : 'secondary'}}">{{$channel_operation->status ?? 'draft'}}</button>
                                            </div>
                                        </div>
                                        <div class="card-body">

                                            {{-- ACCOUNT --}}
                                            <strong>
                                                <i class="fas fa-building"></i>
                                                ACCOUNT
                                            </strong>
                                            <p class="text-muted">
                                                {{$login->branch->account->short_name ?? '-'}}
                                            </p>
                                            {{-- NAME --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-address-card"></i>
                                                NAME
                                            </strong>
                                            <p class="text-muted">
                                                {{$login->user->fullName() ?? '-'}}
                                            </p>
                                            {{-- BRANCH --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-code-branch"></i>
                                                BRANCH
                                            </strong>
                                            <p class="text-muted">
                                                [{{$login->branch->branch_code ?? '-'}}] {{$login->branch->branch_name ?? '-'}}
                                            </p>
                                            {{-- DATE --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-calendar-day"></i>
                                                DATE
                                            </strong>
                                            <p class="text-muted">
                                                {{$channel_operation->date}}
                                            </p>
                                            {{-- STORE IN CHARGE --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-user-shield"></i>
                                                STORE IN CHARGE
                                            </strong>
                                            <p class="text-muted">
                                                {{$channel_operation->store_in_charge}}
                                            </p>
                                            {{-- POSITION --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-user-tag"></i>
                                                POSITION
                                            </strong>
                                            <p class="text-muted">
                                                {{$channel_operation->position}}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- MERCH UPDATE --}}
                                    @php
                                        $merch_updates = $channel_operation->merch_updates()->first();
                                    @endphp
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">MERCH UPDATE</h3>
                                        </div>
                                        <div class="card-body">
                                            {{-- STATUS --}}
                                            <strong>
                                                <i class="fas fa-tag"></i>
                                                MERCH STATUS
                                            </strong>
                                            <p class="text-muted">
                                                {{$merch_updates->status ?? '-'}}
                                            </p>
                                            {{-- ACTUAL --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-check-circle"></i>
                                                ACTUAL
                                            </strong>
                                            <p class="text-muted">
                                                {{$merch_updates->actual ?? '-'}}
                                            </p>
                                            {{-- TARGET --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-bullseye"></i>
                                                TARGET
                                            </strong>
                                            <p class="text-muted">
                                                {{$merch_updates->target ?? '-'}}
                                            </p>
                                            {{-- DAYS OF GAPS --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-cloud-sun"></i>
                                                DAYS OF GAPS
                                            </strong>
                                            <p class="text-muted">
                                                {{$merch_updates->days_of_gaps ?? '-'}}
                                            </p>
                                            {{-- SALES OPPORTUNITIES --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-money-check-alt"></i>
                                                SALES OPPORTUNITIES
                                            </strong>
                                            <p class="text-muted">
                                                {{$merch_updates->sales_opportunities ?? '-'}}
                                            </p>
                                            {{-- REMARKS --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-comment-dots"></i>
                                                REMARKS
                                            </strong>
                                            <p class="text-muted">
                                                {{$merch_updates->remarks ?? '-'}}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- TRADE DISPLAY --}}
                                    @php 
                                        $trade_displays = $channel_operation->trade_displays()->first();
                                    @endphp
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">TRADE DISPLAY</h3>
                                        </div>
                                        <div class="card-body">
                                            {{-- PLANOGRAM --}}
                                            <strong>
                                                <i class="fas fa-ruler-horizontal"></i>
                                                PLANOGRAM
                                            </strong>
                                            <p class="text-muted">
                                                {{$trade_displays->planogram ?? '-'}}
                                            </p>
                                            {{-- BEVI PRICING --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-tags"></i>
                                                BEVI PRICING
                                            </strong>
                                            <p class="text-muted">
                                                {{$trade_displays->bevi_pricing ?? '-'}}
                                            </p>
                                            {{-- ON SHELVES AVAILABILITY - BATH --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-shower"></i>
                                                ON SHELVES AVAILABILITY - BATH
                                            </strong>
                                            <p class="text-muted">
                                                <b class="">ACTUAL: </b> {{$trade_displays->osa_bath_actual ?? '-'}}
                                                <br>
                                                <b class="">TARGET: </b> {{$trade_display->osa_bath_target ?? '-'}}
                                                <br>
                                                @php
                                                    $percent = 0;
                                                    if(!empty($trade_displays->osa_bath_actual) && !empty($trade_display->osa_bath_target)) {
                                                        $percent = ($trade_displays->osa_bath_actual / $trade_display->osa_bath_target) * 100;
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
                                                <b class="">ACTUAL: </b> {{$trade_displays->osa_face_actual ?? '-'}}
                                                <br>
                                                <b class="">TARGET: </b> {{$trade_displays->osa_face_target ?? '-'}}
                                                <br>
                                                @php
                                                    $percent = 0;
                                                    if(!empty($trade_displays->osa_face_actual) && !empty($trade_displays->osa_face_target)) {
                                                        $percent = ($trade_displays->osa_face_actual / $trade_displays->osa_face_target) * 100;
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
                                                <b class="">ACTUAL: </b> {{$trade_displays->osa_body_actual}}
                                                <br>
                                                <b class="">TARGET: </b> {{$trade_displays->osa_body_target}}
                                                <br>
                                                @php
                                                    $percent = 0;
                                                    if(!empty($trade_displays->osa_body_actual) && !empty($trade_displays->osa_body_target)) {
                                                        $percent = ($trade_displays->osa_body_actual / $trade_displays->osa_body_target) * 100;
                                                    }
                                                @endphp 
                                                <b>PERCENT: {{number_format($percent, 1)}} %</b>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- TRADE MARKETING ACTIVITIES--}}
                                    @php
                                    $trade_marketing_activities = $channel_operation->trade_marketing_activities()->first();
                                    $paf = \App\Models\Paf::where('PAFNo', $trade_marketing_activities->paf_number)->first();
                                    @endphp
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">TRADE MARKETING ACTIVITIES</h3>
                                        </div>
                                        <div class="card-body">
                                            {{-- PAF NUMBER --}}
                                            <strong>
                                                <i class="fas fa-code"></i>
                                                PAF NUMBER
                                            </strong>
                                            <p class="text-muted">
                                                {{$trade_marketing_activities->paf_number ?? 'NONE'}}
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
                                                {{$paf->start_date ?? ' - '}} to {{$paf->end_date ?? ' - '}}
                                            </p>
                                            {{-- TYPE OF ACTIVITY --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-atom"></i>
                                                TYPE OF ACTIVITY
                                            </strong>
                                            <p class="text-muted">
                                                {{$paf->type ?? ' - '}}
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
                                                            @if(!empty($trade_marketing_activities->skus))
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
                                                {{$trade_marketing_activities->remarks ?? ' - '}}
                                            </p>

                                        </div>
                                    </div>

                                    {{-- DISPLAY RENTALS--}}
                                    @php
                                        $display_rentals = $channel_operation->display_rentals()->first();
                                    @endphp
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">DISPLAY RENTALS</h3>
                                        </div>
                                        <div class="card-body">
                                            {{-- DRA STATUS --}}
                                            <strong>
                                                <i class="fas fa-tag"></i>
                                                DRA STATUS
                                            </strong>
                                            <p class="text-muted">
                                                {{$display_rentals->status ?? '-'}}
                                            </p>
                                            {{-- DRA LOCATION --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-thumbtack"></i>
                                                DRA LOCATION
                                            </strong>
                                            <p class="text-muted">
                                                {{$display_rentals->location ?? '-'}}
                                            </p>
                                            {{-- STOCKS DISPLAYED --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-cubes"></i>
                                                % STOCKS DISPLAYED
                                            </strong>
                                            <p class="text-muted">
                                                {{$display_rentals->stocks_displayed ?? '-'}}
                                            </p>
                                            {{-- REMARKS --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-comment-dots"></i>
                                                REMARKS
                                            </strong>
                                            <p class="text-muted">
                                                {{$display_rentals->remarks ?? '-'}}
                                            </p>

                                        </div>
                                    </div>

                                    {{-- EXTRA DISPLAY --}}
                                    @php
                                        $extra_displays = $channel_operation->extra_displays()->first();
                                    @endphp
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">EXTRA DISPLAY</h3>
                                        </div>
                                        <div class="card-body">
                                            {{-- EXTRA DISPLAY --}}
                                            <strong>
                                                <i class="fas fa-map-marker-alt"></i>
                                                DISPLAY LOCATION
                                            </strong>
                                            <p class="text-muted">
                                                {{$extra_displays->location}}
                                            </p>
                                            {{-- RATE PER MONTH --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-percent"></i>
                                                RATE PER MONTH IF RENTED
                                            </strong>
                                            <p class="text-muted">
                                                {{$extra_displays->rate_per_month}}
                                            </p>
                                            {{-- AMOUNT --}}
                                            <hr>
                                            <strong>
                                                <i class="fas fa-sort-amount-up"></i>
                                                AMOUNT OF BEVI PRODUCTS DISPLAYED
                                            </strong>
                                            <p class="text-muted">
                                                {{$extra_displays->amount}}
                                            </p>
                                            
                                        </div>
                                    </div>

                                    {{-- COMPETETIVE REPORTS --}}
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">COMPETETIVE REPORTS</h3>
                                        </div>
                                        <div class="card-body">
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
                                                            @foreach($channel_operation->competetive_reports as $report)
                                                            <tr class="text-center">
                                                                <td>{{$report['company_name']}}</td>
                                                                <td>{{$report['product_description']}}</td>
                                                                <td>{{$report['srp']}}</td>
                                                                <td>{{$report['type_of_promotion']}}</td>
                                                                <td>{{$report['impact_to_our_product']}}</td>
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
                                                {{$channel_operation->total_findings}}
                                            </p>
                                            
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            {{$branch_logins->links()}}
                        </div>
                    </div>
                @else
                    <div class="list-group">
                        <div class="list-group-item text-center">
                            No Activities
                        </div>
                    </div>
                @endif
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
