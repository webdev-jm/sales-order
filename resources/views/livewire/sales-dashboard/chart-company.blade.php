<div>
    <div class="card border-4 border-gray-200 rounded-3xl shadow">
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="company-chart"></div>
            </figure>
        </div>
    </div>
    
    <script>
        var company_chart = Highcharts.chart('company-chart', {
            chart: {
                plotShadow: false,
                type: 'pie'
            },
            accessibility: {
                enabled: false,
            },
            title: {
                text: 'COMPANY',
                align: 'center',
            },
            colors: ['#5DADE2', '#de6304'],
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    borderRadius: 5,
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                        distance: -50,
                        filter: {
                            property: 'percentage',
                            operator: '>',
                            value: 4
                        }
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
