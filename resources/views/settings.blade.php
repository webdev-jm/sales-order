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

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Update Settings', ['class' => 'btn btn-primary', 'form' => 'update_settings']) !!}
    </div>
</div>

@endsection

@section('js')
<script>

</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection