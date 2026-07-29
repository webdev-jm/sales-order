@extends('adminlte::page')

@section('title')
    MAPS
@endsection

@section('css')
<style>
    .maps-page #container {
        height: 640px;
        border: 1px solid #dee2e6;
        border-radius: .35rem;
        overflow: hidden;
    }
    .maps-page .card-title {
        font-size: .95rem;
        font-weight: 600;
    }
    .maps-page label {
        font-size: .78rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: .25rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .maps-subtitle {
        font-size: .8rem;
        color: #6c757d;
    }
    .maps-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem 1rem;
        font-size: .78rem;
        color: #495057;
    }
    .maps-legend .key {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .maps-legend .swatch {
        width: .8rem;
        height: .8rem;
        display: inline-block;
        border: 1px solid #fff;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .15);
    }
    .maps-legend .swatch.dot { border-radius: 50%; }
    .maps-legend .swatch.tri {
        width: 0; height: 0; border: 0;
        border-left: .45rem solid transparent;
        border-right: .45rem solid transparent;
        border-bottom: .75rem solid #28a745;
        box-shadow: none;
    }
    .maps-legend .swatch.sq { border-radius: 2px; }
</style>
@endsection

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1 class="h4 mb-0 text-dark"><i class="fas fa-route text-primary mr-2"></i>MAPS</h1>
        <span class="maps-subtitle">GPS route traced from each sign-in to sign-out.</span>
    </div>
    <div class="col-sm-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-calendar-alt mr-1"></i>MCP</a>
        <a href="{{route('report.map')}}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-map-marked-alt mr-1"></i>MCP MAP</a>
        <a href="{{route('report.locations')}}" class="btn btn-sm btn-primary"><i class="fa fa-route mr-1"></i>MAPS</a>
    </div>
</div>
@endsection

@section('content')

<div class="maps-page">

    {{-- Filters --}}
    <div class="card card-outline card-primary">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i>Filter</h3>
        </div>
        <div class="card-body pb-2">
            {!! Form::open(['method' => 'GET', 'route' => ['report.locations'], 'id' => 'filter_form']) !!}
            {!! Form::close() !!}

            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" form="filter_form" value="{{$date_from}}">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" form="filter_form" value="{{$date_to}}">
                    </div>
                </div>
                <div class="col-md-4 col-8">
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select name="user_id" form="filter_form" class="form-control form-control-sm" id="user_id">
                            <option value="">All users</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}" {{$user_id == $user->id ? 'selected' : ''}}>{{$user->fullName()}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-4">
                    <div class="form-group">
                        <label class="d-none d-md-block">&nbsp;</label>
                        <div class="d-flex">
                            {!! Form::submit('Filter', ['class' => 'btn btn-sm btn-primary flex-fill', 'form' => 'filter_form']) !!}
                            <a href="{{route('report.locations')}}" class="btn btn-sm btn-outline-secondary ml-2">Reset</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Map --}}
    <div class="card">
        <div class="card-header py-2 d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-map-marked-alt mr-1"></i>Route Map</h3>
            <div class="maps-legend">
                <span class="key"><span class="swatch tri"></span>Sign In</span>
                <span class="key"><span class="swatch sq" style="background:#dc3545"></span>Sign Out</span>
                <span class="key"><span class="swatch dot" style="background:#6c757d"></span>Recorded point</span>
            </div>
        </div>
        <div class="card-body p-2">
            @php $has_filters = (!empty($date_from) || !empty($date_to) || !empty($user_id)); @endphp
            @if(!$has_filters)
                <div class="alert alert-light border text-center text-muted mb-0">
                    <i class="fas fa-filter mr-1"></i>Choose a user and/or date range, then click <b>Filter</b> to plot routes.
                </div>
            @elseif(count($route_data) < 1)
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle mr-1"></i>No recorded location trails for the selected filters.
                </div>
            @else
                <figure class="highcharts-figure mb-0">
                    <div id="container"></div>
                </figure>
            @endif
        </div>
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

        // Bootstrap theme colours, so the routes match the rest of the app.
        const palette = ['#007bff', '#28a745', '#e83e8c', '#fd7e14', '#6f42c1',
            '#17a2b8', '#dc3545', '#20c997', '#6610f2', '#6c757d'];

        const successColor = '#28a745';
        const dangerColor = '#dc3545';
        const mutedColor = '#6c757d';

        const routeSeries = routeData.map((route, index) => ({
            type: 'mapline',
            name: route.name,
            color: palette[index % palette.length],
            lineWidth: 2.5,
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
                margin: 0,
                style: {
                    fontFamily: 'Source Sans Pro, -apple-system, sans-serif'
                }
            },

            title: {
                text: ''
            },

            credits: {
                enabled: false
            },

            tooltip: {
                useHTML: true,
                borderRadius: 6,
                style: {
                    fontSize: '11px'
                }
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
                    text: 'Routes',
                    style: {
                        fontSize: '11px',
                        fontWeight: '600'
                    }
                },
                align: 'left',
                float: true,
                borderRadius: 4,
                padding: 8,
                itemMarginBottom: 4,
                itemStyle: {
                    fontSize: '11px',
                    fontWeight: '400'
                },
                backgroundColor: 'rgba(255, 255, 255, .9)'
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
                color: mutedColor,
                marker: {
                    radius: 3,
                    lineColor: '#ffffff',
                    lineWidth: 1
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
                color: successColor,
                marker: {
                    symbol: 'triangle',
                    radius: 6,
                    lineColor: '#ffffff',
                    lineWidth: 1
                },
                dataLabels: {
                    enabled: true,
                    format: 'IN',
                    y: -12,
                    allowOverlap: true,
                    style: {
                        fontSize: '9px',
                        fontWeight: '700',
                        color: successColor,
                        textOutline: '2px #ffffff'
                    }
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
                color: dangerColor,
                marker: {
                    symbol: 'square',
                    radius: 6,
                    lineColor: '#ffffff',
                    lineWidth: 1
                },
                dataLabels: {
                    enabled: true,
                    format: 'OUT',
                    y: -12,
                    allowOverlap: true,
                    style: {
                        fontSize: '9px',
                        fontWeight: '700',
                        color: dangerColor,
                        textOutline: '2px #ffffff'
                    }
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
