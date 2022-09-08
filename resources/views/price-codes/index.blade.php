@extends('adminlte::page')

@section('title')
    Price Codes
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Price Codes</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('price code create')
        <a href="{{route('price-code.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Price Code</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Price Codes</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Product</th>
                    <th>Code</th>
                    <th>Selling Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($price_codes as $price_code)
                <tr>
                    <td>{{$price_code->company->name}}</td>
                    <td>{{$price_code->product->stock_code}}</td>
                    <td>{{$price_code->code}}</td>
                    <td>{{$price_code->selling_price}}</td>
                    <td class="text-right">
                        @can('price code edit')
                            <a href="{{route('price-code.edit', $price_code->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('price code delete')
                            <a href="#" title="delete"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$price_codes->links()}}
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