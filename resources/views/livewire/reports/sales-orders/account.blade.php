<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Per account sales contribution</h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="container-accounts"></div>
                <p class="highcharts-description">
                    Sales Order Total: <h4>{{number_format($grand_total, 2)}}</h4>
                </p>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            Highcharts.chart('container-accounts', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Per Account'
                },
                subtitle: {
                    text: 'Grand Total Per Account'
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45
                    }
                },
                series: [{
                    name: 'TOTAL',
                    data: @php echo json_encode($chart_data); @endphp
                }]
            });
        });
    </script>
</div>
