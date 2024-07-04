@extends('adminlte::page')

@section('title')
    Purchase Orders
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Purchase Orders</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('purchase-order.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            Back
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">PO: 123456778</h3>
    </div>
    <div class="card-body">

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