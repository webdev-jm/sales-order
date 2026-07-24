@extends('adminlte::page')

@section('title')
    MAPS
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
        <h1>MAPS</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-default"><i class="fa fa-calendar-alt mr-2"></i>MCP</a>
        <a href="{{route('report.map')}}" class="btn btn-default"><i class="fa fa-map-marked-alt mr-2"></i>MCP MAP</a>
        <a href="{{route('report.locations')}}" class="btn btn-primary"><i class="fa fa-route mr-2"></i>MAPS</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">MAPS</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'GET', 'route' => ['report.locations'], 'id' => 'filter_form']) !!}
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

        @if((!empty($date_from) || !empty($date_to) || !empty($user_id)) && count($route_data) < 1)
            <div class="alert alert-info mb-0">No recorded location trails for the selected filters.</div>
        @else
            <figure class="highcharts-figure">
                <div id="container"></div>
            </figure>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('vendor/highcharts/highmaps.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/offline-exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/tiledwebmap.js') }}"></script>
<script>

    (async () => {

        const routeData = @php echo json_encode($route_data); @endphp;
        const pointData = @php echo json_encode($point_data); @endphp;
        const startData = @php echo json_encode($start_data); @endphp;
        const endData = @php echo json_encode($end_data); @endphp;

        if (!routeData.length) {
            return;
        }

        const palette = ['#e6194B', '#3cb44b', '#4363d8', '#f58231', '#911eb4',
            '#42d4f4', '#f032e6', '#bfef45', '#fabed4', '#469990'];

        const routeSeries = routeData.map((route, index) => ({
            type: 'mapline',
            name: route.name,
            color: palette[index % palette.length],
            lineWidth: 3,
            data: [{
                geometry: route.geometry,
                user: route.user,
                branch: route.branch,
                time_in: route.time_in,
                time_out: route.time_out,
                points: route.points
            }],
            tooltip: {
                pointFormat: '<b>USER: </b>{point.user}<br>' +
                    '<b>BRANCH: </b>{point.branch}<br>' +
                    '<b>TIME IN: </b>{point.time_in}<br>' +
                    '<b>TIME OUT: </b>{point.time_out}<br>' +
                    '<b>POINTS: </b>{point.points}'
            }
        }));

        // Center the view on the first recorded point.
        const center = pointData.length
            ? [pointData[0].lon, pointData[0].lat]
            : [121.0071423, 14.5635197];

        Highcharts.mapChart('container', {
            chart: {
                margin: 0
            },

            title: {
                text: ''
            },

            mapNavigation: {
                enabled: true,
                enableMouseWheelZoom: true,
                enableDoubleClickZoom: true,
                enableTouchZoom: true,
                buttonOptions: {
                    alignTo: 'spacingBox'
                }
            },

            mapView: {
                center: center,
                zoom: 14
            },

            legend: {
                enabled: true,
                title: {
                    text: 'Routes'
                },
                align: 'left',
                float: true,
                borderRadius: 2,
                itemMarginBottom: 5,
                backgroundColor: `color-mix(
                    in srgb,
                    var(--highcharts-background-color, white),
                    transparent 15%
                )`
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
            }].concat(routeSeries).concat([{
                type: 'mappoint',
                name: 'Recorded Points',
                color: '#00000088',
                marker: {
                    radius: 3
                },
                data: pointData,
                showInLegend: false,
                tooltip: {
                    pointFormat: '<b>USER: </b>{point.user}<br>' +
                        '<b>BRANCH: </b>{point.branch}<br>' +
                        '<b>SEQUENCE: </b>{point.sequence}<br>' +
                        '<b>RECORDED: </b>{point.recorded_at}<br>' +
                        '<b>ACCURACY: </b>{point.accuracy}'
                }
            }, {
                type: 'mappoint',
                name: 'Sign In',
                color: '#2e7d32',
                marker: {
                    symbol: 'triangle',
                    radius: 7,
                    lineColor: '#ffffff',
                    lineWidth: 1
                },
                dataLabels: {
                    enabled: true,
                    format: 'IN',
                    style: { color: '#2e7d32', textOutline: '1px #ffffff' }
                },
                data: startData,
                tooltip: {
                    pointFormat: '<b>SIGN IN</b><br>' +
                        '<b>USER: </b>{point.user}<br>' +
                        '<b>BRANCH: </b>{point.branch}<br>' +
                        '<b>TIME IN: </b>{point.time_in}'
                }
            }, {
                type: 'mappoint',
                name: 'Sign Out',
                color: '#c62828',
                marker: {
                    symbol: 'square',
                    radius: 7,
                    lineColor: '#ffffff',
                    lineWidth: 1
                },
                dataLabels: {
                    enabled: true,
                    format: 'OUT',
                    style: { color: '#c62828', textOutline: '1px #ffffff' }
                },
                data: endData,
                tooltip: {
                    pointFormat: '<b>SIGN OUT</b><br>' +
                        '<b>USER: </b>{point.user}<br>' +
                        '<b>BRANCH: </b>{point.branch}<br>' +
                        '<b>TIME OUT: </b>{point.time_out}'
                }
            }])
        });

    })();
</script>
@endsection

@section('footer')
@endsection
