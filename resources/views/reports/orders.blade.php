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
        <a href="{{route('report.sales-order')}}" class="btn btn-primary"><i class="fa fa-chart-line mr-2"></i>Sales Order</a>
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
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Per salesman sales contribution</h3>
            </div>
            <div class="card-body">
                <figure class="highcharts-figure">
                    <div id="container"></div>
                    <p class="highcharts-description">
                        A variation of a 3D pie chart with an inner radius added.
                        These charts are often referred to as donut charts.
                    </p>
                </figure>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Per Account sales contribution</h3>
            </div>
            <div class="card-body">
                <figure class="highcharts-figure">
                    <div id="container-accounts"></div>
                    <p class="highcharts-description">
                        A variation of a 3D pie chart with an inner radius added.
                        These charts are often referred to as donut charts.
                    </p>
                </figure>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top SKUs</h3>
            </div>
            <div class="card-body">
                
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    $(function() {
        Highcharts.chart('container', {
            chart: {
                type: 'pie',
                options3d: {
                    enabled: true,
                    alpha: 45
                }
            },
            title: {
                text: 'Beijing 2022 gold medals by country'
            },
            subtitle: {
                text: '3D donut in Highcharts'
            },
            plotOptions: {
                pie: {
                    innerSize: 100,
                    depth: 45
                }
            },
            series: [{
                name: 'Medals',
                data: [
                    ['Norway', 16],
                    ['Germany', 12],
                    ['USA', 8],
                    ['Sweden', 8],
                    ['Netherlands', 8],
                    ['ROC', 6],
                    ['Austria', 7],
                    ['Canada', 4],
                    ['Japan', 3]

                ]
            }]
        });

        Highcharts.chart('container-accounts', {
            chart: {
                type: 'pie',
                options3d: {
                    enabled: true,
                    alpha: 45
                }
            },
            title: {
                text: 'Beijing 2022 gold medals by country'
            },
            subtitle: {
                text: '3D donut in Highcharts'
            },
            plotOptions: {
                pie: {
                    innerSize: 100,
                    depth: 45
                }
            },
            series: [{
                name: 'Medals',
                data: [
                    ['Norway', 16],
                    ['Germany', 12],
                    ['USA', 8],
                    ['Sweden', 8],
                    ['Netherlands', 8],
                    ['ROC', 6],
                    ['Austria', 7],
                    ['Canada', 4],
                    ['Japan', 3]

                ]
            }]
        });

    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
