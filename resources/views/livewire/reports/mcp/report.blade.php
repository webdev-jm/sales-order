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
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">MCP Reports</h3>
                <div class="card-tools">
                    @can('report export')
                    <a href="" class="btn btn-success btn-sm"><i class="fa fa-file-export mr-2"></i>Export</a>
                    <a href="" class="btn btn-info btn-sm"><i class="fa fa-print mr-2"></i>Print</a>
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
                        @foreach($schedule_dates as $schedule_date)
                        <tr class="bg-secondary">
                            <td class="font-weight-bold text-center">{{$schedule_date->date}}</td>
                            <td colspan="6" class="font-weight-bold text-uppercase">{{$schedule_date->user->firstname}} {{$schedule_date->user->lastname}}</td>
                        </tr>
                            @foreach($schedules[$schedule_date->user_id][$schedule_date->date] as $schedule)
                            <tr>
                                <td class="align-middle text-center">{{$schedule->date}}</td>
                                <td class="align-middle">{{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}</td>
                                @if(isset($actuals[$schedule->id]))
                                    <td>
                                        @foreach($actuals[$schedule->id] as $actual)
                                        <p class="mb-0">
                                            {{$actual->latitude}}, {{$actual->longitude}}
                                            <i class="fa fa-info-circle text-primary ml-2" data-toggle="tooltip" data-placement="right" title="{{ \App\Helpers\AppHelper::instance()->getAddress($actual->latitude, $actual->longitude) }}"></i>
                                        </p>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($actuals[$schedule->id] as $actual)
                                        <p class="mb-0">
                                            {{date('H:i:s', strtotime($actual->time_in))}}
                                        </p>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($actuals[$schedule->id] as $actual)
                                        <p class="mb-0">
                                            {{date('H:i:s', strtotime($actual->time_out))}}
                                        </p>
                                        @endforeach
                                    </td>
                                    <td class="text-center align-middle p-0">
                                        @if(!empty($actuals[$schedule->id]->count()))
                                            <i class="fa fa-check text-success"></i>
                                        @else
                                            <i class="fa fa-times text-danger"></i>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
        
            </div>
            <div class="card-footer pb-0">
                {{$schedule_dates->links()}}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</div>
