<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Template</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        {!! Form::label('name', 'Template Name') !!}
                        {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'wire:model' => 'name']) !!}
                        <small class="text-danger">{{$errors->first('name')}}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        {!! Form::label('breakpoint', 'Breakpoint') !!}
                        {!! Form::text('breakpoint', '', ['class' => 'form-control'.($errors->has('breakpoint') ? ' is-invalid' : ''), 'wire:model' => 'breakpoint']) !!}
                        <small class="text-danger">{{$errors->first('breakpoint')}}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        {!! Form::label('breakpoint_col', 'Breakpoint Column') !!}
                        {!! Form::number('breakpoint_col', '', ['class' => 'form-control'.($errors->has('breakpoint_col') ? ' is-invalid' : ''), 'wire:model' => 'breakpoint_col']) !!}
                        <small class="text-danger">{{$errors->first('breakpoint_col')}}</small>
                    </div>
                </div>
            </div>

            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        Template Fields
                        @if($errors->has('lines'))
                            <span class="badge badge-danger">REQUIRED</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th class="p-0 text-center align-middle">#</th>
                                <th>Column Name</th>
                                <th>Column Number</th>
                                <th class="p-0 text-center align-middle"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $num = 0;
                            @endphp
                            @foreach($lines as $key => $line)
                                @php
                                    $num++;
                                @endphp
                                <tr>
                                    <td class="p-0 text-center align-middle">
                                        {{$num}}
                                    </td>
                                    <td class="p-0">
                                        <input type="text" class="form-control border-0" wire:model="lines.{{$key}}.column_name">
                                    </td>
                                    <td class="p-0">
                                        <input type="number" class="form-control border-0" wire:model="lines.{{$key}}.column_number">
                                    </td>
                                    <td class="p-1 text-center align-middle">
                                        <button class="btn btn-danger btn-sm btn-block" wire:click.prevent="removeLine({{$key}})">
                                            <i class="fa fa-trash-alt fa-xs"></i>
                                            REMOVE
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right p-1">
                    <button class="btn btn-info btn-sm" wire:click.prevent="addLine">
                        <i class="fa fa-plus fa-sm mr-1"></i>
                        Add Line
                    </button>
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary" wire:click.prevent="saveTemplate">
                <i class="fa fa-save fa-sm mr-1"></i>
                Add Template
            </button>
        </div>
    </div>

    
</div>
