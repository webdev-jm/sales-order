<div class="row">
    <div class="col-lg-4">
        <form wire:submit.prevent="filter">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Report</h3>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="user_id">User</label>
                                <select id="user_id" class="form-control" wire:model.lazy="user_id" wire:change="filter">
                                    <option value=""></option>
                                    @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->firstname}} {{$user->lastname}} ({{$user->email}})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="date_from">Date From</label>
                                <input type="date" class="form-control" wire:model.lazy="date_from" wire:change="filter">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="date_from">Date To</label>
                                <input type="date" class="form-control" wire:model.lazy="date_to" wire:change="filter">
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Legends</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <i class="fa fa-check text-success"></i>
                        <span class="ml-2">Visited</span>
                    </div>
                    <div class="col-lg-4">
                        <i class="fa fa-times text-danger"></i>
                        <span class="ml-2">Not Visited</span>
                    </div>
                    <div class="col-lg-4">
                        <i class="fa fa-plus text-warning"></i>
                        <span class="ml-2">Deviated</span>
                    </div>
                </div>
            </div>  
        </div>

    </div>

    <div class="col-lg-8">
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">MCP Reports</h3>
                <div class="card-tools">
                    @can('report export')
                        <a href="" class="btn btn-success btn-sm" wire:click.prevent="export" wire:loading.attr="disabled"><i class="fa fa-file-export mr-2"></i>Export</a>
                    @endcan
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Actual (location)</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Visited</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $prev_date = '';
                        @endphp
                        @foreach($schedule_dates as $schedule_date)

                            {{-- check deviations --}}
                            @if(isset($deviation_logins[$schedule_date->user_id]))
                                @foreach($deviation_logins[$schedule_date->user_id] as $date => $logins)
                                    @if(($prev_date == '' || $prev_date < $date) && ($schedule_date->date > $date))
                                        <tr class="bg-secondary">
                                            <td class="font-weight-bold text-center">{{$date}}</td>
                                            <td colspan="6" class="font-weight-bold text-uppercase">{{$schedule_date->user->firstname}} {{$schedule_date->user->lastname}}</td>
                                        </tr>
                                        @foreach($logins as $branch_id => $login)
                                            <tr>
                                                <td class="align-middle text-center">
                                                    {{$date}}
                                                </td>
                                                <td class="align-middle">
                                                    {{$login['branch_code']}} {{$login['branch_name']}}
                                                </td>
                                                <td class="text-center">
                                                    @foreach($login['data'] as $actual)
                                                    <p class="mb-0">
                                                        {{$actual->latitude}}, {{$actual->longitude}}
                                                        <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$actual->id}})">
                                                            <i class="fa fa-info-circle text-primary ml-2"></i>
                                                        </a>
                                                    </p>
                                                    @endforeach
                                                </td>
                                                <td class="text-center">
                                                    @foreach($login['data'] as $actual)
                                                    <p class="mb-0">
                                                        {{date('h:i:s a', strtotime($actual->time_in))}}
                                                    </p>
                                                    @endforeach
                                                </td>
                                                <td class="text-center">
                                                    @foreach($login['data'] as $actual)
                                                        @if(!empty($actual->time_out))
                                                        <p class="mb-0">
                                                            {{date('h:i:s a', strtotime($actual->time_out))}}
                                                        </p>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td class="text-center align-middle p-0">
                                                    <i class="fa fa-plus text-warning" title="Deviation"></i>
                                                </td>
                                                <td></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif

                            <tr class="bg-secondary">
                                <td class="font-weight-bold text-center">{{$schedule_date->date}}</td>
                                <td colspan="6" class="font-weight-bold text-uppercase">{{$schedule_date->user->firstname}} {{$schedule_date->user->lastname}}</td>
                            </tr>
                            {{-- Scheduled --}}
                            @foreach($schedules[$schedule_date->user_id][$schedule_date->date] as $schedule)
                            <tr>
                                <td class="align-middle text-center">{{$schedule->date}}</td>
                                <td class="align-middle">{{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}</td>
                                @if(isset($actuals[$schedule->id]))
                                    <td class="text-center">
                                        @foreach($actuals[$schedule->id] as $actual)
                                        <p class="mb-0">
                                            {{$actual->latitude}}, {{$actual->longitude}}
                                            <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$actual->id}})">
                                                <i class="fa fa-info-circle text-primary ml-2"></i>
                                            </a>
                                        </p>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @foreach($actuals[$schedule->id] as $actual)
                                        <p class="mb-0">
                                            {{date('h:i:s a', strtotime($actual->time_in))}}
                                        </p>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @foreach($actuals[$schedule->id] as $actual)
                                            @if(!empty($actual->time_out))
                                            <p class="mb-0">
                                                {{date('h:i:s a', strtotime($actual->time_out))}}
                                            </p>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td class="text-center align-middle p-0">
                                        @if(!empty($actuals[$schedule->id]->count()))
                                            <i class="fa fa-check text-success" title="Visited"></i>
                                        @else
                                            <i class="fa fa-times text-danger" title="Not Visited"></i>
                                        @endif
                                    </td>
                                    <td></td>
                                @endif
                            </tr>
                            @endforeach
                            {{-- Deviations --}}
                            @if(isset($deviations[$schedule_date->user_id][$schedule_date->date]))
                                
                                @foreach($deviations[$schedule_date->user_id][$schedule_date->date] as $branch_id => $deviation)
                                <tr>
                                    <td class="align-middle text-center">
                                        {{$deviation['date']}}
                                    </td>
                                    <td class="align-middle">
                                        {{$deviation['branch_code']}} {{$deviation['branch_name']}}
                                    </td>
                                    <td class="text-center">
                                        @foreach($deviation['actuals'] as $actual)
                                        <p class="mb-0">
                                            {{$actual['latitude']}}, {{$actual['longitude']}}
                                            <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$actual['id']}})">
                                                <i class="fa fa-info-circle text-primary ml-2"></i>
                                            </a>
                                        </p>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @foreach($deviation['actuals'] as $actual)
                                        <p class="mb-0">
                                            {{date('h:i:s a', strtotime($actual['time_in']))}}
                                        </p>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @foreach($deviation['actuals'] as $actual)
                                            @if(!empty($actual['time_out']))
                                            <p class="mb-0">
                                                {{date('h:i:s a', strtotime($actual['time_out']))}}
                                            </p>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td class="text-center align-middle p-0">
                                        <i class="fa fa-plus text-warning" title="Deviation"></i>
                                    </td>
                                    <td></td>
                                </tr>
                                @endforeach
                            @endif

                            @php
                                $prev_date = $schedule_date->date;
                            @endphp
                        @endforeach
                    </tbody>
                </table>
        
            </div>
            <div class="card-footer pb-0">
                {{$schedule_dates->links()}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="detail-modal">
        <div class="modal-dialog modal-lg">
            <livewire:reports.mcp.login-detail/>
        </div>
    </div>

    <script>
        window.addEventListener('showDetail', event => {
            $('#detail-modal').modal('show');
        });

        document.addEventListener('livewire:load', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</div>
