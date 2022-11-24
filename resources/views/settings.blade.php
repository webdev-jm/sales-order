@extends('adminlte::page')

@section('title')
    System Settings
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>System Settings</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="#" class="btn btn-warning" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload PO Numbers</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['setting.update', $setting->id], 'id' => 'update_settings']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Settings</h3>
    </div>
    <div class="card-body">

        <div class="row">

            <div class="col-lg-2">
                <div class="form-group">
                    {!! Form::label('data_per_page', 'Data per page') !!}
                    {!! Form::number('data_per_page', $setting->data_per_page, ['class' => 'form-control'.($errors->has('data_per_page') ? ' is-invalid' : ''), 'form' => 'update_settings']) !!}
                    <p class="text-danger">{{$errors->first('data_per_page')}}</p>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group">
                    {!! Form::label('sales_order_limit', 'Sales Order Limit') !!}
                    {!! Form::number('sales_order_limit', $setting->sales_order_limit, ['class' => 'form-control'.($errors->has('sales_order_limit') ? ' is-invalid' : ''), 'form' => 'update_settings']) !!}
                    <p class="text-danger">{{$errors->first('sales_order_limit')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('mcp_deadline', 'Activity Plan Deadline (monthly)') !!}
                    {!! Form::number('mcp_deadline', $setting->mcp_deadline, ['class' => 'form-control'.($errors->has('mcp_deadline') ? ' is-invalid' : ''), 'form' => 'update_settings', 'max' => 31, 'min' => 1]) !!}
                    <p class="text-danger">{{$errors->first('mcp_deadline')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Update Settings', ['class' => 'btn btn-primary', 'form' => 'update_settings']) !!}
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Users</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['po-number.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
                {!! Form::close() !!}

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="custom-file">
                                {!! Form::file('upload_file', ['class' => 'custom-file-input'.($errors->has('upload_file') ? ' is-invalid' : ''), 'form' => 'upload_form']) !!}
                                {!! Form::label('upload_file', 'Upload File', ['class' => 'custom-file-label']) !!}
                            </div>
                            <p class="text-danger">{{$errors->first('upload_file')}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Upload', ['class' => 'btn btn-primary', 'form' => 'upload_form']) !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loadingModal" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="loadingModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="exampleModalLongTitle">UPLOADING......</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <i class="fa fa-spinner fa-spin fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('#btn-upload').on('click', function(e){
            e.preventDefault();
            $('#modal-upload').modal('show');
        });

        $('#upload_form').on('submit', function() {
            $('#modal-upload').modal('hide');
            $('#loadingModal').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection