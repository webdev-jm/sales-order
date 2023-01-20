<div>
    <ul class="pagination pagination-month justify-content-center">
        <li class="page-item"><a class="page-link" href="#" wire:click.prevent="selectDate({{$prev_year}}, '{{$prev_month}}')">«</a></li>
        <li class="page-item{{$month == '01' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '01')">
                <p class="page-month">Jan</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '02' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '02')">
                <p class="page-month">Feb</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '03' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '03')">
                <p class="page-month">Mar</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '04' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '04')">
                <p class="page-month">Apr</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '05' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '05')">
                <p class="page-month">May</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '06' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '06')">
                <p class="page-month">Jun</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '07' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '07')">
                <p class="page-month">Jul</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '08' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '08')">
                <p class="page-month">Aug</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '09' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '09')">
                <p class="page-month">Sep</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '10' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '10')">
                <p class="page-month">Oct</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '11' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '11')">
                <p class="page-month">Nov</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item{{$month == '12' ? ' active' : ''}}">
            <a class="page-link" href="#" wire:click.prevent="selectDate({{$year}}, '12')">
                <p class="page-month">Dec</p>
                <p class="page-year">{{$year}}</p>
            </a>
        </li>
        <li class="page-item"><a class="page-link" href="#" wire:click.prevent="selectDate({{$next_year}}, '{{$next_month}}')">»</a></li>
    </ul>
    
    <div class="row">
        <div class="col-lg-3">

        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Select Day</h3>
                    <div class="card-tools">
                        <button class="btn btn-default btn-sm" wire:click.prevent="clearDay" wire:loading.attr="disabled">Clear</button>
                        <button class="btn btn-success btn-sm" wire:click.prevent="export" wire:loading.attr="disabled"><i class="fa fa-file-export mr-1"></i>Export</button>
                    </div>
                </div>
                <div class="card-body text-center">
                    @for($i = 1; $i <= $total_days; $i++)
                    <button class="btn {{isset($days[$year][(int)$month]) && in_array($i, $days[$year][(int)$month]) ? 'btn-primary' : 'btn-default'}} my-1" wire:click.prevent="selectDay('{{$i}}')" wire:loading.attr="disabled">
                        {{$i < 10 ? '0'.$i : $i}}
                    </button>
                    @endfor
                </div>
                <div class="card-footer">
                    @if(isset($days))
                        @foreach($days as $year => $months)
                            @foreach($months as $month => $days)
                                @foreach($days as $day)
                                <span class="badge badge-primary">{{$year.'-'.$month.'-'.($day < 10 ? '0'.(int)$day : $day)}}</span>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endisset
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            
        </div>
    </div>
</div>
