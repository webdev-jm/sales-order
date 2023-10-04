<div>
    <div class="card border-4 border-gray-200 rounded-3xl shadow">
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="sales-bar"></div>
            </figure>
        </div>
    </div>

    <script>
        var sales_vs_target_chart = Highcharts.chart('sales-bar', {
            chart: {
                type: 'column',
            },
            accessibility: {
                enabled: false,
            },
            title: {
                text: 'Sales vs Target',
                align: 'center',
            },
            xAxis: {
                categories: @php echo json_encode($chart_data['categories'] ?? []); @endphp,
            },
            yAxis: [{
                min: 0,
                title: {
                    text: 'Sales',
                },
                stackLabels: {
                    enabled: false,
                },
            },
            {
                min: 0,
                max:100,
                opposite: true,
                title: {
                    text: 'Percent',
                },
                labels: {
                    format: '{value} %',
                },
            }], 
            colors: ['#2980B9', '#27AE60'],
            legend: {
                align: 'left',
                x: 150,
                verticalAlign: 'top',
                y: 70,
                floating: true,
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: false,
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}',
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: false,
                    },
                },
                line: {
                    dataLabels: {
                        enabled: true,
                    },
                }
            },
            series: [
                {
                    type: 'column',
                    name: 'SALES',
                    data: @php echo json_encode($chart_data['sales_data'] ?? []); @endphp
                }, 
                {
                    type: 'column',
                    name: 'TARGET',
                    data: @php echo json_encode($chart_data['target_data'] ?? []); @endphp
                }, 
                {
                    type: 'line',
                    name: 'VS TARGET',
                    yAxis:1,
                    data: @php echo json_encode($chart_data['line_data'] ?? []); @endphp,
                    marker: {
                        lineWidth: 10,
                        lineColor: '#F39C12',
                        fillColor: 'white'
                    },
                    color: '#F39C12',
                    lineWidth: 7,
                    dataLabels: {
                        enabled: true,
                        align: 'right', // Set the alignment to 'right'
                        x: 5, // Set the x-offset to move the data label to the right
                    },
                    showInLegend: true,
                }
            ]
        });
    </script>
</div>
