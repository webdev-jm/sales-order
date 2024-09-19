<div>
    <div class="row mb-2">
        <div class="col-lg-6">
            <button class="btn btn-primary btn-sm" wire:click.prevent="save">
                <i class="fa fa-save fa-sm mr-1"></i>
                SAVE
            </button>
            <button class="btn btn-info btn-sm">
                <i class="fa fa-list fa-sm mr-1"></i>
                VIEW PRE-PLAN
            </button>
        </div>
    </div>

    <div class="row">
        {{-- PROGRAM INFO --}}
        <div class="col-lg-7">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">PROGRAM INFORMATION</h3>
                    <div class="card-tools">
                        
                    </div>
                </div>
                <div class="card-body">
        
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
                <div class="card-footer">
                </div>
            </div>
        </div>
        {{-- PROGRAM DURATION --}}
        <div class="col-lg-5">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">PROGRAM DURATION</h3>
                </div>
                <div class="card-body">

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
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="">ACTIVITY TYPE</label>
                                <select class="form-control form-control-sm">
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
                                            <tr>
                                                <td class="p-0">
                                                    <input type="text" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.product_id">
                                                </td>
                                                <td class="p-0">
                                                    <input type="text" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.branch_id">
                                                </td>
                                                <td class="p-0">
                                                    <input type="number" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.quantity">
                                                </td>
                                                <td class="p-0">
                                                    <input type="number" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.srp">
                                                </td>
                                                <td class="p-0">
                                                    <input type="number" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.percentage">
                                                </td>
                                                <td class="p-0">
                                                    <input type="number" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.amount">
                                                </td>
                                                <td class="p-0">
                                                    <input type="number" class="form-control form-control-sm border-0 text-right" wire:model="details.{{$key}}.expense">
                                                </td>
                                                <td class="p-0 text-center">
                                                    <button class="btn btn-xs btn-danger">
                                                        <i class="fa fa-trash-alt fa-sm"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
        });
    </script>
    
</div>
