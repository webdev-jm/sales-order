@extends('adminlte::page')

@section('title')
    Reports - Sales Orders
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Reports / Sales Orders</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-default"><i class="fa fa-calendar-alt mr-2"></i>MCP</a>
        <a href="{{route('report.sales-order', ['year' => date('Y'), 'month' => date('m')])}}" class="btn btn-primary"><i class="fa fa-chart-line mr-2"></i>Sales Order</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Select Month</h3>
    </div>
    <div class="card-body">
        <livewire:reports.sales-orders.months/>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <livewire:reports.sales-orders.salesman/>
    </div>

    <div class="col-lg-6">
        <livewire:reports.sales-orders.account/>
    </div>

    <div class="col-lg-12">
        <livewire:reports.sales-orders.top-sku/>
    </div>

    <div class="col-lg-6">
        <livewire:reports.sales-orders.brand/>
    </div>

    <div class="col-lg-6">
        <livewire:reports.sales-orders.category/>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<!-- optional -->
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
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
