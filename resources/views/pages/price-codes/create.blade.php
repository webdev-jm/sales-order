@extends('adminlte::page')

@section('title')
    Price Codes - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Price Codes / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('price-code.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Price Code</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['price-code.store'], 'id' => 'add_price_code']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('company_id', 'Company') !!}
                    {!! Form::select('company_id', $companies, null, ['class' => 'form-control select'.($errors->has('company_id') ? ' is-invalid' : ''), 'form' => 'add_price_code']) !!}
                    <p class="text-danger">{{$errors->first('company_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('product_id', 'Product') !!}
                    {!! Form::select('product_id', $products, null, ['class' => 'form-control select'.($errors->has('product_id') ? ' is-invalid' : ''), 'form' => 'add_price_code']) !!}
                    <p class="text-danger">{{$errors->first('product_id')}}</p>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('code', 'Code') !!}
                    {!! Form::text('code', '', ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'add_price_code']) !!}
                    <p class="text-danger">{{$errors->first('code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('selling_price', 'Selling Price') !!}
                    {!! Form::text('selling_price', '', ['class' => 'form-control'.($errors->has('selling_price') ? ' is-invalid' : ''), 'form' => 'add_price_code']) !!}
                    <p class="text-danger">{{$errors->first('selling_price')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('price_basis', 'Price Basis') !!}
                    {!! Form::select('price_basis', $price_basis, null, ['class' => 'form-control'.($errors->has('price_basis') ? ' is-invalid' : ''), 'form' => 'add_price_code']) !!}
                    <p class="text-danger">{{$errors->first('price_basis')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Price Code', ['class' => 'btn btn-primary', 'form' => 'add_price_code']) !!}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('.select').select2();
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection