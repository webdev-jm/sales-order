@extends('adminlte::page')

@section('title')
    Sales Orders - Add
@endsection

@section('css')
<style>
    /* td {
        word-wrap: break-word;
        white-space: inherit !important;
    } */

    .w150 {
        width: 200px;
    }

    .w100 {
        width: 75px;
    }

    .fast-spin {
        -webkit-animation: fa-spin 500ms infinite linear;
        animation: fa-spin 500ms infinite linear;
    }

    .bg-disabled {
        background-color: #e9ecef;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Orders / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-order.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['sales-order.store'], 'id' => 'add_sales_order']) !!}
<input type="hidden" name="control_number" form="add_sales_order" value="{{$control_number}}">
{!! Form::close() !!}

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-1 align-middle">CONTROL NO: {{$control_number}}</h3>
                <p class="text-danger m-0">{{$errors->first('control_number')}}</p>
                {{-- {!! Form::hidden('control_number', $control_number, ['form' => 'add_sales_order']) !!} --}}
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
                            {!! Form::text('po_number', session('po_number') ?? '', ['class' => 'form-control form-control-sm'.($errors->has('po_number') ? ' is-invalid' : ''), 'form' => 'add_sales_order']) !!}
                            <p class="text-danger">{{$errors->first('po_number')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('order_date', 'Order Date') !!}
                            {!! Form::date('order_date', session('order_date') ?? now(), ['class' => 'form-control form-control-sm bg-white'.($errors->has('order_date') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('order_date')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('ship_date', 'Ship Date') !!}
                            {!! Form::date('ship_date', session('ship_date') ?? now(), ['class' => 'form-control form-control-sm'.($errors->has('ship_date') ? ' is-invalid' : ''), 'form' => 'add_sales_order']) !!}
                            <p class="text-danger">{{$errors->first('ship_date')}}</p>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            {!! Form::label('shipping_instruction', 'Shipping Instruction') !!}
                            {!! Form::textarea('shipping_instruction', session('shipping_instruction') ?? '', ['class' => 'form-control form-control-sm'.($errors->has('shipping_instruction') ? ' is-invalid' : ''), 'rows' => 1, 'form' => 'add_sales_order']) !!}
                            <p class="text-danger">{{$errors->first('shipping_instruction')}}</p>
                        </div>
                    </div>

                </div>

                <label class="mb-0">SHIP TO ADDRESS</label>
                <a href="#" class="badge badge-info ml-2" id="btn-address-modal"><i class="fa fa-edit mr-1"></i>Change Address</a>

                {!! Form::hidden('shipping_address_id', session('shipping_address_id') ?? 'default', ['form' => 'add_sales_order', 'id' => 'shipping_address_id']) !!}
                <hr class="mt-0">

                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_name', 'Ship To Name') !!}
                            {!! Form::text('ship_to_name', session('ship_to_name') ?? $logged_account->account->account_name, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_name') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_name')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_address1', 'Building') !!}
                            {!! Form::text('ship_to_address1', session('ship_to_address1') ?? $logged_account->account->ship_to_address1, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_address1') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_address1')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_address2', 'Street') !!}
                            {!! Form::text('ship_to_address2', session('ship_to_address2') ?? $logged_account->account->ship_to_address2, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_address2') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_address2')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('ship_to_address3', 'City') !!}
                            {!! Form::text('ship_to_address3', session('ship_to_address3') ?? $logged_account->account->ship_to_address3, ['class' => 'form-control form-control-sm bg-white'.($errors->has('ship_to_address3') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
                            <p class="text-danger">{{$errors->first('ship_to_address3')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('postal_code', 'Postal Code') !!}
                            {!! Form::text('postal_code', session('postal_code') ?? $logged_account->account->postal_code, ['class' => 'form-control form-control-sm bg-white'.($errors->has('postal_code') ? ' is-invalid' : ''), 'form' => 'add_sales_order', 'readonly']) !!}
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

<div class="modal fade" id="address-modal">
    <div class="modal-dialog modal-lg">
        <livewire:sales-order.shipping-address-change :account_id="$logged_account->account_id"/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {

        $('#btn-address-modal').on('click', function(e) {
            e.preventDefault();
            $('#address-modal').modal('show');
        });

        $('body').on('click', '.btn-submit', function(e) {
            e.preventDefault();
            var status = $(this).val();
            var status_val = 'draft';
            if(status == 'Finalize') {
                if(confirm('Are you sure to finalize this sales order?')) {
                    status_val = 'finalized';
                    $('#status').val(status_val);
                    $('#'+$(this).attr('form')).submit();
                }
            } else {
                status_val = 'draft';
                $('#status').val(status_val);
                $('#'+$(this).attr('form')).submit();
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