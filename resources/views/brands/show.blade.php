@extends('adminlte::page')

@section('title')
    Brand
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Brand</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('brand.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-3">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Brand Details</h3>
            </div>
            <div class="card-body p-1">
                <ul class="list-group">
                    <li class="list-group-item">
                        BRAND NAME:
                        <span class="float-right">{{$brand->brand}}</span>
                    </li>
                </ul>
            </div>
        </div>

        <livewire:brand.approver :brand="$brand"/>
    </div>

    <div class="col-lg-9">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Products</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>STOCK CODE</th>
                            <th>DESCRIPTION</th>
                            <th>SIZE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brand->products as $product)
                            <tr>
                                <td>{{$product->stock_code}}</td>
                                <td>{{$product->description}}</td>
                                <td>{{$product->size}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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