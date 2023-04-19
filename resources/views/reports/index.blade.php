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
        <h1>Reports / Sales Orders</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-primary"><i class="fa fa-calendar-alt mr-2"></i>MCP</a>
        <a href="{{route('report.mcp-dashboard')}}" class="btn btn-default"><i class="fa fa-list mr-2"></i>MCP Dashboard</a>
        <a href="{{route('report.sales-order', ['year' => date('Y'), 'month' => date('m')])}}" class="btn btn-default"><i class="fa fa-chart-pie mr-2"></i>Sales Order</a>
    </div>
</div>
@endsection

@section('content')

    {{-- Header --}}
    <livewire:reports.mcp.header/>

    <hr>

    {{-- MCP Data --}}
    <livewire:reports.mcp.report/>

    <hr>

    {{-- MCP percentage --}}
    <livewire:reports.mcp.percentage />

@endsection

@section('js')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();

        $('body').on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
