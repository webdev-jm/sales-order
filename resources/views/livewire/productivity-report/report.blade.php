<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">FILTER</h3>
        </div>
        <div class="card-body">
    
            <div class="row">
                {{-- DATE FROM --}}
                <div class="col-lg-2">
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control" wire:model="date_from" id="date_from">
                    </div>
                </div>
    
                {{-- DATE TO --}}
                <div class="col-lg-2">
                    <div class="form-group">
                        <label for="date_to">Date From</label>
                        <input type="date" class="form-control" wire:model="date_to" id="date_to">
                    </div>
                </div>
            </div>
    
        </div>
    </div>
    
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">WEEKLY REPORT</h3>
        </div>
        <div class="card-body">
            @for($i = 1;$i <= $week_number;$i++)
                @if(!empty($data[$i]))
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">WEEK {{$i}}</h3>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr class="text-center">
                                    <th>ACCOUNT</th>
                                    <th>SALESMAN</th>
                                    <th>PLANNED CALLS</th>
                                    @foreach($days_of_week as $day)
                                        <th class="text-uppercase">{{$day}}</th>
                                    @endforeach
                                    <th>ACTUAL CALLS</th>
                                    <th>CALL RATE</th>
                                    <th>PRODUCTIVITY CALLS</th>
                                    <th>HIT CALLS</th>
                                    <th>GAPS VS PLANNED</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data[$i] as $name => $val)
                                <tr class="text-center">
                                    <td>{{$val['account']}}</td>
                                    <td>{{$name}}</td>
                                    <td>{{$val['planned']}}</td>
                                    @foreach($days_of_week as $day)
                                        <td>{{$val[$day] ?? 0}}</td>
                                    @endforeach
                                    <td>{{$val['total_visited']}}</td>
                                    <td>{{number_format($val['call_rate'] ?? 0)}}%</td>
                                    <td>{{$val['productive_calls']}}</td>
                                    <td>{{number_format($val['hit_calls'] ?? 0)}} %</td>
                                    <td>{{$val['gaps_vs_planned']}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endfor
        </div>
    </div>
</div>
