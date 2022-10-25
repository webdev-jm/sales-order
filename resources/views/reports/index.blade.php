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
        <a href="{{route('report.sales-order')}}" class="btn btn-default"><i class="fa fa-chart-pie mr-2"></i>Sales Order</a>
    </div>
</div>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-3">
            
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{$schedules_count}}</h3>
    
                    <p>Schedules</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

        </div>

        <div class="col-lg-3">
            
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{$visited_count->total}}</h3>
    
                    <p>Visited</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>

        </div>

        <div class="col-lg-3">
            
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="text-white">{{$reschedule_count}}</h3>
    
                    <p class="text-white">Reschedule Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
            </div>
            
        </div>

        <div class="col-lg-3">
            
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{$delete_count}}</h3>
    
                    <p>Delete Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
            </div>
            
        </div>
    </div>

    <hr>

    <livewire:reports.mcp.report/>

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
