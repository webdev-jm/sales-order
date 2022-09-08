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
                    {!! Form::label('core_group', 'Core Group') !!}
                    {!! Form::text('core_group', '', ['class' => 'form-control'.($errors->has('core_group') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('core_group')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('uom', 'Unit of Measurement') !!}
                    {!! Form::text('uom', '', ['class' => 'form-control'.($errors->has('uom') ? ' is-invalid' : ''), 'form' => 'add_product']) !!}
                    <p class="text-danger">{{$errors->first('uom')}}</p>
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