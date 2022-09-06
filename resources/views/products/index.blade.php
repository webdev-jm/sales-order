@extends('adminlte::page')

@section('title')
    Products
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Products</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('product create')
        <a href="{{route('product.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Product</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">List of Products</h3>
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
                    <th>Stock Code</th>
                    <th>Description</th>
                    <th>Size</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{$product->stock_code}}</td>
                    <td>{{$product->description}}</td>
                    <td>{{$product->size}}</td>
                    <td class="text-right">
                        @can('product edit')
                            <a href="{{route('product.edit', $product->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('product delete')
                            <a href="#" title="delete"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$products->links()}}
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