@extends('adminlte::page')

@section('title')
    Sales Orders - Add
@endsection

@section('css')
<style>
    td {
        word-wrap: break-word;
        white-space: inherit !important;
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
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Sales Order Form</h3>
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
    <div class="card-body">

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $num = 0;
                            @endphp
                            @foreach($products as $product)
                            @php
                                $num++;
                            @endphp
                            <tr>
                                <td class="align-middle">[{{$product->stock_code}}] {{$product->description}} {{$product->size}}</td>
                                <td class="align-middle text-center">{{$product->uom}}</td>
                                <td class="p-0 align-middle">
                                    <input type="number" class="form-control border-0">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer">
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