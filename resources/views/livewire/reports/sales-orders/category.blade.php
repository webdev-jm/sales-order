<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Per category sales contribution</h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="container-category"></div>
                <p class="highcharts-description">
                    Sales Order Grand Total: <h4>{{number_format($grand_total, 2)}}</h4>
                </p>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            Highcharts.chart('container-category', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Per Category'
                },
                subtitle: {
                    text: 'Grand Total Per Brand'
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45
                    }
                },
                series: [{
                    name: 'TOTAL',
                    allowPointSelect: true,
                    data: @php echo json_encode($chart_data); @endphp
                }]
            });
        });
    </script>
</div>
