@extends('adminlte::page')

@section('title')
    Sales Orders - Edit
@endsection

@section('css')
<style>
    td {
        word-wrap: break-word;
        white-space: inherit !important;
    }

    .fast-spin {
        -webkit-animation: fa-spin 500ms infinite linear;
        animation: fa-spin 500ms infinite linear;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1 class="align-middle">Sales Orders / Edit <span class="badge {{$sales_order->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$sales_order->status}}</span></h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-order.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['sales-order.update', $sales_order->id], 'id' => 'update_sales_order']) !!}
{!! Form::close() !!}

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-1 align-middle">CONTROL NO: {{$sales_order->control_number}}</h3>
                {!! Form::hidden('control_number', $sales_order->control_number, ['form' => 'add_sales_order']) !!}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div class="card-tools">
                    {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-submit', 'form' => 'add_sales_order']) !!}
                    {!! Form::submit('Finalize', ['class' => 'btn btn-primary btn-submit', 'form' => 'add_sales_order']) !!}
                    {!! Form::hidden('status', 'draft', ['form' => 'add_sales_order', 'id' => 'status']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title">ORDER HEADER</h3>
            </div>
            <div class="card-body">

                <div class="row">

                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('po_number', 'PO Number') !!}
                            {!! Form::text('po_number', $sales_order->po_number, ['class' => 'form-control form-control-sm'.($errors->has('po_number') ? ' is-invalid' : ''), 'form' => 'add_sales_order']) !!}
                            <p class="text-danger">{{$errors->first('po_number')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('order_date', 'Order Date') !!}
                            {!! Form::date('order_date', $sales_order->order_date, ['class' => 'form-control form-control-sm bg-white'.($errors->has('order_date') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('order_date')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('ship_date', 'Ship Date') !!}
                            {!! Form::date('ship_date', $sales_order->ship_date, ['class' => 'form-control form-control-sm'.($errors->has('ship_date') ? ' is-invalid' : ''), 'form' => 'add_sales_order']) !!}
                            <p class="text-danger">{{$errors->first('ship_date')}}</p>
                        </div>
                    </div>

                </div>

                <label class="mb-0">SHIP TO ADDRESS</label>
                <hr class="mt-0">

                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_name', 'Ship To Name') !!}
                            {!! Form::text('ship_to_name', $sales_order->ship_to_name, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_name') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_name')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_address1', 'Building') !!}
                            {!! Form::text('ship_to_address1', $sales_order->ship_to_building, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_address1') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_address1')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_address2', 'Street') !!}
                            {!! Form::text('ship_to_address2', $sales_order->ship_to_street, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_address2') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_address2')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_address3', 'City') !!}
                            {!! Form::text('ship_to_address3', $sales_order->ship_to_city, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_address3') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_address3')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('postal_code', 'Postal Code') !!}
                            {!! Form::text('postal_code', $sales_order->ship_to_postal, ['class' => 'form-control form-control-sm bg-white'.($errors->has('postal_code') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('postal_code')}}</p>
                        </div>
                    </div>

                </div>
                
            </div>
        </div>
        <livewire:sales-order.sales-order-products />
    </div>
    <div class="col-lg-5">
        <livewire:sales-order.sales-order-total />
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '.btn-submit', function(e) {
            e.preventDefault();
            var status = $(this).val();
            var status_val = 'draft';
            if(status == 'Finalize') {
                status_val = 'final';
            } else {
                status_val = 'draft';
            }

            $('#status').val(status_val);
            $('#'+$(this).attr('form')).submit();
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection