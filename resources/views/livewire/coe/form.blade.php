<div>
    <div class="bs-stepper">
        <div class="bs-stepper-header" role="tablist">
            <!-- your steps here -->
            @if($stage <= 2)
                <div class="step {{$stage == 1 ? 'active' : ''}}" data-target="#stage1">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage1" id="stage1-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label">COE REPORTS</span>
                    </button>
                </div>
                <div class="line"></div>
            @endif
            @if($stage <= 3)
                <div class="step {{$stage == 2 ? 'active' : ''}}" data-target="#stage2">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage2" id="stage2-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label">MERCH UPDATE</span>
                    </button>
                </div>
                @if($stage == 3)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage > 2 && $stage <= 4)
                <div class="step {{$stage == 3 ? 'active' : ''}}" data-target="#stage3">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage3" id="stage3-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label">TRADE DISPLAY</span>
                    </button>
                </div>
                @if($stage == 4)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage > 3 && $stage <= 5)
                <div class="step {{$stage == 4 ? 'active' : ''}}" data-target="#stage4">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage4" id="stage4-trigger">
                        <span class="bs-stepper-circle">4</span>
                        <span class="bs-stepper-label">TRADE MARKETING ACTIVITIES</span>
                    </button>
                </div>
                @if($stage == 5)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage > 4 && $stage <= 6)
                <div class="step {{$stage == 5 ? 'active' : ''}}" data-target="#stage5">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage5" id="stage5-trigger">
                        <span class="bs-stepper-circle">5</span>
                        <span class="bs-stepper-label">DISPLAY RENTALS</span>
                    </button>
                </div>
                @if($stage == 6)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage > 5 && $stage <= 7)
                <div class="step {{$stage == 6 ? 'active' : ''}}" data-target="#stage6">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage6" id="stage6-trigger">
                        <span class="bs-stepper-circle">6</span>
                        <span class="bs-stepper-label">EXTRA DISPLAY</span>
                    </button>
                </div>
                @if($stage == 7)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage == 7)
                <div class="step {{$stage == 7 ? 'active' : ''}}" data-target="#stage7">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage7" id="stage7-trigger">
                        <span class="bs-stepper-circle">7</span>
                        <span class="bs-stepper-label">COMPETETIVE REPORTS</span>
                    </button>
                </div>
            @endif
        </div>
        <div class="bs-stepper-content">
            {{-- COE REPORTS --}}
            <div id="stage1" class="content {{$stage == 1 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage1-trigger">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">COE REPORTS</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            {{-- DATE --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">DATE</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="date" class="form-control form-underline readonly" wire:model="coe_reports.date" readonly>
                                    </div>
                                </div>
                            </div>
                            {{-- COE NAME --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">COE NAME</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline readonly" placeholder="COE NAME" wire:model="coe_reports.name" readonly>
                                    </div>
                                </div>
                            </div>
                            {{-- ACCOUNT NAME --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">ACCOUNT NAME</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline readonly" placeholder="ACCOUNT NAME" wire:model="coe_reports.account_name" readonly>
                                    </div>
                                </div>
                            </div>
                            {{-- BRANCH CODE --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">BRANCH CODE</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline readonly" placeholder="BRANCH CODE" wire:model="coe_reports.branch_code" readonly>
                                    </div>
                                </div>
                            </div>
                            {{-- BRANCH NAME --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">BRANCH NAME</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline readonly" placeholder="BRANCH NAME" wire:model="coe_reports.branch_name" readonly>
                                    </div>
                                </div>
                            </div>
                            {{-- STORE IN CHARGE --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">STORE IN CHARGE</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline{{$errors->has('coe_reports.store_in_charge') ? ' is-invalid' : ''}}" placeholder="STORE IN CHARGE" wire:model="coe_reports.store_in_charge">
                                        @if($errors->has('coe_reports.store_in_charge'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- DATE --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">COE DATE</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="date" class="form-control form-underline{{$errors->has('coe_reports.coe_date') ? ' is-invalid' : ''}}" placeholder="DATE" wire:model="coe_reports.coe_date">
                                        @if($errors->has('coe_reports.coe_date'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="next" wire:loading.attr="disabled">NEXT <i class="fa fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
            
            </div>
            {{-- MERCH UPDATE --}}
            <div id="stage2" class="content {{$stage == 2 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage2-trigger">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">MERCH UPDATE</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            {{-- MERCH STATUS --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">MERCH STATUS</h3>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control form-underline{{$errors->has('merch_updates.status') ? ' is-invalid' : ''}}" wire:model="merch_updates.status">
                                            <option value="">-- MERCH STATUS --</option>
                                            @foreach($merch_status_arr as $status)
                                                <option value="{{$status}}">{{$status}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('merch_updates.status'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- ACTUAL --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">ACTUAL</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('merch_updates.actual') ? ' is-invalid' : ''}}" placeholder="ACTUAL" wire:model="merch_updates.actual">
                                        @if($errors->has('merch_updates.actual'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- TARGET --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">TARGET</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('merch_updates.target') ? ' is-invalid' : ''}}" placeholder="TARGET" wire:model="merch_updates.target">
                                        @if($errors->has('merch_updates.target'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- DAYS OF GAPS --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">DAYS OF GAPS</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('merch_updates.days_of_gaps') ? ' is-invalid' : ''}}" placeholder="DAYS OF GAPS" wire:model="merch_updates.days_of_gaps">
                                        @if($errors->has('merch_updates.days_of_gaps'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- SALES OPPORTUNITIES --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">SALES OPPORTUNITIES</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('merch_updates.sales_opportunities') ? ' is-invalid' : ''}}" placeholder="SALES OPPORTUNITIES" wire:model="merch_updates.sales_opportunities">
                                        @if($errors->has('merch_updates.sales_opportunities'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- REMARKS --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">REMARKS</h3>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control form-underline{{$errors->has('merch_updates.remarks') ? ' is-invalid' : ''}}" placeholder="REMARKS" wire:model="merch_updates.remarks"></textarea>
                                        @if($errors->has('merch_updates.remarks'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        <button class="btn btn-primary" wire:click.prevent="next" wire:loading.attr="disabled">NEXT <i class="fa fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
                
            </div>
            {{-- TRADE DISPLAY --}}
            <div id="stage3" class="content {{$stage == 3 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage3-trigger">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">TRADE DISPLAY</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            {{-- PLANOGRAM --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">PLANOGRAM</h3>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control form-underline{{$errors->has('trade_displays.planogram') ? ' is-invalid' : ''}}" wire:model="trade_displays.planogram">
                                            <option value="">-- PLANOGRAM --</option>
                                            @foreach($planogram_arr as $planogram)
                                                <option value="{{$planogram}}">{{$planogram}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('trade_displays.planogram'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- BEVI PRICING --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">BEVI PRICING</h3>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control form-underline{{$errors->has('trade_displays.bevi_pricing') ? ' is-invalid' : ''}}" wire:model="trade_displays.bevi_pricing">
                                            <option value="">-- BEVI PRICING --</option>
                                            @foreach($bevi_pricing_arr as $pricing)
                                                <option value="{{$pricing}}">{{$pricing}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('trade_displays.bevi_pricing'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                        </div>
        
                        <div class="row">
                            {{-- ON SHELVES AVAILABILITY - BATH --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">ON SHELVES AVAILABILITY - BATH</h3>
                                        <div class="card-tools">
                                            @php
                                                $percent = 0;
                                                if(!empty($trade_displays['osa_bath_actual']) && !empty($trade_displays['osa_bath_total'])) {
                                                    $percent = ($trade_displays['osa_bath_actual'] / $trade_displays['osa_bath_total']) * 100;
                                                }
                                            @endphp
                                            <span class="badge badge-info" style="font-size: 15px;">{{number_format($percent, 1)}}%</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            {{-- ACTUAL --}}
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">ACTUAL</label>
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_bath_actual') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_bath_actual" placeholder="ACTUAL">
                                                    @if($errors->has('trade_displays.osa_bath_actual'))
                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- TOTAL --}}
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">TOTAL</label>
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_bath_total') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_bath_total" placeholder="TOTAL">
                                                    @if($errors->has('trade_displays.osa_bath_total'))
                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            {{-- ON SHELVES AVAILABILITY - FACE --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">ON SHELVES AVAILABILITY - FACE</h3>
                                        <div class="card-tools">
                                            @php
                                                $percent = 0;
                                                if(!empty($trade_displays['osa_face_actual']) && !empty($trade_displays['osa_face_total'])) {
                                                    $percent = ($trade_displays['osa_face_actual'] / $trade_displays['osa_face_total']) * 100;
                                                }
                                            @endphp
                                            <span class="badge badge-info" style="font-size: 15px;">{{number_format($percent, 1)}}%</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            {{-- ACTUAL --}}
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">ACTUAL</label>
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_face_actual') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_face_actual" placeholder="ACTUAL">
                                                    @if($errors->has('trade_displays.osa_face_actual'))
                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- TOTAL --}}
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">TOTAL</label>
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_face_total') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_face_total" placeholder="TOTAL">
                                                    @if($errors->has('trade_displays.osa_face_total'))
                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            {{-- ON SHELVES AVAILABILITY - BODY --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">ON SHELVES AVAILABILITY - BODY</h3>
                                        <div class="card-tools">
                                            @php
                                                $percent = 0;
                                                if(!empty($trade_displays['osa_body_actual']) && !empty($trade_displays['osa_body_total'])) {
                                                    $percent = ($trade_displays['osa_body_actual'] / $trade_displays['osa_body_total']) * 100;
                                                }
                                            @endphp
                                            <span class="badge badge-info" style="font-size: 15px;">{{number_format($percent, 1)}}%</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            {{-- ACTUAL --}}
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">ACTUAL</label>
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_body_actual') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_body_actual" placeholder="ACTUAL">
                                                    @if($errors->has('trade_displays.osa_body_actual'))
                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- TOTAL --}}
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">TOTAL</label>
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_body_total') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_body_total" placeholder="TOTAL">
                                                    @if($errors->has('trade_displays.osa_body_total'))
                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <div class="row">
                             {{-- REMARKS --}}
                             <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">REMARKS</h3>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control form-underline{{$errors->has('trade_displays.remarks') ? ' is-invalid' : ''}}" placeholder="REMARKS" wire:model="trade_displays.remarks"></textarea>
                                        @if($errors->has('trade_displays.remarks'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        <button class="btn btn-primary" wire:click.prevent="next" wire:loading.attr="disabled">NEXT <i class="fa fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
                
            </div>
            {{-- TRADE MARKETING ACTIVITIES --}}
            <div id="stage4" class="content {{$stage == 4 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage4-trigger">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">TRADE MARKETING ACTIVITIES</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            {{-- PAF --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">PAF</h3>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control form-underline">
                                            <option value="">- SELECT PAF -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <div class="row">
                            {{-- PAF DETAILS --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">PAF DETAILS</h3>
                                    </div>
                                    <div class="card-body py-0 px-2">
                                        <ul class="list-group list-group-unbordered">
                                            <li class="list-group-item p-1">
                                                <b>PROGRAM TITLE</b>
                                                <span class="float-right">-</span>
                                            </li>
                                            <li class="list-group-item p-1">
                                                <b>DURATION</b>
                                                <span class="float-right">-</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        <button class="btn btn-primary" wire:click.prevent="next" wire:loading.attr="disabled">NEXT <i class="fa fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
                
            </div>
            {{-- DISPLAY RENTALS --}}
            <div id="stage5" class="content {{$stage == 5 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage5-trigger">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">DISPLAY RENTALS</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            {{-- DRA STATUS --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">DRA STATUS</h3>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control form-underline{{$errors->has('display_rentals.status') ? ' is-invalid' : ''}}" wire:model="display_rentals.status">
                                            <option value="">-- DRA STATUS --</option>
                                            @foreach($dra_status_arr as $status)
                                                <option value="{{$status}}">{{$status}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('display_rentals.status'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- DRA LOCATION --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">DRA LOCATION</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline{{$errors->has('display_rentals.location') ? ' is-invalid' : ''}}" wire:model="display_rentals.location" placeholder=" DRA LOCATION">
                                        @if($errors->has('display_rentals.location'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- STOCKS DISPLAYED --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">% STOCKS DISPLAYED</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('display_rentals.stocks_displayed') ? ' is-invalid' : ''}}" wire:model="display_rentals.stocks_displayed" placeholder="% STOCKS DISPLAYED">
                                        @if($errors->has('display_rentals.stocks_displayed'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- REMARKS --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">REMARKS</h3>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control form-underline{{$errors->has('display_rentals.remarks') ? ' is-invalid' : ''}}" placeholder="REMARKS" wire:model="display_rentals.remarks"></textarea>
                                        @if($errors->has('display_rentals.remarks'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        <button class="btn btn-primary" wire:click.prevent="next" wire:loading.attr="disabled">NEXT <i class="fa fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
                
            </div>
            {{-- EXTRA DISPLAY --}}
            <div id="stage6" class="content {{$stage == 6 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage6-trigger">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">EXTRA DISPLAY</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            {{-- DISPLAY LOCATION --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">DISPLAY LOCATION</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline{{$errors->has('extra_displays.location') ? ' is-invalid' : ''}}" wire:model="extra_displays.location" placeholder="DISPLAY LOCATION">
                                        @if($errors->has('extra_displays.location'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- RATE PER MONTH --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">RATE PER MONTH IF RENTED</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('extra_displays.rate_per_month') ? ' is-invalid' : ''}}" wire:model="extra_displays.rate_per_month" placeholder="RATE PER MONTH">
                                        @if($errors->has('extra_displays.rate_per_month'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- AMOUNT OF BEVI PRODUCTS DISPLAYED --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">AMOUNT OF BEVI PRODUCTS DISPLAYED</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="number" class="form-control form-underline{{$errors->has('extra_displays.amount') ? ' is-invalid' : ''}}" wire:model="extra_displays.amount" placeholder="AMOUNT">
                                        @if($errors->has('extra_displays.amount'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        <button class="btn btn-primary" wire:click.prevent="next" wire:loading.attr="disabled">NEXT <i class="fa fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
                
            </div>
            {{-- COMPETETIVE REPORTS --}}
            <div id="stage7" class="content {{$stage == 7 ? 'active' : ''}}" role="tabpanel" aria-labelledby="stage7-trigger">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">COMPETETIVE REPORTS</h3>
                        <div class="card-tools">
                            <button class="btn btn-info" wire:click.prevent="addLine" wire:loading.attr="disabled">
                                <i class="fa fa-plus mr-1" wire:loading.remove></i>
                                <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                                ADD LINE
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <ul class="list-group">
                            @if(!empty($competetive_reports))
                                @foreach($competetive_reports as $key => $report)
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-lg-2">
                                                <label for="">COMPANY NAME</label>
                                                <input type="text" class="form-control form-underline{{$errors->has('competetive_reports.'.$key.'.company_name') ? ' is-invalid' : ''}}" placeholder="COMPANY NAME" wire:model="competetive_reports.{{$key}}.company_name">
                                                @if($errors->has('competetive_reports.'.$key.'.company_name'))
                                                    <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                @endif
                                            </div>
                                            <div class="col-lg-3">
                                                <label for="">PRODUCT DESCRIPTION</label>
                                                <input type="text" class="form-control form-underline{{$errors->has('competetive_reports.'.$key.'.product_description') ? ' is-invalid' : ''}}" placeholder="PRODUCT DESCRIPTION" wire:model="competetive_reports.{{$key}}.product_description">
                                                @if($errors->has('competetive_reports.'.$key.'.product_description'))
                                                    <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                @endif
                                            </div>
                                            <div class="col-lg-2">
                                                <label for="">SRP</label>
                                                <input type="text" class="form-control form-underline{{$errors->has('competetive_reports.'.$key.'.srp') ? ' is-invalid' : ''}}" placeholder="SRP" wire:model="competetive_reports.{{$key}}.srp">
                                                @if($errors->has('competetive_reports.'.$key.'.srp'))
                                                    <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                @endif
                                            </div>
                                            <div class="col-lg-2">
                                                <label for="">TYPE OF PROMOTION</label>
                                                <input type="text" class="form-control form-underline{{$errors->has('competetive_reports.'.$key.'.type_of_promotion') ? ' is-invalid' : ''}}" placeholder="TYPE OF PROMOTION" wire:model="competetive_reports.{{$key}}.type_of_promotion">
                                                @if($errors->has('competetive_reports.'.$key.'.type_of_promotion'))
                                                    <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                @endif
                                            </div>
                                            <div class="col-lg-2">
                                                <label for="">IMPACT TO OUR PRODUCT</label>
                                                <input type="text" class="form-control form-underline{{$errors->has('competetive_reports.'.$key.'.impact_to_our_product') ? ' is-invalid' : ''}}" placeholder="IMPACT TO OUR PRODUCT" wire:model="competetive_reports.{{$key}}.impact_to_our_product">
                                                @if($errors->has('competetive_reports.'.$key.'.impact_to_our_product'))
                                                    <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                @endif
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-danger btn-sm btn-block" wire:click.prevent="removeLine({{$key}})" wire:loading.attr="disabled">REMOVE</button>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        <button class="btn btn-success" wire:click.prevent="next" wire:loading.attr="disabled"><i class="fa fa-save mr-1"></i> SAVE</button>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

