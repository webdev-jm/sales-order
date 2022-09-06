@extends('adminlte::page')

@section('title')
    Products - Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Products / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('product.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Product</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['product.update', $product->id], 'id' => 'update_product']) !!}
        {!! Form::close() !!}

        <div class="row">
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('stock_code', 'Stock Code') !!}
                    {!! Form::text('stock_code', $product->stock_code, ['class' => 'form-control'.($errors->has('stock_code') ? ' is-invalid' : ''), 'form' => 'update_product']) !!}
                    <p class="text-danger">{{$errors->first('stock_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::text('description', $product->description, ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'form' => 'update_product']) !!}
                    <p class="text-danger">{{$errors->first('description')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('size', 'Size') !!}
                    {!! Form::text('size', $product->size, ['class' => 'form-control'.($errors->has('size') ? ' is-invalid' : ''), 'form' => 'update_product']) !!}
                    <p class="text-danger">{{$errors->first('size')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('category', 'Category') !!}
                    {!! Form::text('category', $product->category, ['class' => 'form-control'.($errors->has('size') ? ' is-invalid' : ''), 'form' => 'update_product']) !!}
                    <p class="text-danger">{{$errors->first('category')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('brand', 'Brand') !!}
                    {!! Form::text('brand', $product->brand, ['class' => 'form-control'.($errors->has('brand') ? ' is-invalid' : ''), 'form' => 'update_product']) !!}
                    <p class="text-danger">{{$errors->first('brand')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('alternative_code', 'Alternative Code') !!}
                    {!! Form::text('alternative_code', $product->alternative_code, ['class' => 'form-control'.($errors->has('alternative_code') ? ' is-invalid' : ''), 'form' => 'update_product']) !!}
                    <p class="text-danger">{{$errors->first('alternative_code')}}</p>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-lg-6">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr class="text-center">
                            <th></th>
                            <th>UOM</th>
                            <th>PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center p-0 align-middle font-weight-bold">1.</td>
                            <td class="p-0">
                                {!! Form::text('stock_uom1', $product->stock_uom1, ['class' => 'form-control text-center'.($errors->has('stock_uom1') ? ' is-invalid' : '  border-0'), 'form' => 'update_product']) !!}
                            </td>
                            <td class="p-0">
                                {!! Form::text('uom_price1', $product->uom_price1, ['class' => 'form-control text-center'.($errors->has('uom_price1') ? ' is-invalid' : '  border-0'), 'form' => 'update_product']) !!}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center p-0 align-middle font-weight-bold">2.</td>
                            <td class="p-0">
                                {!! Form::text('stock_uom2', $product->stock_uom2, ['class' => 'form-control text-center'.($errors->has('stock_uom2') ? ' is-invalid' : '  border-0'), 'form' => 'update_product']) !!}
                            </td>
                            <td class="p-0">
                                {!! Form::text('uom_price2', $product->uom_price2, ['class' => 'form-control text-center'.($errors->has('uom_price2') ? ' is-invalid' : '  border-0'), 'form' => 'update_product']) !!}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center p-0 align-middle font-weight-bold">3.</td>
                            <td class="p-0">
                                {!! Form::text('stock_uom3', $product->stock_uom3, ['class' => 'form-control text-center'.($errors->has('stock_uom3') ? ' is-invalid' : '  border-0'), 'form' => 'update_product']) !!}
                            </td>
                            <td class="p-0">
                                {!! Form::text('uom_price3', $product->uom_price3, ['class' => 'form-control text-center'.($errors->has('uom_price3') ? ' is-invalid' : '  border-0'), 'form' => 'update_product']) !!}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Product', ['class' => 'btn btn-primary', 'form' => 'update_product']) !!}
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