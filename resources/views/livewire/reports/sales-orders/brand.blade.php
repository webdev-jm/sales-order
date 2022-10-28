<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Per brand sales contribution</h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="container-brand"></div>
                <p class="highcharts-description">
                    Sales Order Grand Total: <h4>{{number_format($grand_total, 2)}}</h4>
                </p>
            </figure>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            Highcharts.chart('container-brand', {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: 'Per Brand'
                },
                subtitle: {
                    text: 'Grand Total Per Brand'
                },
                plotOptions: {
                    pie: {
                        innerSize: 100,
                        depth: 45,
                        colors: [
                            '#f54242', // bellic
                            '#eded07', // defensil
                            '#a807ed', // danarra
                            '#5b07ed', // dreamwhite
                            '#eda807', // bare soap
                            '#0404b0', // men products
                            '#ff0808', // kojiesan
                        ]
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
