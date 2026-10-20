@extends('adminlte::page')

@section('title')
    MAPS
@endsection

@section('css')
<style>
    .maps-page #container { height: 620px; border: 1px solid #dee2e6; border-radius: .35rem; overflow: hidden; }
    .maps-page .card-title { font-size: .95rem; font-weight: 600; }
    .maps-page label { font-size: .78rem; font-weight: 600; color: #495057; margin-bottom: .25rem; text-transform: uppercase; letter-spacing: .02em; }
    .maps-subtitle { font-size: .8rem; color: #6c757d; }
    .maps-page .info-box { min-height: 72px; }
    .maps-page .info-box-number { font-size: 1.25rem; }
    .maps-legend { display: flex; flex-wrap: wrap; gap: .25rem 1rem; font-size: .78rem; color: #495057; }
    .maps-legend .key { display: inline-flex; align-items: center; gap: .35rem; }
    .maps-legend .swatch { width: .8rem; height: .8rem; display: inline-block; border: 1px solid #fff; box-shadow: 0 0 0 1px rgba(0, 0, 0, .15); }
    .maps-legend .swatch.dot { border-radius: 50%; }
    .maps-legend .swatch.tri { width: 0; height: 0; border: 0; border-left: .45rem solid transparent; border-right: .45rem solid transparent; border-bottom: .75rem solid #28a745; box-shadow: none; }
    .maps-legend .swatch.sq { border-radius: 2px; }
    .maps-page .route-swatch { width: .9rem; height: .35rem; display: inline-block; border-radius: 2px; vertical-align: middle; }
    .maps-page .table-routes tbody tr { cursor: pointer; }
    .maps-page .table-routes tbody tr.table-active td { font-weight: 600; }
    .maps-page .table-scroll { max-height: 420px; overflow-y: auto; }
    .maps-page .route-line path { vector-effect: non-scaling-stroke; }
    .maps-page .table-scroll thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
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
                        <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" form="filter_form" value="{{$date_from}}">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" form="filter_form" value="{{$date_to}}">
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

    @php
        $has_filters = (!empty($date_from) || !empty($date_to) || !empty($user_id));
    @endphp

    @if(!$has_filters)
        <div class="alert alert-light border text-center text-muted">
            <i class="fas fa-filter mr-1"></i>Choose a user and/or date range, then click <b>Filter</b> to plot routes.
        </div>
    @elseif(count($routes) < 1)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>No recorded location trails for the selected filters.
        </div>
    @else
        <div class="row">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-route"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Routes</span><span class="info-box-number">{{ number_format($totals['routes']) }}</span></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Users</span><span class="info-box-number">{{ number_format($totals['users']) }}</span></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-road"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Distance</span><span class="info-box-number">{{ number_format($totals['distance_km'], 1) }} km</span></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Signed in</span><span class="info-box-number">{{ number_format($totals['hours'], 1) }} h</span></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-map-pin"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Stays</span><span class="info-box-number">{{ number_format($totals['stops']) }}</span></div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-signal"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Tracking gaps</span><span class="info-box-number">{{ number_format($totals['gaps']) }}</span></div>
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
                    <span class="key"><span class="swatch dot" style="background:#ffc107"></span>Stayed {{ \App\Services\Maps\RouteSummary::STOP_MIN_MINUTES }}+ min</span>
                    <span class="key"><span class="swatch dot" style="background:#6c757d"></span>Recorded point</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary ml-2 d-none" id="show-all-routes">Show all routes</button>
                </div>
            </div>
            <div class="card-body p-2">
                <figure class="highcharts-figure mb-0">
                    <div id="container"></div>
                </figure>
                <p class="text-muted small mb-0 mt-2">The line follows readings within {{ \App\Services\Maps\RouteSummary::LINE_ACCURACY_M }} m accuracy and ignores GPS drift under {{ \App\Services\Maps\RouteSummary::MIN_STEP_M }} m. Recorded points are hidden until you pick a route or turn them on in the map legend.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-list mr-1"></i>Routes <small class="text-muted">click a route to show it alone</small></h3></div>
                    <div class="card-body p-0 table-scroll">
                        <table class="table table-sm table-hover mb-0 table-routes">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>User</th>
                                    <th>Branch</th>
                                    <th>Time in</th>
                                    <th>Time out</th>
                                    <th class="text-right">Signed in</th>
                                    <th class="text-right">Distance</th>
                                    <th class="text-right">Stays</th>
                                    <th class="text-right" title="Readings received / points stored">Readings</th>
                                    <th class="text-right" title="No reading for {{ \App\Services\Maps\RouteSummary::GAP_MINUTES }} minutes or more">Gaps</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($routes as $index => $route)
                                    <tr data-route="{{ $index }}">
                                        <td><span class="route-swatch" data-swatch="{{ $index }}"></span></td>
                                        <td>{{ $route['user'] }}</td>
                                        <td>{{ $route['branch'] }}</td>
                                        <td class="text-nowrap">{{ $route['time_in'] }}</td>
                                        <td class="text-nowrap">{{ $route['time_out'] ?: 'still signed in' }}</td>
                                        <td class="text-right text-nowrap">{{ intdiv($route['duration_minutes'], 60) }}h {{ str_pad($route['duration_minutes'] % 60, 2, '0', STR_PAD_LEFT) }}m</td>
                                        <td class="text-right text-nowrap">{{ number_format($route['distance_km'], 2) }} km</td>
                                        <td class="text-right">{{ count($route['stops']) }}</td>
                                        <td class="text-right text-nowrap">{{ number_format($route['tracked_points']) }} / {{ number_format(count($route['points'])) }}@if($route['vague_points'] > 0) <span class="text-muted" title="Readings too vague to draw">(+{{ $route['vague_points'] }})</span>@endif</td>
                                        <td class="text-right text-nowrap">
                                            @if($route['gaps'] > 0)
                                                <span class="badge badge-danger" title="Longest {{ $route['longest_gap_minutes'] }} minutes">{{ $route['gaps'] }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i>Distance and time by user</h3></div>
                    <div class="card-body p-2"><div id="user-chart"></div></div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@section('js')
@if(count($routes) > 0)
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/tiledwebmap.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/offline-exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
<script>
    (function () {
        const routes = @json($routes);

        // Bootstrap theme colours, so the routes match the rest of the app.
        const palette = ['#007bff', '#28a745', '#e83e8c', '#fd7e14', '#6f42c1', '#17a2b8', '#dc3545', '#20c997', '#6610f2', '#6c757d'];
        const colorOf = (index) => palette[index % palette.length];
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const minutes = (value) => value >= 60 ? Math.floor(value / 60) + 'h ' + String(value % 60).padStart(2, '0') + 'm' : value + ' min';
        const time = (value) => String(value || '').slice(11, 16);

        document.querySelectorAll('[data-swatch]').forEach((el) => { el.style.background = colorOf(+el.dataset.swatch); });

        const routeTooltip = (route) => '<b>' + escapeHtml(route.user) + '</b><br>' + escapeHtml(route.branch) + '<br>' +
            'In: ' + escapeHtml(route.time_in) + '<br>Out: ' + escapeHtml(route.time_out || 'still signed in') + '<br>' +
            'Distance: <b>' + route.distance_km.toFixed(2) + ' km</b>, stays: ' + route.stops.length;

        const series = [{ type: 'tiledwebmap', name: 'Basemap', provider: { type: 'OpenStreetMap' }, showInLegend: false }];

        routes.forEach((route, index) => {
            const color = colorOf(index);
            if (route.coordinates.length > 1) {
                series.push({
                    type: 'mapline', id: 'route-' + index, routeIndex: index, name: route.user + ' - ' + route.branch,
                    color: color, fillColor: 'none', className: 'route-line', lineWidth: 3, showInLegend: false,
                    data: [{ geometry: { type: 'LineString', coordinates: route.coordinates }, route: route }],
                    tooltip: { pointFormatter: function () { return routeTooltip(this.route); } }
                });
            }
            series.push({
                type: 'mappoint', id: 'points-' + index, routeIndex: index, name: 'Recorded points',
                color: color, visible: false, showInLegend: false,
                marker: { radius: 3, lineColor: '#ffffff', lineWidth: 1 },
                data: route.points.map((p, n) => ({ lat: p.lat, lon: p.lon, p: p, n: n + 1, route: route })),
                tooltip: { pointFormatter: function () {
                    const p = this.p;
                    const span = p.last_seen_at && p.last_seen_at !== p.recorded_at ? ' to ' + escapeHtml(time(p.last_seen_at)) + ' (' + p.samples + ' readings)' : '';
                    return '<b>' + escapeHtml(this.route.user) + '</b><br>Point ' + this.n + ': ' + escapeHtml(p.recorded_at) + span +
                        '<br>Accuracy: ' + (p.accuracy === null ? '-' : Math.round(p.accuracy) + ' m');
                } }
            });
        });

        const pointSeries = (c) => c.series.filter((s) => String(s.options.id || '').startsWith('points-'));
        let focused = null;
        const all = (key) => routes.flatMap((route, index) => route[key].map((item) => Object.assign({ routeIndex: index, route: route }, item)));

        series.push({
            type: 'mappoint', id: 'all-points', name: 'Recorded points', color: '#6c757d', visible: false,
            marker: { radius: 0 }, data: [], events: {
                show: function () { pointSeries(this.chart).forEach((s) => s.setVisible(focused === null || s.options.routeIndex === focused, false)); this.chart.redraw(); },
                hide: function () { pointSeries(this.chart).forEach((s) => s.setVisible(false, false)); this.chart.redraw(); }
            }
        });

        series.push({
            type: 'mappoint', id: 'stops', name: 'Stays', color: '#ffc107',
            marker: { symbol: 'circle', lineColor: '#ffffff', lineWidth: 1.5 },
            data: all('stops').map((s) => ({
                lat: s.latitude, lon: s.longitude, s: s, routeIndex: s.routeIndex,
                marker: { radius: Math.min(16, 6 + Math.sqrt(s.minutes)) }
            })),
            dataLabels: { enabled: true, format: '{point.s.minutes}m', style: { fontSize: '9px', fontWeight: '700', textOutline: '2px #ffffff' }, y: -14, allowOverlap: false },
            tooltip: { pointFormatter: function () {
                const s = this.s;
                return '<b>' + escapeHtml(s.route.user) + '</b><br>Stayed ' + minutes(s.minutes) + '<br>' + escapeHtml(time(s.from)) + ' to ' + escapeHtml(time(s.until)) + '<br>' + escapeHtml(s.route.branch);
            } }
        });

        series.push({
            type: 'mappoint', id: 'starts', name: 'Sign In', color: '#28a745',
            marker: { symbol: 'triangle', radius: 7, lineColor: '#ffffff', lineWidth: 1 },
            dataLabels: { enabled: true, format: 'IN', y: -13, allowOverlap: true, style: { fontSize: '9px', fontWeight: '700', color: '#28a745', textOutline: '2px #ffffff' } },
            data: routes.map((route, index) => route.start && { lat: route.start.lat, lon: route.start.lon, route: route, routeIndex: index }).filter(Boolean),
            tooltip: { pointFormatter: function () { return '<b>SIGN IN</b><br>' + routeTooltip(this.route); } }
        });

        series.push({
            type: 'mappoint', id: 'ends', name: 'Sign Out', color: '#dc3545',
            marker: { symbol: 'square', radius: 6, lineColor: '#ffffff', lineWidth: 1 },
            dataLabels: { enabled: true, format: 'OUT', y: -13, allowOverlap: true, style: { fontSize: '9px', fontWeight: '700', color: '#dc3545', textOutline: '2px #ffffff' } },
            data: routes.map((route, index) => route.end && { lat: route.end.lat, lon: route.end.lon, route: route, routeIndex: index }).filter(Boolean),
            tooltip: { pointFormatter: function () { return '<b>SIGN OUT</b><br>' + routeTooltip(this.route); } }
        });

        const allCoordinates = routes.flatMap((route) => route.coordinates.concat(route.points.map((p) => [p.lon, p.lat])));
        const fit = (coordinates) => coordinates.length ? { type: 'MultiPoint', coordinates: coordinates } : null;

        const chart = Highcharts.mapChart('container', {
            chart: { margin: 0, style: { fontFamily: 'Source Sans Pro, -apple-system, sans-serif' } },
            title: { text: '' },
            credits: { enabled: false },
            tooltip: { useHTML: true, headerFormat: '', borderRadius: 6, style: { fontSize: '11px' } },
            mapNavigation: { enabled: true, enableMouseWheelZoom: true, enableDoubleClickZoom: true, enableTouchZoom: true, buttonOptions: { alignTo: 'spacingBox' } },
            mapView: fit(allCoordinates) ? { fitToGeometry: fit(allCoordinates), padding: 40, maxZoom: 17 } : { center: [121.0071423, 14.5635197], zoom: 12 },
            legend: { enabled: true, align: 'left', verticalAlign: 'bottom', floating: true, borderRadius: 4, padding: 8, itemStyle: { fontSize: '11px', fontWeight: '400' }, backgroundColor: 'rgba(255, 255, 255, .9)' },
            plotOptions: { series: { turboThreshold: 0 }, mappoint: { dataLabels: { enabled: false } } },
            series: series
        });

        // Show one route alone: hide the other routes and their markers, then zoom to it.
        const rows = document.querySelectorAll('.table-routes tbody tr');
        const showAll = document.getElementById('show-all-routes');
        function fitTo(coordinates, padding) {
            if (!coordinates.length) {
                return;
            }
            const projected = coordinates.map(([lon, lat]) => chart.mapView.lonLatToProjectedUnits({ lon: lon, lat: lat }));
            const xs = projected.map((p) => p.x), ys = projected.map((p) => p.y);
            chart.mapView.fitToBounds({ x1: Math.min(...xs), x2: Math.max(...xs), y1: Math.min(...ys), y2: Math.max(...ys) }, padding);
        }

        function focus(index) {
            focused = index;
            chart.series.forEach((s) => {
                if (s.options.routeIndex !== undefined) {
                    const isRoute = s.options.routeIndex === index;
                    const pointsOn = chart.get('all-points').visible;
                    s.setVisible(s.type === 'mapline' ? (index === null || isRoute) : ((index !== null && isRoute) || (index === null && pointsOn)), false);
                }
            });
            ['stops', 'starts', 'ends'].forEach((id) => {
                const s = chart.get(id);
                s.points.forEach((p) => p.graphic && p.graphic.attr({ opacity: index === null || p.options.routeIndex === index ? 1 : 0.15 }));
                s.points.forEach((p) => p.dataLabel && p.dataLabel.attr({ opacity: index === null || p.options.routeIndex === index ? 1 : 0 }));
            });
            chart.redraw();

            const route = index === null ? null : routes[index];
            const coords = route ? route.coordinates.concat(route.points.map((p) => [p.lon, p.lat])) : allCoordinates;
            fitTo(coords, 50);

            rows.forEach((row) => row.classList.toggle('table-active', index !== null && +row.dataset.route === index));
            showAll.classList.toggle('d-none', index === null);
        }

        rows.forEach((row) => row.addEventListener('click', () => {
            const index = +row.dataset.route;
            focus(focused === index ? null : index);
            document.getElementById('container').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }));
        showAll.addEventListener('click', () => focus(null));

        // Distance and time signed in per user.
        const byUser = {};
        routes.forEach((route) => {
            byUser[route.user] = byUser[route.user] || { km: 0, hours: 0 };
            byUser[route.user].km += route.distance_km;
            byUser[route.user].hours += route.duration_minutes / 60;
        });
        const names = Object.keys(byUser).sort();

        Highcharts.chart('user-chart', {
            chart: { type: 'bar', height: Math.max(220, 80 + names.length * 34) },
            title: { text: '' },
            credits: { enabled: false },
            xAxis: { categories: names, title: { text: null } },
            yAxis: [{ min: 0, title: { text: 'km' } }, { min: 0, title: { text: 'hours' }, opposite: true }],
            tooltip: { shared: true, valueDecimals: 1 },
            series: [
                { name: 'Distance (km)', color: '#28a745', data: names.map((n) => Math.round(byUser[n].km * 10) / 10) },
                { name: 'Signed in (h)', color: '#6c757d', yAxis: 1, data: names.map((n) => Math.round(byUser[n].hours * 10) / 10) }
            ]
        });
    })();
</script>
@endif
@endsection

@section('footer')
@endsection
