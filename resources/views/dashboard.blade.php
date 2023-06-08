@extends('adminlte::page')

@section('title')
    Dashboard
@endsection

@section('css')
<style>
    .small-box .inner {
        border: solid 3px rgb(98, 98, 98);
    }
    .small-box:hover .inner {
        border: solid 3px rgb(76, 145, 255);
        cursor: pointer;
    }
    .h-90 {
        height: 90% !important;
    }

    #container {
        height: 700px;
    }
</style>
@endsection

@section('content_header')
    <h1>Dashboard</h1>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['dashboard'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="row">
    {{-- REMINDERS --}}
    <div class="col-lg-6">
        <livewire:dashboard.reminder-list />
    </div>
</div>

@can('system logs')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Map Chart Test</h3>
    </div>
    <div class="card-body">
        <figure class="highcharts-figure">
            <div id="container"></div>
            <div class="highcharts-description">
                This demo uses a <code>mappoint</code> series with mapmarker symbols.
            </div>
        </figure>
    </div>
</div>
@endcan

@endsection

@section('js')
<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
<script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
@can('system logs')
    <script>
        
        (async () => {

            const topology = await fetch(
                'https://code.highcharts.com/mapdata/countries/ph/ph-all.topo.json'
            ).then(response => response.json());

            // Create the chart
            Highcharts.mapChart('container', {
                chart: {
                    map: topology,
                    margin: 1
                },

                title: {
                    text: 'Categories of European capitals',
                    floating: true,
                    style: {
                        textOutline: '5px contrast'
                    }
                },

                subtitle: {
                    text: 'Map markers in Highcharts',
                    floating: true,
                    y: 36,
                    style: {
                        textOutline: '5px contrast'
                    }
                },

                mapNavigation: {
                    enabled: true,
                    buttonOptions: {
                        alignTo: 'spacingBox',
                        verticalAlign: 'bottom'
                    }
                },

                mapView: {
                    padding: [0, 0, 85, 0]
                },

                legend: {
                    floating: true,
                    backgroundColor: '#ffffffcc'
                },

                plotOptions: {
                    mappoint: {
                        keys: ['id', 'lat', 'lon', 'name', 'y'],
                        marker: {
                            lineWidth: 1,
                            lineColor: '#000',
                            symbol: 'mapmarker',
                            radius: 8
                        },
                        dataLabels: {
                            enabled: false
                        }
                    }
                },

                tooltip: {
                    headerFormat: '<span style="color:{point.color}">\u25CF</span> {point.key}<br/>',
                    pointFormat: '{series.name}'
                },

                series: @php echo json_encode($chart_data); @endphp,

            });

        })();
    </script>
@endcan
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
