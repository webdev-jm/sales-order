@extends('adminlte::page')

@section('title')
    Reports
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
    <h1>Reports</h1>
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
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
