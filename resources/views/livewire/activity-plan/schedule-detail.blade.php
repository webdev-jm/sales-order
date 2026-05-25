<div>
    <div class="modal-content">
        <div class="modal-header bg-light">
            <h4 class="modal-title"><i class="fas fa-calendar-day mr-2"></i>Schedule Details</h4>
            <div class="d-flex align-items-center ml-auto">
                @if(!empty($detail))
                    <span class="badge badge-secondary mr-2">{{$detail->date}}</span>
                @endif
                <span wire:loading><i class="fa fa-spinner fa-spin"></i></span>
            </div>
        </div>
        <div class="modal-body">
            @if(!empty($detail))
                @if(isset($detail->branch))
                <h5 class="mb-3">
                    <i class="fas fa-building mr-1 text-muted"></i>
                    BRANCH: [{{$detail->branch->branch_code}}] {{$detail->branch->branch_name}}
                </h5>
                @endif

                <ul class="list-group list-group-flush mb-3">
                    @if(!empty($detail->exact_location))
                    <li class="list-group-item px-0">
                        <i class="fas fa-map-marker-alt mr-2 text-danger"></i>
                        <strong>Address:</strong> {{$detail->exact_location}}
                    </li>
                    @endif
                    @if(!empty($detail->activity))
                    <li class="list-group-item px-0">
                        <i class="fas fa-tasks mr-2 text-primary"></i>
                        <strong>Activity / Purpose:</strong> {{$detail->activity}}
                    </li>
                    @endif
                    @if(isset($detail->user) || isset($detail->work_with))
                    <li class="list-group-item px-0">
                        <i class="fas fa-user-friends mr-2 text-success"></i>
                        <strong>Work With:</strong> {{$detail->user ? $detail->user->fullName() : $detail->work_with}}
                    </li>
                    @endif
                </ul>

                @if(!empty($trip_data))
                    @if($source == 'trips')
                        <div class="card card-primary card-outline mt-2">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-route mr-1"></i> TRIP DETAILS</h3>
                                <div class="card-tools">
                                    @can('trip print')
                                        <a href="{{route('trip.print', $trip_data->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-lg-6 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($trip_data->trip_number, 'C39', 1.5, 50, 'black', false) !!}
                                        <div class="text-muted small mt-1">TRIP CODE</div>
                                        <h4 class="font-weight-bold">{{$trip_data->trip_number}}</h4>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <div class="text-muted small">TRANSPORTATION TYPE</div>
                                        <h4 class="font-weight-bold">
                                            @if($trip_data->transportation_type == 'AIR')
                                                <i class="fa fa-plane mr-1"></i>
                                            @endif
                                            @if($trip_data->transportation_type == 'LAND')
                                                <i class="fa fa-car mr-1"></i>
                                            @endif
                                            {{$trip_data->transportation_type}}
                                        </h4>
                                    </div>
                                </div>

                                <hr>

                                <div class="row align-items-center mb-3">
                                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                                        <h1 class="font-weight-bold w-100">{{$trip_data->from}}</h1>
                                    </div>
                                    <div class="col-lg-2 text-center align-middle">
                                        @if($trip_data->transportation_type == 'AIR')
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
                                        <h1 class="font-weight-bold w-100">{{$trip_data->to}}</h1>
                                    </div>
                                </div>

                                <hr>

                                <div class="row text-center">
                                    <div class="{{$trip_data->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                        <div class="text-muted small">NAME</div>
                                        <strong class="text-uppercase">{{$trip_data->user->fullName()}}</strong>
                                    </div>
                                    <div class="{{$trip_data->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                        <div class="text-muted small">DEPARTURE DATE</div>
                                        <strong>{{date('m/d/Y', strtotime($trip_data->departure))}}</strong>
                                    </div>
                                    @if($trip_data->trip_type == 'round_trip')
                                        <div class="col-lg-3">
                                            <div class="text-muted small">RETURN DATE</div>
                                            <strong>{{date('m/d/Y', strtotime($trip_data->return))}}</strong>
                                        </div>
                                    @endif
                                    <div class="{{$trip_data->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                        <div class="text-muted small">TYPE</div>
                                        <strong class="text-uppercase">{{str_replace('_', ' ', $trip_data->trip_type)}}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card card-primary card-outline mt-2">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-route mr-1"></i> TRIP DETAILS</h3>
                                <div class="card-tools">
                                    @can('trip print')
                                        <a href="{{route('trip.print', $trip_data->trip->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-lg-6 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($trip_data->trip->trip_number, 'C39', 1.5, 50, 'black', false) !!}
                                        <div class="text-muted small mt-1">TRIP CODE</div>
                                        <h4 class="font-weight-bold">{{$trip_data->trip->trip_number}}</h4>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <div class="text-muted small">TRANSPORTATION TYPE</div>
                                        <h4 class="font-weight-bold">
                                            @if($trip_data->trip->transportation_type == 'AIR')
                                                <i class="fa fa-plane mr-1"></i>
                                            @endif
                                            @if($trip_data->trip->transportation_type == 'LAND')
                                                <i class="fa fa-car mr-1"></i>
                                            @endif
                                            {{$trip_data->trip->transportation_type}}
                                        </h4>
                                    </div>
                                </div>

                                <hr>

                                <div class="row align-items-center mb-3">
                                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                                        <h1 class="font-weight-bold w-100">{{$trip_data->from}}</h1>
                                    </div>
                                    <div class="col-lg-2 text-center align-middle">
                                        @if($trip_data->trip->transportation_type == 'AIR')
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
                                        <h1 class="font-weight-bold w-100">{{$trip_data->to}}</h1>
                                    </div>
                                </div>

                                <hr>

                                <div class="row text-center">
                                    <div class="{{$trip_data->trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                        <div class="text-muted small">NAME</div>
                                        <strong class="text-uppercase">{{$trip_data->user->fullName()}}</strong>
                                    </div>
                                    <div class="{{$trip_data->trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                        <div class="text-muted small">DEPARTURE DATE</div>
                                        <strong>{{date('m/d/Y', strtotime($trip_data->departure))}}</strong>
                                    </div>
                                    @if($trip_data->trip->trip_type == 'round_trip')
                                        <div class="col-lg-3">
                                            <div class="text-muted small">RETURN DATE</div>
                                            <strong>{{date('m/d/Y', strtotime($trip_data->return))}}</strong>
                                        </div>
                                    @endif
                                    <div class="{{$trip_data->trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                        <div class="text-muted small">TYPE</div>
                                        <strong class="text-uppercase">{{str_replace('_', ' ', $trip_data->trip->trip_type)}}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
