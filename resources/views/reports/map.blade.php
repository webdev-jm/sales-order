@extends('adminlte::page')

@section('title')
    MCP MAP
@endsection

@section('css')
<style>
    #container {
        height: 700px;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>MCP MAP Reports</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-default"><i class="fa fa-calendar-alt mr-2"></i>MCP</a>
        <a href="{{route('report.map')}}" class="btn btn-primary"><i class="fa fa-calendar-alt mr-2"></i>MCP MAP</a>
        <a href="{{route('report.mcp-dashboard')}}" class="btn btn-default"><i class="fa fa-list mr-2"></i>MCP Dashboard</a>
        <a href="{{route('report.sales-order', ['year' => date('Y'), 'month' => date('m')])}}" class="btn btn-default"><i class="fa fa-chart-pie mr-2"></i>Sales Order</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">MCP MAP Reports</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'GET', 'route' => ['dashboard'], 'id' => 'filter_form']) !!}
        {!! Form::close() !!}

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">FILTER</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="date_from">Date From</label>
                                    <input type="date" class="form-control" name="date_from" form="filter_form" value="{{$date_from}}">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="date_to">Date To</label>
                                    <input type="date" class="form-control" name="date_to" form="filter_form" value="{{$date_to}}">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="user_id">User</label>
                                    <select name="user_id" form="filter_form" class="form-control" id="user_id">
                                        <option value="">-select user-</option>
                                        @foreach($users as $user)
                                            <option value="{{$user->id}}" {{$user_id == $user->id ? 'selected' : ''}}>{{$user->fullName()}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        {!! Form::submit('Filter', ['class' => 'btn btn-primary btn-sm', 'form' => 'filter_form']) !!}
                    </div>
                </div>
            </div>
        </div>

        <figure class="highcharts-figure">
            <div id="container"></div>
        </figure>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
<script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
<script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/maps/modules/tiledwebmap.js"></script>
<script>

    (async () => {

        const topology = await fetch(
            'https://code.highcharts.com/mapdata/countries/ph/ph-all.topo.json'
        ).then(response => response.json());

        Highcharts.mapChart('container', {
            chart: {
                margin: 0
            },

            title: {
                text: ''
            },

            subtitle: {
                text: ''
            },

            navigation: {
                buttonOptions: {
                    align: 'left',
                    theme: {
                        stroke: '#e6e6e6'
                    }
                }
            },

            mapNavigation: {
                enabled: true,
                buttonOptions: {
                    alignTo: 'spacingBox'
                }
            },

            mapView: {
                center: [121.0071423, 14.5635197],
                zoom: 12
            },

            legend: {
                enabled: true,
                title: {
                    text: 'Branches'
                },
                align: 'left',
                symbolWidth: 20,
                symbolHeight: 20,
                itemStyle: {
                    textOutline: '1 1 1px rgba(255,255,255)'
                },
                backgroundColor: `color-mix(
                    in srgb,
                    var(--highcharts-background-color, white),
                    transparent 15%
                )`,
                float: true,
                borderRadius: 2,
                itemMarginBottom: 5
            },

            plotOptions: {
                mappoint: {
                    dataLabels: {
                        enabled: false
                    }
                }
            },

            series: [{
                type: 'tiledwebmap',
                name: 'Basemap Tiles',
                provider: {
                    type: 'OpenStreetMap'
                },
                showInLegend: false
            }, {
                type: 'mapbubble',
                name: 'Branch Visits',
                dataLabels: {
                    enabled: true,
                    format: '{point.user} <br> {point.time_in}',
                    style: {
                        color: 'var(--highcharts-neutral-color-100, black)'
                    }
                },
                tooltip: {
                    pointFormat: '{point.name} <br>'+
                        '<b>BRANCH: </b>{point.branch}<br>' +
                        '<b>USER: </b>{point.user} <br>' +
                        '<b>TIME IN: </b>{point.time_in} <br>' +
                        '<b>TIME OUT: </b> {point.time_out} <br>' +
                        '<b>ACCURACY: </b>{point.accuracy}'
                },
                data: @php echo json_encode($chart_data); @endphp,
                maxSize: '12%',
            } ,{
                type: 'mappoint',
                name: 'Branches',
                marker: {
                    symbol: 'url(https://www.highcharts.com/samples/graphics/building.svg)',
                    width: 24,
                    height: 24
                },
                data: @php echo json_encode($branch_data); @endphp,
                tooltip: {
                    pointFormat: '<b>BRANCH: </b>{point.name} <br>'+
                    '<b>USER: </b>{point.user} <br>' +
                    '<b>SCHEDULE: </b>{point.schedule_date} <br>' +
                    '<b>OBJECTIVE: </b>{point.objective} <br>' +
                    '<b>SOURCE: </b>{point.source}'
                },
            },]
        });


    })();
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
