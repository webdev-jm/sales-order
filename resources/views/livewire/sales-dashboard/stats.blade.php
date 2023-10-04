<div>
    {{-- STATS --}}
    <div class="row">
        {{-- SALES --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-green-600 via-green-400 to-green-600">
                    <i class="fa fa-dollar-sign text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">SALES</span>
                    <span class="info-box-number text-lg" id="val-sales">
                        @if(empty($sales))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($sales['total_sales'], 2)}}
                        @endif
                    </span>
                </div>
            </div>
        </div>
        {{-- TARGET --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-blue-600 via-blue-400 to-blue-600">
                    <i class="fa fa-bullseye text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">TARGET</span>
                    <span class="info-box-number text-lg" id="val-target">
                        @if(empty($target))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($target['target'], 2)}}
                        @endif
                    </span>
                </div>
            </div>
        </div>
        {{-- SALES PERFORMANCE --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-yellow-600 via-yellow-400 to-yellow-600">
                    <i class="fa fa-chart-bar text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">SALES PERFORMANCE</span>
                    <span class="info-box-number text-lg" id="val-performance">
                        @if(empty($sales_performance))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($sales_performance, 2)}}%
                        @endif
                    </span>
                </div>
            </div>
        </div>
        {{-- TIME PAR --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-cyan-600 via-cyan-400 to-cyan-600">
                    <i class="fa fa-history text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">TIME PAR</span>
                    <span class="info-box-number text-lg" id="val-time-par">
                        @if(empty($time_par))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($time_par['time_par'], 2)}}%
                        @endif
                    </span>
                </div>
            </div>
        </div>
        {{-- GROWTH VS LY --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-red-600 via-red-400 to-red-600">
                    <i class="fa fa-chart-line text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">GROWTH VS. LY</span>
                    <span class="info-box-number text-lg" id="val-growth">
                        @if(empty($growth))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($growth['gw'], 2)}}%
                        @endif
                    </span>
                </div>
            </div>
        </div>
        {{-- INVENTORY DAYS OUTSTANDING --}}
        <div class="col-lg-12">
            <div class="card border-4 border-gray-200 rounded-3xl shadow">
                <div class="card-body">
                    <figure class="highcharts-figure">
                        <div id="container"></div>
                    </figure>
                </div>
            </div>
        </div>

        {{-- EXPORT --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-red-600 via-orange-400 to-orange-600">
                    <i class="fa fa-plane-departure text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">EXPORT<small class="ml-2 text-muted">Collection Performance</small></span>
                    <span class="info-box-number mt-0 text-lg" id="val-export">
                        @if(empty($export))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($export['performance'], 2)}}%
                        @endif
                    </span>
                    
                </div>
            </div>
        </div>
        {{-- RDG --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-yellow-600 via-rose-400 to-rose-600">
                    <i class="fa fa-map text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">RDG<small class="ml-2 text-muted">Collection Performance</small></span>
                    <span class="info-box-number text-lg" id="val-rdg">
                        @if(empty($rdg))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($rdg['performance'], 2)}}%
                        @endif
                    </span>
                    
                </div>
            </div>
        </div>
        {{-- NKAG --}}
        <div class="col-lg-12">
            <div class="info-box border-4 border-gray-200 rounded-3xl border-blur-sm bg-gradient-to-t from-white to-gray-200 shadow">
                <span class="info-box-icon rounded-3xl bg-gradient-to-t from-red-600 via-yellow-400 to-yellow-600">
                    <i class="fa fa-key text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text font-[900]">NKAG<small class="ml-2 text-muted">Collection Performance</small></span>
                    <span class="info-box-number text-lg" id="val-nkag">
                        @if(empty($nkag))
                            <i class="fa fa-spinner fa-spin"></i>
                        @else
                            {{number_format($nkag['performance'], 2)}}%
                        @endif
                    </span>
                    
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>
        var speed_chart = Highcharts.chart('container', {
            chart: {
                type: 'gauge',
                plotBackgroundColor: null,
                plotBackgroundImage: null,
                plotBorderWidth: 0,
                plotShadow: false,
                height: '80%'
            },

            title: {
                text: 'Speedometer'
            },

            pane: {
                startAngle: -90,
                endAngle: 89.9,
                background: null,
                center: ['50%', '75%'],
                size: '110%'
            },

            // the value axis
            yAxis: {
                min: 0,
                max: 200,
                tickPixelInterval: 72,
                tickPosition: 'inside',
                tickColor: Highcharts.defaultOptions.chart.backgroundColor || '#FFFFFF',
                tickLength: 20,
                tickWidth: 2,
                minorTickInterval: null,
                labels: {
                    distance: 20,
                    style: {
                        fontSize: '14px'
                    }
                },
                lineWidth: 0,
                plotBands: [{
                    from: 0,
                    to: 120,
                    color: '#55BF3B', // green
                    thickness: 20
                }, {
                    from: 120,
                    to: 160,
                    color: '#DDDF0D', // yellow
                    thickness: 20
                }, {
                    from: 160,
                    to: 200,
                    color: '#DF5353', // red
                    thickness: 20
                }]
            },

            series: [{
                name: 'Speed',
                data: [80],
                tooltip: {
                    valueSuffix: ' km/h'
                },
                dataLabels: {
                    format: '{y} km/h',
                    borderWidth: 0,
                    color: (
                        Highcharts.defaultOptions.title &&
                        Highcharts.defaultOptions.title.style &&
                        Highcharts.defaultOptions.title.style.color
                    ) || '#333333',
                    style: {
                        fontSize: '16px'
                    }
                },
                dial: {
                    radius: '80%',
                    backgroundColor: 'gray',
                    baseWidth: 12,
                    baseLength: '0%',
                    rearLength: '0%'
                },
                pivot: {
                    backgroundColor: 'gray',
                    radius: 6
                }

            }]

        });

        setInterval(() => {
            const chart = speed_chart;
            if (chart && !chart.renderer.forExport) {
                const point = chart.series[0].points[0],
                    inc = Math.round((Math.random() - 0.5) * 20);

                let newVal = point.y + inc;
                if (newVal < 0 || newVal > 200) {
                    newVal = point.y - inc;
                }

                point.update(newVal);
            }

        }, 300);

    </script>
</div>
