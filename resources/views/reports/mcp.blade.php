@extends('adminlte::page')

@section('title')
    Reports - MCP
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Reports / MCP Dashboard</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-default"><i class="fa fa-calendar-alt mr-2"></i>MCP</a>
        <a href="{{route('report.mcp-dashboard')}}" class="btn btn-primary"><i class="fa fa-list mr-2"></i>MCP Dashboard</a>
        <a href="{{route('report.sales-order', ['year' => date('Y'), 'month' => date('m')])}}" class="btn btn-default"><i class="fa fa-chart-pie mr-2"></i>Sales Order</a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">

    </div>
    <div class="col-lg-12">
        <livewire:reports.mcp.dashboard />
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
