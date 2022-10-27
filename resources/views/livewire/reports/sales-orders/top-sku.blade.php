<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top SKUs</h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="sku-container"></div>
                <p class="highcharts-description">
                  Chart showing the top 10 sku by amount.
                </p>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            Highcharts.chart('sku-container', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'TOP SKUs'
                },
                xAxis: {
                    categories: @php echo json_encode($categories); @endphp
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Sales Order Total'
                    }
                },
                legend: {
                    reversed: true
                },
                plotOptions: {
                    series: {
                        stacking: 'normal'
                    }
                },
                series: [
                    {
                        name: 'TOP SKU',
                        data: @php echo json_encode($data); @endphp
                    },
                ]
            });
        });
    </script>
</div>
