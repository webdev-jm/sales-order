<div>
    @if(!$addTemplate)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Templates</h3>
                <div class="card-tools">
                    <a href="" class="btn btn-primary btn-sm" wire:click.prevent="toggleTemplateForm">
                        <i class="fa fa-plus fa-sm"></i>
                        Add Template
                    </a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>NAME</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account_templates as $account_template)
                            <tr>
                                <td>{{$account_template->upload_template->name}}</td>
                                <td class="text-right">
                                    <a href="" title="edit" wire:click.prevent="editTemplate({{$account_template->id}})">
                                        <i class="fas fa-edit text-success mx-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{$account_templates->links()}}
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add Template</h3>
                <div class="card-tools">
                    <a href="" class="btn btn-secondary btn-sm" wire:click.prevent="toggleTemplateForm">
                        <i class="fa fa-arrow-left fa-sm"></i>
                        Back
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="row">

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="upload_template_id">Upload Template</label>
                            <select class="form-control{{$errors->has('upload_template_id') ? ' is-invalid' : ''}}" wire:model="upload_template_id">   
                                <option value="">-select upload template-</option>
                                @foreach($templates as $template)
                                    <option value="{{$template->id}}">{{$template->name}}</option>
                                @endforeach
                            </select>
                            <small class="text-danger">{{$errors->first('upload_template_id')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select class="form-control{{$errors->has('type') ? ' is-invalid' : ''}}" wire:model="type">
                                <option value="">-select type-</option>
                                <option value="number">Column Number</option>
                                <option value="name">Column Name</option>
                            </select>
                            <small class="text-danger">{{$errors->first('type')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="start_row">Start Row</label>
                            <input type="number" class="form-control{{$errors->has('start_row') ? ' is-invalid' : ''}}" wire:model="start_row">
                            <small class="text-danger">{{$errors->first('start_row')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="breakpoint">Breakpoint (if multiple one in file)</label>
                            <input type="text" class="form-control{{$errors->has('breakpoint') ? ' is-invalid' : ''}}" wire:model="breakpoint">
                            <small class="text-danger">{{$errors->first('breakpoint')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="breakpoint_col">Breakpoint Column</label>
                            <input type="text" class="form-control{{$errors->has('breakpoint_col') ? ' is-invalid' : ''}}" wire:model="breakpoint_col">
                            <small class="text-danger">{{$errors->first('breakpoint_col')}}</small>
                        </div>
                    </div>
                </div>

                @if(!empty($template_fields))
                    <hr>
                    <strong>FIELDS</strong>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Field</th>
                                    <th>Column Name</th>
                                    <th>Column Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($template_fields as $field)
                                    <tr>
                                        <td>{{$field->number}}</td>
                                        <td>{{$field->column_name}}</td>
                                        <td class="p-0">
                                            <input type="text" class="form-control border-0" wire:model="account_template_fields.{{$field->id}}.name">
                                        </td>
                                        <td class="p-0">
                                            <input type="text" class="form-control border-0" wire:model="account_template_fields.{{$field->id}}.number">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
            <div class="card-footer text-right">
                <a href="" class="btn btn-sm btn-primary" wire:click.prevent="saveTemplate">
                    <i class="fa fa-save"></i>
                    SAVE
                </a>
            </div>
        </div>
    @endif
</div>
