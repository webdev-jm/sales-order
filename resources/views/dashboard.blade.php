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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">TESTING</h3>
    </div>
</div>

    <div class="row">
        <div class="col-lg-6">
            <h1>Dashboard</h1>
        </div>
        @can('sales dashboard')
        <div class="col-lg-6 text-right">
            <a href="{{route('sales-dashboard.index')}}" class="btn btn-primary">SALES DASHBOARD</a>
        </div>
        @endcan
    </div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['dashboard'], 'id' => 'search_form']) !!}
{!! Form::close() !!}


@can('system logs')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Map Chart Test</h3>
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
@endcan

<div class="row">
    {{-- REMINDERS --}}
    <div class="col-lg-6">
        <livewire:dashboard.reminder-list />
    </div>

</div>


@endsection

@section('js')
<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
<script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
<script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/maps/modules/tiledwebmap.js"></script>
@can('system logs')
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

                tooltip: {
                    pointFormat: '{point.name} <br>'+
                        '{point.branch}<br>' +
                        '{point.user} <br>' +
                        '{point.time_in} <br>' +
                        '{point.accuracy}'
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
                        format: '{point.time_in}',
                        style: {
                            color: 'var(--highcharts-neutral-color-100, black)'
                        }
                    },
                    accessibility: {
                        point: {
                            valueDescriptionFormat: '{point.name}, ' +
                                '{point.branch}. Population {point.time}. ' +
                                'Latitude {point.lat:.2f}, longitude {point.lon:.2f}.'
                        }
                    },
                    data: @php echo json_encode($chart_data); @endphp,
                    maxSize: '12%',
                } ,{
                    type: 'mappoint',
                    name: 'Branches',
                    marker: {
                        symbol: 'url(https://www.highcharts.com/samples/graphics/museum.svg)',
                        width: 24,
                        height: 24
                    },
                    data: @php echo json_encode($branch_data); @endphp

                },]
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
