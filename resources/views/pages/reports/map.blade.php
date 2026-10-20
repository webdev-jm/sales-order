@extends('adminlte::page')

@section('title')
    MCP MAP
@endsection

@section('css')
<style>
    .visit-map #container { height: 620px; border: 1px solid #dee2e6; border-radius: .35rem; overflow: hidden; }
    .visit-map .small-box .inner h3 { font-size: 1.7rem; }
    .visit-map .small-box .inner p { margin-bottom: 0; }
    .visit-map .small-box .inner small { display: block; opacity: .85; }
    .visit-map .legend-key { display: inline-flex; align-items: center; gap: .3rem; margin-right: .9rem; font-size: .8rem; color: #495057; }
    .visit-map .legend-key .dot { width: .75rem; height: .75rem; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 1px rgba(0,0,0,.15); }
    .visit-map .legend-key .diamond { width: .6rem; height: .6rem; transform: rotate(45deg); display: inline-block; box-shadow: 0 0 0 1px rgba(0,0,0,.15); }
    .visit-map table.table-visits tbody tr { cursor: pointer; }
    .visit-map table.table-visits th[data-sort] { cursor: pointer; white-space: nowrap; }
    .visit-map table.table-visits th[data-sort]::after { content: ' \2195'; opacity: .35; }
    .visit-map .table-scroll { max-height: 460px; overflow-y: auto; }
    .visit-map .distance-lines path { vector-effect: non-scaling-stroke; }
    .visit-map .table-scroll thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
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
<div class="visit-map">

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">FILTER</h3>
        </div>
        <div class="card-body pb-1">
            {!! Form::open(['method' => 'GET', 'route' => ['report.map'], 'id' => 'filter_form']) !!}
            {!! Form::close() !!}

            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" form="filter_form" value="{{$date_from}}">
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" form="filter_form" value="{{$date_to}}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select name="user_id" form="filter_form" class="form-control" id="user_id">
                            <option value="">All users</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}" {{$user_id == $user->id ? 'selected' : ''}}>{{$user->fullName()}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label class="d-none d-lg-block">&nbsp;</label>
                        <div class="d-flex">
                            {!! Form::submit('Filter', ['class' => 'btn btn-primary flex-fill', 'form' => 'filter_form']) !!}
                            <a href="{{route('report.map')}}" class="btn btn-outline-secondary ml-2">Reset</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(is_null($report))
        <div class="alert alert-light border text-center text-muted">
            <i class="fas fa-filter mr-1"></i>Choose a date range and/or a user, then click <b>Filter</b> to plot the visits.
        </div>
    @elseif(count($report['visits']) < 1 && count($report['branches']) < 1)
        <div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>No branch visits or scheduled branches for the selected filters.</div>
    @else
        @php($summary = $report['summary'])

        <div class="row">
            <div class="col-lg col-md-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($summary['visits']) }}</h3>
                        <p>Branch visits</p>
                        <small>{{ $summary['users'] }} {{ \Illuminate\Support\Str::plural('user', $summary['users']) }}</small>
                    </div>
                    <div class="icon"><i class="fas fa-store"></i></div>
                </div>
            </div>
            <div class="col-lg col-md-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ is_null($summary['on_site_rate']) ? '-' : $summary['on_site_rate'].'%' }}</h3>
                        <p>Signed in on site</p>
                        <small>within {{ $on_site_m }} m of the branch pin</small>
                    </div>
                    <div class="icon"><i class="fas fa-map-pin"></i></div>
                </div>
            </div>
            <div class="col-lg col-md-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($summary['off_site']) }}</h3>
                        <p>Signed in off site</p>
                        <small>over {{ number_format($near_m) }} m away; median {{ is_null($summary['median_distance']) ? '-' : number_format($summary['median_distance']).' m' }}</small>
                    </div>
                    <div class="icon"><i class="fas fa-route"></i></div>
                </div>
            </div>
            <div class="col-lg col-md-6 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ is_null($summary['coverage_rate']) ? '-' : $summary['coverage_rate'].'%' }}</h3>
                        <p>Schedule coverage</p>
                        <small>{{ number_format($summary['visited']) }} of {{ number_format($summary['scheduled']) }} scheduled visited, {{ number_format($summary['missed']) }} missed</small>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                </div>
            </div>
            <div class="col-lg col-md-6 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($summary['unscheduled']) }}</h3>
                        <p>Unscheduled visits</p>
                        <small>{{ number_format($summary['no_pin']) }} at branches with no pin</small>
                    </div>
                    <div class="icon"><i class="fas fa-random"></i></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center">
                <h3 class="card-title mr-auto"><i class="fas fa-map-marked-alt mr-1"></i>Visits map</h3>
                <div>
                    <span class="legend-key"><span class="dot" style="background:#28a745"></span>On site</span>
                    <span class="legend-key"><span class="dot" style="background:#fd7e14"></span>Near ({{ $on_site_m }}-{{ number_format($near_m) }} m)</span>
                    <span class="legend-key"><span class="dot" style="background:#dc3545"></span>Off site</span>
                    <span class="legend-key"><span class="dot" style="background:#6c757d"></span>Branch not pinned</span>
                    <span class="legend-key"><span class="diamond" style="background:#17a2b8"></span>Scheduled, visited</span>
                    <span class="legend-key"><span class="diamond" style="background:#343a40"></span>Scheduled, missed</span>
                </div>
            </div>
            <div class="card-body p-2">
                <div id="container"></div>
                <p class="text-muted small mb-0 mt-2">Dashed lines join a sign-in that was not on site to its branch pin. Click a series in the map legend to hide it.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i>Sign-in location by user</h3></div>
                    <div class="card-body p-2"><div id="per-user-chart"></div></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-check mr-1"></i>Schedule coverage by user</h3></div>
                    <div class="card-body p-0 table-scroll">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr><th>User</th><th class="text-right">Scheduled</th><th class="text-right">Visited</th><th class="text-right">Coverage</th></tr>
                            </thead>
                            <tbody>
                                @foreach($report['users'] as $row)
                                    @php($coverage = $row['scheduled'] ? round(100 * $row['visited'] / $row['scheduled']) : null)
                                    <tr>
                                        <td>{{ $row['user'] }}</td>
                                        <td class="text-right">{{ number_format($row['scheduled']) }}</td>
                                        <td class="text-right">{{ number_format($row['visited']) }}</td>
                                        <td class="text-right">
                                            @if(is_null($coverage))
                                                <span class="text-muted">-</span>
                                            @else
                                                <span class="badge {{ $coverage >= 90 ? 'badge-success' : ($coverage >= 70 ? 'badge-warning' : 'badge-danger') }}">{{ $coverage }}%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center">
                <h3 class="card-title mr-auto"><i class="fas fa-list mr-1"></i>Visits <small class="text-muted">click a row to find it on the map</small></h3>
                <div class="d-flex">
                    <select id="visit-status" class="form-control form-control-sm mr-2" aria-label="Filter by location">
                        <option value="">All locations</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="search" id="visit-search" class="form-control form-control-sm" placeholder="Search user or branch" aria-label="Search visits">
                </div>
            </div>
            <div class="card-body p-0 table-scroll">
                <table class="table table-sm table-hover mb-0 table-visits" id="visits-table">
                    <thead>
                        <tr>
                            <th data-sort="user">User</th>
                            <th data-sort="branch">Branch</th>
                            <th data-sort="time_in">Time in</th>
                            <th data-sort="time_out">Time out</th>
                            <th data-sort="distance_m" class="text-right">From pin</th>
                            <th data-sort="accuracy_m" class="text-right">GPS accuracy</th>
                            <th data-sort="status">Location</th>
                            <th data-sort="scheduled">Scheduled</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection

@section('js')
@if(!is_null($report) && (count($report['visits']) > 0 || count($report['branches']) > 0))
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/tiledwebmap.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/offline-exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
<script>
    (function () {
        const visits = @json($report['visits']);
        const branches = @json($report['branches']);
        const users = @json($report['users']);
        const statuses = @json($statuses);
        const colors = { on_site: '#28a745', near: '#fd7e14', off_site: '#dc3545', no_pin: '#6c757d', no_gps: '#adb5bd' };
        const branchColors = { visited: '#17a2b8', missed: '#343a40', not_counted: '#adb5bd' };
        const branchNames = { visited: 'Scheduled, visited', missed: 'Scheduled, missed', not_counted: 'Scheduled, not counted (rescheduled, deviated or on leave)' };

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const meters = (value) => value === null || value === undefined ? '-' : (value >= 1000 ? (value / 1000).toFixed(1) + ' km' : Math.round(value) + ' m');

        const plotted = visits.filter((v) => v.lat !== null);
        const coordinates = plotted.map((v) => [v.lon, v.lat]).concat(branches.map((b) => [b.lon, b.lat]));

        const visitSeries = ['on_site', 'near', 'off_site', 'no_pin'].map((status) => ({
            type: 'mappoint',
            id: 'visits-' + status,
            name: statuses[status],
            color: colors[status],
            marker: { symbol: 'circle', radius: 6, lineColor: '#ffffff', lineWidth: 1.5 },
            data: plotted.filter((v) => v.status === status).map((v) => ({ id: 'visit-' + v.id, lat: v.lat, lon: v.lon, visit: v })),
            tooltip: {
                pointFormatter: function () {
                    const v = this.visit;
                    return '<b>' + escapeHtml(v.user) + '</b><br>' + escapeHtml(v.branch) + '<br>' +
                        'In: ' + escapeHtml(v.time_in) + '<br>Out: ' + escapeHtml(v.time_out || '-') + '<br>' +
                        'From branch pin: <b>' + meters(v.distance_m) + '</b><br>GPS accuracy: ' + meters(v.accuracy_m) +
                        (v.scheduled ? '' : '<br><i>Not on the schedule</i>');
                }
            }
        }));

        const offsetLines = {
            type: 'mapline',
            name: 'Distance to branch',
            className: 'distance-lines',
            color: 'rgba(220, 53, 69, .55)',
            fillColor: 'none',
            lineWidth: 1.5,
            dashStyle: 'ShortDash',
            enableMouseTracking: false,
            data: plotted.filter((v) => (v.status === 'near' || v.status === 'off_site') && v.pin_lat !== null)
                .map((v) => ({ geometry: { type: 'LineString', coordinates: [[v.lon, v.lat], [v.pin_lon, v.pin_lat]] } }))
        };

        const branchSeries = ['visited', 'missed', 'not_counted'].map((state) => ({
            type: 'mappoint',
            name: branchNames[state],
            color: branchColors[state],
            visible: state !== 'not_counted',
            marker: { symbol: 'diamond', radius: 7, lineColor: '#ffffff', lineWidth: 1.5 },
            data: branches.filter((b) => b.state === state).map((b) => ({ lat: b.lat, lon: b.lon, branch: b })),
            tooltip: {
                pointFormatter: function () {
                    const b = this.branch;
                    return '<b>' + escapeHtml(b.name) + '</b><br>' + escapeHtml(b.user) + '<br>Scheduled: ' + escapeHtml(b.date) +
                        '<br>Source: ' + escapeHtml(b.source) + (b.objective ? '<br>Objective: ' + escapeHtml(b.objective) : '');
                }
            }
        }));

        const map = Highcharts.mapChart('container', {
            chart: { margin: 0 },
            title: { text: '' },
            credits: { enabled: false },
            accessibility: { description: 'Branch sign-ins coloured by their distance from the branch pin, with the scheduled branches.' },
            mapNavigation: { enabled: true, buttonOptions: { alignTo: 'spacingBox' } },
            mapView: coordinates.length
                ? { fitToGeometry: { type: 'MultiPoint', coordinates: coordinates }, padding: 40, maxZoom: 17 }
                : { center: [121.0071423, 14.5635197], zoom: 11 },
            legend: { enabled: true, align: 'left', verticalAlign: 'bottom', floating: true, backgroundColor: 'rgba(255,255,255,.9)', borderRadius: 4, padding: 8, itemStyle: { fontSize: '11px', fontWeight: '400' } },
            tooltip: { useHTML: true, headerFormat: '' },
            plotOptions: { series: { turboThreshold: 0 }, mappoint: { dataLabels: { enabled: false } } },
            series: [{ type: 'tiledwebmap', name: 'Basemap', provider: { type: 'OpenStreetMap' }, showInLegend: false }, offsetLines]
                .concat(branchSeries).concat(visitSeries)
        });

        Highcharts.chart('per-user-chart', {
            chart: { type: 'bar', height: Math.max(220, 60 + users.length * 26) },
            title: { text: '' },
            credits: { enabled: false },
            xAxis: { categories: users.map((u) => u.user), title: { text: null } },
            yAxis: { min: 0, allowDecimals: false, title: { text: 'Sign-ins' } },
            legend: { reversed: true },
            tooltip: { shared: true },
            plotOptions: { series: { stacking: 'normal' } },
            series: ['no_gps', 'no_pin', 'off_site', 'near', 'on_site'].map((status) => ({
                name: statuses[status],
                color: colors[status],
                data: users.map((u) => u[status])
            }))
        });

        // Visits table: built here so sorting and filtering do not reload the page.
        const tbody = document.querySelector('#visits-table tbody');
        const statusSelect = document.getElementById('visit-status');
        const search = document.getElementById('visit-search');
        const badge = { on_site: 'badge-success', near: 'badge-warning', off_site: 'badge-danger', no_pin: 'badge-secondary', no_gps: 'badge-light' };
        let sortKey = 'time_in';
        let sortAsc = true;

        function render() {
            const term = search.value.trim().toLowerCase();
            const status = statusSelect.value;
            const rows = visits
                .filter((v) => (!status || v.status === status) && (!term || (v.user + ' ' + v.branch).toLowerCase().includes(term)))
                .sort((a, b) => {
                    const x = a[sortKey], y = b[sortKey];
                    if (x === y) return 0;
                    if (x === null || x === undefined) return 1;
                    if (y === null || y === undefined) return -1;
                    return (x > y ? 1 : -1) * (sortAsc ? 1 : -1);
                });

            tbody.innerHTML = rows.map((v) => '<tr data-id="' + v.id + '">' +
                '<td>' + escapeHtml(v.user) + '</td>' +
                '<td>' + escapeHtml(v.branch) + '</td>' +
                '<td class="text-nowrap">' + escapeHtml(v.time_in) + '</td>' +
                '<td class="text-nowrap">' + escapeHtml(v.time_out || '-') + '</td>' +
                '<td class="text-right text-nowrap">' + meters(v.distance_m) + '</td>' +
                '<td class="text-right text-nowrap">' + meters(v.accuracy_m) + '</td>' +
                '<td><span class="badge ' + badge[v.status] + '">' + escapeHtml(statuses[v.status]) + '</span></td>' +
                '<td>' + (v.scheduled ? '<i class="fas fa-check text-success"></i>' : '<span class="text-muted">No</span>') + '</td>' +
                '</tr>').join('') || '<tr><td colspan="8" class="text-center text-muted">No visits match.</td></tr>';
        }

        document.querySelectorAll('#visits-table th[data-sort]').forEach((th) => th.addEventListener('click', () => {
            sortAsc = sortKey === th.dataset.sort ? !sortAsc : true;
            sortKey = th.dataset.sort;
            render();
        }));
        statusSelect.addEventListener('change', render);
        search.addEventListener('input', render);

        tbody.addEventListener('click', (event) => {
            const row = event.target.closest('tr[data-id]');
            const point = row && map.get('visit-' + row.dataset.id);
            if (!point) {
                return;
            }
            point.series.setVisible(true, false);
            map.mapView.setView([point.lon, point.lat], 16);
            point.select(true, false);
            map.tooltip.refresh(point);
            document.getElementById('container').scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        render();
    })();
</script>
@endif
@endsection

@section('footer')
@endsection
