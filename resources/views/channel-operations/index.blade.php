@extends('adminlte::page')

@section('title')
    COE Report
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>COE Reports</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')

<livewire:coe.filter/>

<div class="row">
    {{-- MERCH UPDATE --}}
    <div class="col-lg-12">
        <livewire:coe.reports.merch-updates />
    </div>

    {{-- TRADE DISPLAY --}}
    <div class="col-lg-12">
        <livewire:coe.reports.trade-displays/>
    </div>

    {{-- TRADE MARKETING ACTIVITIES --}}
    <div class="col-lg-12">
        <livewire:coe.reports.trade-marketing-activities/>
    </div>

    {{-- DISPLAY RENTALS --}}
    <div class="col-lg-12">
        <livewire:coe.reports.display-rentals/>
    </div>

    {{-- EXTRA DISPLAYS --}}
    <div class="col-lg-12">
        <livewire:coe.reports.extra-displays/>
    </div>

    {{-- COMPETETIVE REPORTS --}}
    <div class="col-lg-12">
        <livewire:coe.reports.competetive-reports/>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
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