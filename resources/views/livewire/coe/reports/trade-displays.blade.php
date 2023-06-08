<div>
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">TRADE DISPLAY <i class="fa fa-circle-notch fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-6 table-responsive p-0">
                    <table class="table table-bordered table-stripe table-sm">
                        <thead>
                            <tr class="text-center bg-dark text-white">
                                <th class="align-middle">TRADE REPORT</th>
                                <th class="align-middle">ACTUAL</th>
                                <th class="align-middle">TARGET</th>
                                <th class="align-middle">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trade_reports as $report)
                            <tr class="text-center">
                                <td>{{$report}}</td>
                                <td>{{$data[$report]['actual']}}</td>
                                <td>{{$data[$report]['target']}}</td>
                                <td>{{number_format($data[$report]['vs_target'])}}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="text-center bg-dark text-white">
                                <th>TOTAL</th>
                                <th>{{$totals['actual']}}</th>
                                <th>{{$totals['target']}}</th>
                                <th>{{number_format($totals['vs_target'])}}%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="col-lg-6">
                    <div class="" id="container"></div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            Highcharts.chart('container', {
                series: @php echo json_encode($chart_data); @endphp,
                chart: {
                    type: 'column',
                    options3d: {
                        enabled: true,
                        alpha: 5,
                        beta: 15,
                        depth: 100,
                        viewDistance: 30
                    }
                },
                title: {
                    text: 'TRADE DISPLAY ACTUAL VS TARGET'
                },
                colors: [
                    '#080808', '#DE142A'
                ],
                subtitle: {
                    text:
                        'Source: COE Reports'
                },
                xAxis: {
                    categories: @php echo json_encode($trade_reports); @endphp,
                    labels: {
                        x: -10
                    }
                },
                yAxis: {
                    allowDecimals: false,
                    title: {
                        text: 'TOTAL'
                    }
                }
            });

            window.addEventListener('setChart', e => {
                Highcharts.chart('container', {
                    series: e.detail.data,
                    chart: {
                        type: 'column',
                        options3d: {
                            enabled: true,
                            alpha: 5,
                            beta: 15,
                            depth: 100,
                            viewDistance: 30
                        }
                    },
                    title: {
                        text: 'TRADE DISPLAY ACTUAL VS TARGET'
                    },
                    colors: [
                        '#080808', '#DE142A'
                    ],
                    subtitle: {
                        text:
                            'Source: COE Reports'
                    },
                    xAxis: {
                        categories: e.detail.category,
                        labels: {
                            x: -10
                        }
                    },
                    yAxis: {
                        allowDecimals: false,
                        title: {
                            text: 'TOTAL'
                        }
                    }
                });
            });
        });
    </script>
</div>
