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
<div class="row">
    <div class="col-lg-8">
        <livewire:sales-order.sales-order-products/>
    </div>
    <div class="col-lg-4">
        
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