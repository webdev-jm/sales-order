<div>
    <div class="row mb-2">
        <div class="col-lg-6">
            <button class="btn btn-secondary btn-sm" wire:click.prevent="savePaf('draft')" wire:loading.attr="disabled" wire:target="savePaf">
                <i class="fa fa-save fa-sm mr-1" wire:loading.remove wire:target="savePaf"></i>
                <i class="fa fa-spinner fa-spin fa-sm mr-1" wire:loading wire:target="savePaf"></i>
                SAVE AS DRAFT
            </button>
            <button class="btn btn-primary btn-sm" wire:click.prevent="savePaf('submitted')" wire:loading.attr="disabled" wire:target="savePaf">
                <i class="fa fa-save fa-sm mr-1" wire:loading.remove wire:target="savePaf"></i>
                <i class="fa fa-spinner fa-spin fa-sm mr-1" wire:loading wire:target="savePaf"></i>
                SUBMIT
            </button>
            <button class="btn btn-info btn-sm" id="btn-view-pre-plan">
                <i class="fa fa-list fa-sm mr-1"></i>
                VIEW PRE-PLAN
            </button>
        </div>
    </div>

    @if(!empty($pre_plan_number))
        <h3 class="mr-2">
            PRE PLAN NUMBER:
            <button class="btn btn-primary btn-lg mb-2 py-1 d-inline">
                {{$pre_plan_number}}
            </button>
        </h3>
    @endif

    <div class="row">
        {{-- PROGRAM INFO --}}
        <div class="col-lg-7">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">PROGRAM INFORMATION</h3>
                    <div class="card-tools">
                        
                    </div>
                </div>
                <div class="card-body pb-1">
        
                    <div class="row">
        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">ACCOUNT</label>
                                <select class="form-control form-control-sm{{$errors->has('account_id') ? ' is-invalid' : ''}}" wire:model="account_id">
                                    <option value="">- select account -</option>
                                    @foreach($accounts as $account)
                                        <option value="{{$account->id}}">[{{$account->account_code}}] {{$account->short_name}}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger">{{$errors->first('account_id')}}</small>
                            </div>
                        </div>
        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">SUPPORT TYPE</label>
                                <select class="form-control form-control-sm{{$errors->has('support_type_id') ? ' is-invalid' : ''}}" wire:model="support_type_id">
                                    <option value="">- select support type -</option>
                                    @foreach($support_types as $support)
                                        <option value="{{$support->id}}">{{$support->support}}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger">{{$errors->first('support_type_id')}}</small>
                            </div>
                        </div>
        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">EXPENSE TYPE</label>
                                <select class="form-control form-control-sm{{$errors->has('expense_type_id') ? ' is-invalid' : ''}}" wire:model="expense_type_id">
                                    <option value="">- select expense type -</option>
                                    @foreach($expense_types as $expense)
                                        <option value="{{$expense->id}}">{{$expense->expense}}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger">{{$errors->first('expense_type_id')}}</small>
                            </div>
                        </div>
        
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="">TITLE</label>
                                <textarea rows="3" class="form-control form-control-sm{{$errors->has('title') ? ' is-invalid' : ''}}" placeholder="Title" wire:model="title"></textarea>
                                <small class="text-danger">{{$errors->first('title')}}</small>
                            </div>
                        </div>
        
                    </div>
        
                </div>
            </div>
        </div>
        {{-- PROGRAM DURATION --}}
        <div class="col-lg-5">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">PROGRAM DURATION</h3>
                </div>
                <div class="card-body pb-1">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">PROGRAM START</label>
                                <input type="date" class="form-control form-control-sm{{$errors->has('program_start') ? ' is-invalid' : ''}}" wire:model="program_start">
                                <small class="text-danger">{{$errors->first('program_start')}}</small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">PROGRAM END</label>
                                <input type="date" class="form-control form-control-sm{{$errors->has('program_end') ? ' is-invalid' : ''}}" wire:model="program_end">
                                <small class="text-danger">{{$errors->first('program_end')}}</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{-- COST COMPONENTS --}}
        <div class="col-lg-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">COST COMPONENT</h3>
                </div>
                <div class="card-body pb-1">

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="">ACTIVITY TYPE</label>
                                <select class="form-control form-control-sm" wire:model="activity_id">
                                    <option value="">- select activity type -</option>
                                    @foreach($activities as $activity)
                                        <option value="{{$activity->id}}">
                                            {{!empty($activity->activity) ? $activity->activity : $activity->components}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <button class="btn btn-info btn-sm" id="btn-add-detail">
                                <i class="fa fa-plus fa-sm mr-1">ADD LINE</i>
                            </button>
                        </div>

                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="text-center">
                                            <th>TYPE</th>
                                            <th>PRODUCT</th>
                                            <th>BRANCH</th>
                                            <th>QUANTITY</th>
                                            <th>SRP</th>
                                            <th>PERCENTAGE</th>
                                            <th>AMOUNT</th>
                                            <th>EXPENSE</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($details as $key => $detail)
                                            @if(!empty($detail['product']))
                                                <tr>
                                                    <td class="p-0 pl-2">
                                                        {{$detail['type']}}
                                                    </td>
                                                    <td class="p-0 pl-2">
                                                        {{$detail['product']}}
                                                    </td>
                                                    <td class="p-0 pl-2">
                                                        {{$detail['branch']}}
                                                    </td>
                                                    <td class="p-0 pr-2 text-right">
                                                        {{number_format(empty($detail['quantity']) ? 0 : $detail['quantity'])}}
                                                    </td>
                                                    <td class="p-0 pr-2 text-right">
                                                        {{number_format(empty($detail['srp']) ? 0 : $detail['srp'], 2)}}
                                                    </td>
                                                    <td class="p-0 pr-2 text-right">
                                                        {{number_format(empty($detail['percentage']) ? 0 : $detail['percentage'], 2)}}
                                                    </td>
                                                    <td class="p-0 pr-2 text-right">
                                                        {{number_format(empty($detail['amount']) ? 0 : $detail['amount'], 2)}}
                                                    </td>
                                                    <td class="p-0 pr-2 text-right">
                                                        {{number_format(empty($detail['expense']) ? 0 : $detail['expense'], 2)}}
                                                    </td>
                                                    <td class="p-0 text-center">
                                                        <button class="btn btn-xs btn-danger" wire:click.prevent="removeLine({{$key}})">
                                                            <i class="fa fa-trash-alt fa-sm"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                    </div>

                </div>
            </div>
        </div>
        {{-- ATTACHMENTS --}}
        <div class="col-lg-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">ATTACHMENTS</h3>
                </div>
                <div class="card-body">

                    <div class="row">
                        <!-- ATTACHMENT LIST -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">ATTACHMENT LIST</h3>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>FILE</th>
                                                <th>TITLE</th>
                                                <th>DESCRIPTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(!empty($attachments))
                                                @foreach($attachments as $attachment)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ $attachment['file']->temporaryUrl() }}">
                                                                <!-- <img src="{{ $attachment['file']->temporaryUrl() }}" class="mx-auto d-block" height="300px"> -->
                                                            </a>
                                                        </td>
                                                        <th>{{$attachment['title']}}</th>
                                                        <th>{{$attachment['description']}}</th>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ADD ATTACHMENT -->
                         <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">ADD ATTACHMENT</h3>
                                </div>
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="attachment_file">ATTACHMENT</label>
                                                <input type="file" class="form-control{{$errors->has('attachment_file') ? ' is-invalid' : ''}}" wire:model="attachment_file">
                                                <small class="text-danger">{{$errors->first('attachment_file')}}</small>
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">TITLE</label>
                                                <input type="text" class="form-control{{$errors->has('attachment_title') ? ' is-invalid' : ''}}" wire:model="attachment_title">
                                                <small class="text-danger">{{$errors->first('attachment_title')}}</small>
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">DESCRIPTION</label>
                                                <textarea class="form-control{{$errors->has('attachment_description') ? ' is-invalid' : ''}}" wire:model="attachment_description"></textarea>
                                                <small class="text-danger">{{$errors->first('attachment_description')}}</small>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <button type="button" class="btn btn-primary" wire:click.prevent="addAttachment">
                                        ADD ATTACHMENT
                                    </button>
                                </div>
                            </div>
                         </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            $('body').on('click', '#btn-add-detail', function(e) {
                e.preventDefault();
                Livewire.emit('pafAddDetail');
                $('#modal-summary').modal('show');
            });

            $('body').on('click', '#btn-view-pre-plan', function(e) {
                e.preventDefault();
                $('#modal-pre-plan').modal('show');
            });
        });
    </script>
    
</div>
