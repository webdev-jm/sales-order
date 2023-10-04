<div>
    <div class="card border-4 border-gray-200 rounded-3xl shadow">
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="po-vs-invoiced"></div>
            </figure>
        </div>
    </div>

    <script>
        var po_invoiced_chart = Highcharts.chart('po-vs-invoiced', {
            chart: {
                type: 'bar',
            },
            accessibility: {
                enabled: false,
            },
            title: {
                text: 'PO vs INVOICED vs UNSERVED',
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}%',
            },
            xAxis: {
                categories: @php echo json_encode($chart_data['categories'] ?? []); @endphp,
            },
            yAxis: {
                min: 0,
                max:100,
                title: {
                    text: '',
                },
                labels: {
                    format: '{value}%'
                },
                stackLabels: {
                    enabled: false,
                },
                tickInterval: 20,
            },
            legend: {
                reversed: true,
            },
            colors: ['#808B96', '#D35400', '#3498DB'],
            plotOptions: {
                series: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            series: [
                {
                    type: 'bar',
                    name: 'UNSERVED',
                    data: @php echo json_encode($chart_data['data']->UNSERVED ?? []); @endphp
                },
                {
                    type: 'bar',
                    name: 'INVOICED',
                    data: @php echo json_encode($chart_data['data']->INVOICED ?? []); @endphp
                },
                {
                    type: 'bar',
                    name: 'PO',
                    data: @php echo json_encode($chart_data['data']->PO ?? []); @endphp
                }
            ]
        });
    </script>

</div>
