<div>
    <div class="card border-4 border-gray-200 rounded-3xl shadow">
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="chart-business-unit"></div>
            </figure>
        </div>
    </div>

    <script>
        var business_unit_chart = Highcharts.chart('chart-business-unit', {
            chart: {
                plotShadow: false,
                type: 'pie'
            },
            accessibility: {
                enabled: false,
            },
            title: {
                text: 'BUSINESS UNIT',
                align: 'center',
            },
            colors: ['#8f8d8c', '#2980B9', '#27AE60'],
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    borderRadius: 5,
                    dataLabels: {
                        enabled: true,
                        format: '<b class="border-0">{point.name}</b><br>{point.percentage:.1f} %',
                        distance: -50,
                        filter: {
                            property: 'percentage',
                            operator: '>',
                            value: 4
                        },
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Share',
                data: @php echo json_encode($chart_data['data'] ?? []); @endphp
            }]
        });
    </script>
</div>
