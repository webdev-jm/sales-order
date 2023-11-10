<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Schedule Details</h4>
        </div>
        <div class="modal-body p-0">
            @if(!empty($schedule))
                <ul class="list-group">
                    <li class="list-group-item py-2">
                        <span class="font-weight-bold text-uppercase">
                            {{$schedule->user->fullName()}}
                        </span>
                    </li>
                    <li class="list-group-item py-2">
                        <span class="font-weight-bold text-uppercase">
                            [{{$schedule->branch->account->short_name}}] {{$schedule->branch->branch_code}} - {{$schedule->branch->branch_name}} 
                        </span>
                    </li>
                    <li class="list-group-item py-2">
                        <b>Source:</b>
                        {{$schedule->source}}
                    </li>
                    <li class="list-group-item py-2">
                        <b>Objective:</b>
                        {{$schedule->objective}}
                    </li>
                    {{-- DEVIATION DETAILS --}}
                    @if($schedule->source == 'deviation')
                        @if(!empty($deviation_data))
                        <li class="list-group-item py-2">
                            <b>Reason for Deviation:</b>
                            {{$deviation_data->reason_for_deviation}}
                        </li>
                        @endif
                    @endif
                </ul>
                {{-- APPROVALS --}}
                @if($schedule->source == 'deviation')
                    @if(!empty($deviation_data) && !empty($deviation_data->approvals->count()))
                    <table class="table table-bordered table-sm m-3">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deviation_data->approvals as $approval)
                            <tr>
                                <td class="text-uppercase">{{$approval->user->fullName()}}</td>
                                <td>{{$approval->status}}</td>
                                <td>{{$approval->remarks}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                @endif

                {{-- trip --}}
                @if(!empty($schedule->trip) && $schedule->trip->status == 'approved')
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">TRIP DETAILS</h3>
                                <div class="card-tools">
                                    @can('trip print')
                                        <a href="{{route('trip.print', $schedule->trip->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($schedule->trip->trip_number, 'C39', 1.5, 50, 'black', false); !!}
                                        <br>
                                        <strong class="text-muted">TRIP CODE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">{{$schedule->trip->trip_number}}</h3>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <strong>TRANSPORTATION TYPE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">
                                            @if($schedule->trip->transportation_type == 'AIR')
                                                <i class="fa fa-plane mr-1"></i>
                                            @endif
                                            @if($schedule->trip->transportation_type == 'LAND')
                                                <i class="fa fa-car mr-1"></i>
                                            @endif
                                            {{$schedule->trip->transportation_type}}
                                        </h3>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                                        <h1 class="font-weight-bold w-100">{{$schedule->trip->departure}}</h1>
                                    </div>
                                    <div class="col-lg-2 text-center align-middle">
                                        @if($schedule->trip->transportation_type == 'AIR')
                                        <h1 class="trip-icon">
                                            <i class="fas fa-plane text-primary"></i>
                                        </h1>
                                        @else
                                        <h1 class="trip-icon">
                                            <i class="fas fa-car text-primary"></i>
                                        </h1>
                                        @endif
                                    </div>
                                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                                        <h1 class="font-weight-bold w-100">{{$schedule->trip->arrival}}</h1>
                                    </div>
                                </div>

                                <hr>
                                
                                <div class="row">
                                    <div class="col-lg-4 text-center">
                                        <strong class="text-muted">NAME</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{$schedule->user->fullName()}}</strong>
                                    </div>
                                    <div class="col-lg-4 text-center">
                                        <strong class="text-muted">DATE</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($schedule->date))}}</strong>
                                    </div>
                                    @if(!empty($schedule->trip->reference_number))
                                        <div class="col-lg-4 text-center">
                                            <strong class="text-muted">REFERENCE NUMBER</strong>
                                            <br>
                                            <strong class="text-uppercase text-lg">{{$schedule->trip->reference_number}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>
