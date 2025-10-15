<div>


    <form wire:submit.prevent="checkFileData">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">UPLOAD PPU<i class="fa fa-spinner-fa-spin" wire:loading></i></h3>
                <div class="card-tools">
                    <a href="{{asset('/assets/SMS PPU upload Format.xlsx')}}" class="btn btn-success btn-sm">
                        <i class="fa fa-download mr-1"></i>
                        DOWNLOAD TEMPLATE
                    </a>
                </div>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="">UPLOAD FILE</label>
                            <input type="file" class="form-control" wire:model.defer="so_file" accept=".xls, .xlsx">
                        </div>
                    </div>
                </div>
        
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                    <i class="fa fa-upload mr-1" wire:loading.remove></i>
                    <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                    UPLOAD
                </button>
            </div>
        </div>
    </form>

    @if(!empty($ppu_data))
    <div class="row">
        <div class="col-12 mb-3">
            <button class="btn btn-secondary" wire:click.prevent="saveAll('draft')" wire:loading.attr="disabled">
                <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                DRAFT ALL
            </button>
            <button class="btn btn-success" wire:click.prevent="saveAll('finalized')" wire:loading.attr="disabled">
                <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                FINALIZE ALL
            </button>
        </div>


            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">CONTROL NUMBER: <b>{{$success_data['control_number'] ?? 'N/A'}}</b></div>
                        <div class="card-tools">
                            @if(empty($success_data['control_number']))
                                <button class="btn btn-secondary" wire:loading.attr="disabled" wire:click.prevent="savePPUForm('draft', '')">
                                    <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                                    Save as Draft
                                </button>
                                <button class="btn btn-success" wire:loading.attr="disabled" wire:click.prevent="savePPUForm('finalized', '')">
                                    <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                                    Finalize
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if(!empty($err_data))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-danger pl-0">
                                        <ul class="mb-0">
                                            @foreach($err_data as $err)
                                                <li class="">{{$err}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($success_data['message']))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success">
                                        {{$success_data['message']}}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    {{$success_data['control_number'] ?? 'N/A'}}
                                    <small class="float-right">
                                        @if(!empty($success_data['status']))
                                            <span class="badge {{$success_data['status'] == 'draft' ? 'bg-secondary' : 'bg-success'}}">
                                                {{$success_data['status']}}
                                            </span>
                                        @else
                                            <span class="badge badge-info">
                                                preview
                                            </span>
                                        @endif
                                    </small>
                                </h4>
                            </div>
                        </div>
                        
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <b>Account:</b> [{{$account->account_code}}] {{$account->short_name}}<br>
                                <b>Date Prepared:</b> {{ date('Y-m-d') }} <br>

                            </div>
                            <div class="col-sm-4 invoice-col">
                                <b>Submitted Date:</b><br>
                                <p>
                                    {{$ppu_data['date_submitted']}}
                                </p>
                                <b>Pick-up Date:</b><br>
                                <p>
                                    {{$ppu_data['pickup_date']}}
                                </p>
                            </div>    
                        </div>
                        <div class="row">
                            
                            <div class="col-12 table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-thead">
                                        <tr>
                                            <th>#</th>
                                            <th class="align-middle">RTV/RS No.</th>
                                            <th class="align-middle">RTV Date</th>
                                            <th class="align-middle">Branch Name</th>
                                            <th class="align-middle">Quantity</th>
                                            <th class="align-middle">Amount</th>
                                            <th class="align-middle">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $num = 0;
                                            $quantity_total = 0;
                                            $amount_total = 0;
                                        @endphp
                                        @foreach($ppu_data['lines'] as $key => $val)
                                            @php
                                                $num++;
                                            @endphp
                                            <tr>
                                                <td class="align-middle text-center">{{$num}}</td>
                                                <td class="align-middle">{{$val['rtv_number']}}</td>
                                                <td class="align-middle">{{$val['rtv_date']}}</td>
                                                <td class="align-middle">{{$val['branch_name']}}</td>
                                                <td class="text-right">{{$val['total_quantity']}}</td>
                                                <td class="text-right">{{number_format($val['total_amount'], 2)}}</td>
                                                <td class="text-right">{{$val['remarks']}}</td>
                                            </tr>
                                            @php
                                                $quantity_total += $val['total_quantity'];
                                                $amount_total += $val['total_amount'];
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    
                        <div class="row">
                            <div class="col-lg-6">
                                <p class="lead">PPU Summary</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">Total Quantity:</th>
                                            <td class="text-right">{{$quantity_total ?? ''}}</td>
                                       
                                        </tr>
                                        <tr>
                                            <th style="width:50%">Total Amount:</th>
                                            <td class="text-right">{{number_format($amount_total, 2)}}</td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    @endif

</div>
