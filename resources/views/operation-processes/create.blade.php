@extends('adminlte::page')

@section('title')
    Operation Processes - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Operation Processes / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('operation-process.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-2"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Operation Process</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['operation-process.store'], 'id' => 'add_operation_process']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('company_id', 'Company') !!}
                    {!! Form::select('company_id', $companies, null, ['class' => 'form-control'.($errors->has('company_id') ? ' is-invalid' : ''), 'form' => 'add_operation_process']) !!}
                    <p class="text-danger">{{$errors->first('company_id')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('operation_process', 'Operation Process') !!}
                    {!! Form::text('operation_process', '', ['class' => 'form-control'.($errors->has('operation_process') ? ' is-invalid' : ''), 'form' => 'add_operation_process']) !!}
                    <p class="text-danger">{{$errors->first('operation_process')}}</p>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="table-responsive col-lg-6">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Remarks</th>
                            <th class="p-0"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-0">
                                {!! Form::text('description[]', '', ['class' => 'form-control border-0', 'form' => 'add_operation_process']) !!}
                            </td>
                            <td class="p-0">
                                {!! Form::text('remarks[]', '', ['class' => 'form-control border-0', 'form' => 'add_operation_process']) !!}
                            </td>
                            <td class="text-center align-middle p-0">
                                <a href="" class="btn btn-sm btn-white btn-block btn-remove-row">
                                    <i class="fa fa-trash-alt text-danger"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right border-0">
                                <button class="btn btn-sm btn-info btn-add-row">Add New Line</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Operation Process', ['class' => 'btn btn-primary', 'form' => 'add_operation_process']) !!}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-add-row', function(e) {
            e.preventDefault();
            var row = $(this).closest('table').find('tbody').find('tr:first').clone(true);
            row.find('input').val('');
            $(this).closest('table').find('tbody').append(row);
        });

        $('body').on('click', '.btn-remove-row', function(e) { 
            e.preventDefault();
            if($(this).closest('tbody').find('tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection