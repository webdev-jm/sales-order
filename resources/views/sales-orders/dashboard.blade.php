@extends('adminlte::page')

@section('title')
    Sales Order Dashboard
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Order Dashboard</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-order.list')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{{-- month select --}}
<livewire:sales-order.dashboard.months/>

{{-- info box --}}
<livewire:sales-order.dashboard.info-box/>

{{-- sales order list --}}
<livewire:sales-order.dashboard.sales-orders/>

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