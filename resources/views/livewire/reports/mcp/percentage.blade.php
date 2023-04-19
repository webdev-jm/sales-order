<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Percentage</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($data as $key => $value)
                <div class="col-lg-3 col-md-4">
                    <div class="info-box bg-gradient-secondary">
                        <span class="info-box-icon"><i class="fa fa-percentage"></i></span>
          
                        <div class="info-box-content">
                            <span class="info-box-text text-uppercase">{{$key}}</span>
                            <span class="info-box-number">
                                <i class="fa fa-spinner fa-spin" wire:loading></i>
                                <span wire:loading.remove>{{$value['count']}}</span>
                            </span>
            
                            <div class="progress">
                                <div class="progress-bar" style="width: {{$value['percent']}}%"></div>
                            </div>
                            <span class="progress-description">
                                {{$value['percent']}}% of {{$value['total']}}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="col-lg-3 col-md-4">
                    <div class="info-box bg-gradient-secondary">
                        <span class="info-box-icon"><i class="fa fa-percentage"></i></span>
          
                        <div class="info-box-content">
                            <span class="info-box-text text-uppercase">Average # of minutes per visit</span>
                            <span class="info-box-number">
                                <i class="fa fa-spinner fa-spin" wire:loading></i>
                                <span wire:loading.remove>{{number_format($avg_minutes, 2)}}</span>
                            </span>
            
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Average number of minutes per visit
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
