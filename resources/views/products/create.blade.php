@extends('adminlte::page')

@section('title')
    Products - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Products / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('product.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Product</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['product.store'], 'id' => 'add_product']) !!}
        {!! Form::close() !!}

        <div class="row">
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('stock_code', 'Stock Code') !!}
                    {!! Form::text('stock_code', '', ['class' => 'form-control'.($errors->has('stock_code') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('stock_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::text('description', '', ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('description')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('size', 'Size') !!}
                    {!! Form::text('size', '', ['class' => 'form-control'.($errors->has('size') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('size')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('category', 'Category') !!}
                    {!! Form::text('category', '', ['class' => 'form-control'.($errors->has('category') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('category')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('product_class', 'Product Class') !!}
                    {!! Form::text('product_class', '', ['class' => 'form-control'.($errors->has('product_class') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('product_class')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('brand', 'Brand') !!}
                    {!! Form::text('brand', '', ['class' => 'form-control'.($errors->has('brand') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('brand')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('core_group', 'Core Group') !!}
                    {!! Form::text('core_group', '', ['class' => 'form-control'.($errors->has('core_group') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('core_group')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('stock_uom', 'Stock UOM') !!}
                    {!! Form::text('stock_uom', '', ['class' => 'form-control'.($errors->has('stock_uom') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('stock_uom')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    @php
                        $status_arr = [
                            'active' => 'active',
                            'F' => 'Hold',
                            'P' => 'Partial Hold'
                        ];
                    @endphp
                    {!! Form::label('status', 'Status') !!}
                    {!! Form::select('status', $status_arr, null, ['class' => 'form-control'.($errors->has('status') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('status')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('brand_id', 'Brand Tagging') !!}
                    {!! Form::select('brand_id', $brands_arr, null, ['class' => 'form-control'.($errors->has('brand_id') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('brand_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('warehouse', 'Warehouse') !!}
                    {!! Form::text('warehouse', '', ['class' => 'form-control'.($errors->has('warehouse') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('warehouse')}}</p>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        {!! Form::checkbox('special_product', 1, false, ['class' => 'custom-control-input', 'id' => 'special_product', 'form' => 'add_product']) !!}
                        {!! Form::label('special_product', 'Special Product', ['class' => 'custom-control-label']) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order UOM</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! Form::label('order_uom', 'UOM') !!}
                                    {!! Form::text('order_uom', '', ['class' => 'form-control'.($errors->has('order_uom') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                                    <p class="text-danger">{{$errors->first('order_uom')}}</p>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! Form::label('order_uom_conversion', 'Conversion') !!}
                                    {!! Form::number('order_uom_conversion', '', ['class' => 'form-control'.($errors->has('order_uom_conversion') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                                    <p class="text-danger">{{$errors->first('order_uom_conversion')}}</p>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label>Operation</label>
                                <div class="form-group">
                                    <div class="custom-control custom-radio">
                                        {!! Form::radio('order_uom_operator', 'M', true, ['class' => 'custom-control-input', 'id' => 'order_multiply', 'form' => 'add_product']) !!}
                                        {!! Form::label('order_multiply', 'Multiplication', ['class' => 'custom-control-label']) !!}
                                    </div>
                                    <div class="custom-control custom-radio">
                                        {!! Form::radio('order_uom_operator', 'D', false, ['class' => 'custom-control-input', 'id' => 'order_divide', 'form' => 'add_product']) !!}
                                        {!! Form::label('order_divide', 'Division', ['class' => 'custom-control-label']) !!}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Other UOM</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! Form::label('other_uom', 'UOM') !!}
                                    {!! Form::text('other_uom', '', ['class' => 'form-control'.($errors->has('other_uom') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                                    <p class="text-danger">{{$errors->first('other_uom')}}</p>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! Form::label('other_uom_conversion', 'Conversion') !!}
                                    {!! Form::number('other_uom_conversion', '', ['class' => 'form-control'.($errors->has('other_uom_conversion') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                                    <p class="text-danger">{{$errors->first('other_uom_conversion')}}</p>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label>Operation</label>
                                <div class="form-group">
                                    <div class="custom-control custom-radio">
                                        {!! Form::radio('other_uom_operator', 'M', true, ['class' => 'custom-control-input', 'id' => 'other_multiply', 'form' => 'add_product']) !!}
                                        {!! Form::label('other_multiply', 'Multiplication', ['class' => 'custom-control-label']) !!}
                                    </div>
                                    <div class="custom-control custom-radio">
                                        {!! Form::radio('other_uom_operator', 'D', false, ['class' => 'custom-control-input', 'id' => 'other_divide', 'form' => 'add_product']) !!}
                                        {!! Form::label('other_divide', 'Division', ['class' => 'custom-control-label']) !!}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Product', ['class' => 'btn btn-primary', 'form' => 'add_product']) !!}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection 