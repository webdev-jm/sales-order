<div class="row">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-lg-6">
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
                                            <option value="{{$user->id}}">{{$user->fullName()}} ({{$user->email}})</option>
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
            </div>

            <div class="col-lg-6">
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
        </div>
        
    </div>

    <div class="col-xl-12">
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">MCP Reports</h3>
                <div class="card-tools">
                    @can('report export')
                        <div wire:loading.remove>
                            <a href="" class="btn btn-success btn-sm" wire:click.prevent="export"><i class="fa fa-file-export mr-2"></i>Export</a>
                        </div>
                        <span wire:loading><i class="fa fa-spinner fa-spin"></i> Loading</span>
                    @endcan
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th></th>
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
                        @if(!empty($paginatedData))
                            @foreach($paginatedData as $key => $date)
                                @if(!empty($schedules[$date]))
                                    @foreach($schedules[$date] as $user_id => $val)
                                        <tr class="bg-secondary">
                                            <td></td>
                                            <td class="font-weight-bold text-center">
                                                {{$date}}
                                            </td>
                                            <td colspan="7" class="font-weight-bold text-uppercase">{{$val['user']['firstname']}} {{$val['user']['lastname']}}</td>
                                        </tr>

                                        {{-- SCHEDULES --}}
                                        @php
                                            $schedule_count = 0;
                                            $visited_count = 0;
                                        @endphp
                                        @foreach($val['schedules'] as $schedule)
                                            @php
                                                $schedule_count++;
                                            @endphp
                                            <tr>
                                                <td class="p-0 text-center align-middle">
                                                    <a href="#" data-toggle="tooltip" data-placement="right" wire:click.prevent="showScheduleDetail({{$schedule->id}}, 'schedule')">
                                                        <i class="fa fa-list-alt {{$schedule->source == 'deviation' ? 'text-danger' : ($schedule->source == 'request' ? 'text-warning' : 'text-info')}}" title="{{$schedule->source}}"></i>
                                                    </a>
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{$schedule->date}}
                                                </td>
                                                <td class="align-middle">[{{$schedule->branch->account->short_name}}] {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}</td>
                                                @if(isset($actuals[$schedule->id]))
                                                    <td class="text-left">
                                                        @foreach($actuals[$schedule->id] as $actual)
                                                        <p class="mb-0">
                                                            <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$actual->id}})">
                                                                <i class="fa fa-info-circle text-primary mr-2"></i>
                                                            </a>
                                                            {{$actual->latitude}}, {{$actual->longitude}}
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
                                                            @php
                                                                $visited_count++;
                                                            @endphp
                                                            <i class="fa fa-check text-success" title="Visited"></i>
                                                        @else
                                                            <i class="fa fa-times text-danger" title="Not Visited"></i>
                                                        @endif
                                                    </td>
                                                    <td></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        {{-- DEVIATIONS --}}
                                        
                                        @isset($deviations[$date][$user_id])
                                            @foreach($deviations[$date][$user_id] as $branch_id => $deviation)
                                                <tr>
                                                    <td class="p-0 text-center align-middle">
                                                        <a href="#" data-toggle="tooltip" data-placement="right" wire:click.prevent="showScheduleDetail({{$deviation['id']}}, 'unscheduled')">
                                                            <i class="fa fa-list-alt {{$deviation['source'] == 'deviation' ? 'text-danger' : ($deviation['source'] == 'request' ? 'text-warning' : 'text-info')}}" title="{{$deviation['source']}}"></i>
                                                        </a>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        {{$deviation['date']}}
                                                    </td>
                                                    <td class="align-middle">
                                                        [{{$deviation['account_name']}}] {{$deviation['branch_code']}} {{$deviation['branch_name']}}
                                                    </td>
                                                    <td class="text-left">
                                                        @foreach($deviation['actuals'] as $actual)
                                                        <p class="mb-0">
                                                            <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" wire:click.prevent="showDetail({{$actual['id']}})">
                                                                <i class="fa fa-info-circle text-primary mr-2"></i>
                                                            </a>
                                                            {{$actual['latitude']}}, {{$actual['longitude']}}
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
                                                        @if($schedule_count == $visited_count && $schedule_count > 0)
                                                            <i class="fa fa-check text-primary" title="Visited"></i>
                                                        @else
                                                            <i class="fa fa-plus text-warning" title="Deviation"></i>
                                                        @endif
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @endisset
                                    @endforeach
                                @else
                                <tr class="bg-secondary">
                                    <td></td>
                                    <td class="font-weight-bold text-center">
                                        {{$date}}
                                    </td>
                                    <td colspan="7" class="font-weight-bold text-uppercase">NO AVAILABLE DATA</td>
                                </tr>
                                @endif
                                
                            @endforeach
                        @endif
                    </tbody>
                </table>
        
            </div>
            <div class="card-footer pb-0">
                @if(!empty($paginatedData))
                    {{$paginatedData->links()}}
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="detail-modal">
        <div class="modal-dialog modal-lg">
            <livewire:reports.mcp.login-detail/>
        </div>
    </div>

    <div class="modal fade" id="schedule-detail-modal">
        <div class="modal-dialog modal-lg">
            <livewire:reports.mcp.schedule-details/>
        </div>
    </div>

    <script>
        window.addEventListener('showDetail', event => {
            $('#detail-modal').modal('show');
        });

        window.addEventListener('showScheduleDetail', event => {
            $('#schedule-detail-modal').modal('show');
        });

        document.addEventListener('livewire:load', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</div>
