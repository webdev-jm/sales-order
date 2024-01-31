<div>
    @if($stage != 8)
    <div class="bs-stepper">
        <div class="bs-stepper-header" role="tablist">
            <!-- your steps here -->
            @if($stage == 1)
                <div class="step {{$stage == 1 ? 'active' : ''}}" data-target="#stage1">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage1" id="stage1-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label">COE REPORTS</span>
                    </button>
                </div>
                <div class="line"></div>
            @endif
            @if($stage <= 2)
                <div class="step {{$stage == 2 ? 'active' : ''}}" data-target="#stage2">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage2" id="stage2-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label">MERCH UPDATE</span>
                    </button>
                </div>
                @if($stage == 2)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage >= 2 && $stage <= 3)
                <div class="step {{$stage == 3 ? 'active' : ''}}" data-target="#stage3">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage3" id="stage3-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label">TRADE DISPLAY</span>
                    </button>
                </div>
                @if($stage == 3)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage >= 3 && $stage <= 4)
                <div class="step {{$stage == 4 ? 'active' : ''}}" data-target="#stage4">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage4" id="stage4-trigger">
                        <span class="bs-stepper-circle">4</span>
                        <span class="bs-stepper-label">TRADE MARKETING ACTIVITIES</span>
                    </button>
                </div>
                @if($stage == 4)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage >= 4 && $stage <= 5)
                <div class="step {{$stage == 5 ? 'active' : ''}}" data-target="#stage5">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage5" id="stage5-trigger">
                        <span class="bs-stepper-circle">5</span>
                        <span class="bs-stepper-label">DISPLAY RENTALS</span>
                    </button>
                </div>
                @if($stage == 5)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage >= 5 && $stage <= 7)
                <div class="step {{$stage == 6 ? 'active' : ''}}" data-target="#stage6">
                    <button type="button" class="step-trigger" role="tab" aria-controls="stage6" id="stage6-trigger">
                        <span class="bs-stepper-circle">6</span>
                        <span class="bs-stepper-label">EXTRA DISPLAY</span>
                    </button>
                </div>
                @if($stage >= 6)
                    <div class="line"></div>
                @endif
            @endif
            @if($stage >= 6)
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
                            {{-- STORE IN CHARGE --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">POSITION</h3>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control form-underline{{$errors->has('coe_reports.position') ? ' is-invalid' : ''}}" placeholder="POSITION" wire:model="coe_reports.position">
                                        @if($errors->has('coe_reports.position'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
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
                            @if(!empty($merch_updates['status']) && $merch_updates['status'] == 'VACANT')
                                {{-- DAYS OF GAPS --}}
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">DAYS OF GAPS</h3>
                                        </div>
                                        <div class="card-body">
                                            <input type="number" class="form-control form-underline{{$errors->has('merch_updates.days_of_gaps') ? ' is-invalid' : ''}}" placeholder="DAYS OF GAPS" wire:model="merch_updates.days_of_gaps" wire:change="computeSales">
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
                                            <input type="text" class="form-control form-underline readonly{{$errors->has('merch_updates.sales_opportunities') ? ' is-invalid' : ''}}" placeholder="SALES OPPORTUNITIES" wire:model="merch_updates.sales_opportunities" readonly>
                                            @if($errors->has('merch_updates.sales_opportunities'))
                                                <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
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
                                                if(!empty($trade_displays['osa_bath_actual']) && !empty($trade_displays['osa_bath_target'])) {
                                                    $percent = ($trade_displays['osa_bath_actual'] / $trade_displays['osa_bath_target']) * 100;
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
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_bath_target') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_bath_target" placeholder="TOTAL">
                                                    @if($errors->has('trade_displays.osa_bath_target'))
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
                                                if(!empty($trade_displays['osa_face_actual']) && !empty($trade_displays['osa_face_target']) && $trade_displays['osa_face_target'] > 0 && $trade_displays['osa_face_actual'] > 0) {
                                                    $percent = ($trade_displays['osa_face_actual'] / $trade_displays['osa_face_target']) * 100;
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
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_face_target') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_face_target" placeholder="TOTAL">
                                                    @if($errors->has('trade_displays.osa_face_target'))
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
                                                if(!empty($trade_displays['osa_body_actual']) && !empty($trade_displays['osa_body_target'])) {
                                                    $percent = ($trade_displays['osa_body_actual'] / $trade_displays['osa_body_target']) * 100;
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
                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_displays.osa_body_target') ? ' is-invalid' : ''}}" wire:model="trade_displays.osa_body_target" placeholder="TOTAL">
                                                    @if($errors->has('trade_displays.osa_body_target'))
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

                        @if(!empty($pafs_data->count()))
                        <div class="row">
                            {{-- PAF --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">PAF NUMBER</h3>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control form-underline{{$errors->has('trade_marketing_activities.paf_number') ? ' is-invalid' : ''}}" wire:model="paf_number">
                                            <option value="">- PAF NUMBER -</option>
                                            <option value="NONE">NONE</option>
                                            @foreach($pafs_data as $paf_data)
                                                <option value="{{$paf_data->PAFNo}}">{{$paf_data->PAFNo}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('trade_marketing_activities.paf_number'))
                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

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
                                                <span class="float-right">{{$paf->title ?? '-'}}</span>
                                            </li>
                                            <li class="list-group-item p-1">
                                                <b>DURATION</b>
                                                <span class="float-right">{{($paf->start_date ?? '-').' to '.($paf->end_date ?? '-')}}</span>
                                            </li>
                                            <li class="list-group-item p-1">
                                                <b>TYPE OF ACTIVITIES</b>
                                                <span class="float-right">{{$paf->support_type ?? '-'}}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <div class="row">
                            
                            {{-- SKUs --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">PAF SKUs</h3>
                                    </div>
                                    <div class="card-body px-1">
                                        @if(!empty($paf_skus))
                                        <div class="row">
                                            @foreach($paf_skus as $paf_sku)
                                            <div class="col-lg-4">
                                                <div class="card card-outline card-primary">
                                                    <div class="card-header">
                                                        <h3 class="card-title">[<b>{{$paf_sku->sku_code}}</b>] - {{$paf_sku->sku_description}}</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            {{-- ACTUAL --}}
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="">ACTUAL</label>
                                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_marketing_activities.skus.'.$paf_sku->id.'.actual') ? ' is-invalid' : ''}}" wire:model="trade_marketing_activities.skus.{{$paf_sku->id}}.actual">
                                                                    @if($errors->has('trade_marketing_activities.skus.'.$paf_sku->id.'.actual'))
                                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            {{-- TARGET MAXCAP --}}
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="">TARGET MAXCAP</label>
                                                                    <input type="number" class="form-control form-underline{{$errors->has('trade_marketing_activities.skus.'.$paf_sku->id.'.target_maxcap') ? ' is-invalid' : ''}}" wire:model="trade_marketing_activities.skus.{{$paf_sku->id}}.target_maxcap">
                                                                    @if($errors->has('trade_marketing_activities.skus.'.$paf_sku->id.'.target_maxcap'))
                                                                        <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This is a required field.</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <ul class="list-froup list-group-unstyled">
                                            <li class="list-group-item text-center">No available data.</li>
                                        </ul>
                                        @endif
    
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
                                        <textarea class="form-control form-underline{{$errors->has('trade_marketing_activities.remarks') ? ' is-invalid' : ''}}" placeholder="REMARKS" wire:model="trade_marketing_activities.remarks"></textarea>
                                        @if($errors->has('trade_marketing_activities.remarks'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <ul class="list-group">
                            <li class="list-group-item text-center">
                                No available data
                            </li>
                        </ul>
                        @endif

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

                        <div class="row mb-4">
                            <div class="col-12">
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
                        </div>

                        <div class="row">
                            {{-- REMARKS --}}
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">TOTAL FINDINGS</h3>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control form-underline{{$errors->has('total_findings') ? ' is-invalid' : ''}}" placeholder="REMARKS" wire:model="total_findings"></textarea>
                                        @if($errors->has('total_findings'))
                                            <small class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i>This field is required</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-chevron-left mr-1"></i> PREVIOUS</button>
                        @if(!empty($competetive_reports))
                            <button class="btn btn-success" wire:click.prevent="next" wire:loading.attr="disabled"><i class="fa fa-save mr-1"></i> SAVE</button>
                        @endif
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">COE REPORT PREVIEW</h3>
            <div class="card-tools">
                @if(empty($status) || $status == 'draft')
                    <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-pen-alt mr-1"></i>EDIT</button>
                    <button class="btn btn-success text-uppercase" wire:click.prevent="finalize"><i class="fa fa-thumbs-up mr-1"></i>FINALIZE</button>
                @else
                    @if(!empty($coe_id) && auth()->user()->can('channel operation print'))
                    <a class="btn btn-warning" href="{{route('channel-operation.print', $coe_id)}}" target="_blank"><i class="fa fa-download mr-1"></i>DOWNLOAD</a>
                    @endif
                @endif
            </div>
        </div>
        <div class="card-body p-1">

            {{-- COE REPORT --}}
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header align-middle">
                            <h3 class="card-title">COE REPORT</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-{{$status == 'finalized' ? 'success' : 'secondary'}}">{{$status ?? 'draft'}}</button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- ACCOUNT --}}
                            <strong>
                                <i class="fas fa-building"></i>
                                ACCOUNT
                            </strong>
                            <p class="text-muted">
                                {{$coe_reports['account_name'] ?? '-'}}
                            </p>
                            {{-- NAME --}}
                            <hr>
                            <strong>
                                <i class="fas fa-address-card"></i>
                                NAME
                            </strong>
                            <p class="text-muted">
                                {{$coe_reports['name'] ?? '-'}}
                            </p>
                            {{-- BRANCH --}}
                            <hr>
                            <strong>
                                <i class="fas fa-code-branch"></i>
                                BRANCH
                            </strong>
                            <p class="text-muted">
                                [{{$coe_reports['branch_code'] ?? '-'}}] {{$coe_reports['branch_name'] ?? '-'}}
                            </p>
                            {{-- DATE --}}
                            <hr>
                            <strong>
                                <i class="fas fa-calendar-day"></i>
                                DATE
                            </strong>
                            <p class="text-muted">
                                {{$coe_reports['date'] ?? '-'}}
                            </p>
                            {{-- STORE IN CHARGE --}}
                            <hr>
                            <strong>
                                <i class="fas fa-user-shield"></i>
                                STORE IN CHARGE
                            </strong>
                            <p class="text-muted">
                                {{$coe_reports['store_in_charge'] ?? '-'}}
                            </p>
                            {{-- POSITION --}}
                            <hr>
                            <strong>
                                <i class="fas fa-user-tag"></i>
                                POSITION
                            </strong>
                            <p class="text-muted">
                                {{$coe_reports['position'] ?? '-'}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3"></div>
            </div>
            
            {{-- MERCH UPDATE --}}
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
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
                </div>
                <div class="col-lg-3"></div>
            </div>

            {{-- TRADE DISPLAY --}}
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
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
                </div>
                <div class="col-lg-3"></div>
            </div>

            {{-- TRADE MARKETING ACTIVITIES--}}
            @if(!empty($pafs_data->count()))
                <div class="row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
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
                                    {{$trade_marketing_activities['paf_number'] ?? 'NONE'}}
                                </p>
                                {{-- PROGRAM TITLE --}}
                                <hr>
                                <strong>
                                    <i class="fas fa-tasks"></i>
                                    PROGRAM TITLE
                                </strong>
                                <p class="text-muted">
                                    {{$trade_marketing_activities['title'] ?? ' - '}}
                                </p>
                                {{-- DURATION --}}
                                <hr>
                                <strong>
                                    <i class="fas fa-history"></i>
                                    DURATION
                                </strong>
                                <p class="text-muted">
                                    {{$trade_marketing_activities['start_date'] ?? ' - '}} to {{$trade_marketing_activities['end_date'] ?? ' - '}}
                                </p>
                                {{-- TYPE OF ACTIVITY --}}
                                <hr>
                                <strong>
                                    <i class="fas fa-atom"></i>
                                    TYPE OF ACTIVITY
                                </strong>
                                <p class="text-muted">
                                    {{$trade_marketing_activities['type'] ?? ' - '}}
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
                                                @if(!empty($trade_marketing_activities['skus']))
                                                    @foreach($trade_marketing_activities['skus'] as $sku)
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
                    </div>
                    <div class="col-lg-3"></div>
                </div>
            @endif

            {{-- DISPLAY RENTALS--}}
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
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
                </div>
                <div class="col-lg-3"></div>
            </div>

            {{-- EXTRA DISPLAY --}}
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
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
                </div>
                <div class="col-lg-3"></div>
            </div>

            {{-- COMPETETIVE REPORTS --}}
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
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
                                {{$total_findings ?? '-'}}
                            </p>
                            
                        </div>
                    </div>
                </div>
                <div class="col-lg-3"></div>
            </div>

        </div>
        <div class="card-footer text-right">
            @if(empty($status) || $status == 'draft')
                <button class="btn btn-primary" wire:click.prevent="prev" wire:loading.attr="disabled"><i class="fa fa-pen-alt mr-1"></i>EDIT</button>
                <button class="btn btn-success text-uppercase" wire:click.prevent="finalize"><i class="fa fa-thumbs-up mr-1"></i>FINALIZE</button>
            @else
                @if(!empty($coe_id) && auth()->user()->can('channel operation print'))
                <a class="btn btn-warning" href="{{route('channel-operation.print', $coe_id)}}" target="_blank"><i class="fa fa-download mr-1"></i>DOWNLOAD</a>
                @endif
            @endif
        </div>
    </div>
    @endif
</div>

