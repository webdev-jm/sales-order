<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Schedule Details</h4>
            @if(!empty($detail))<span class="float-right"> {{$detail->date}} </span>@endif
            <span wire:loading class="float-right"><i class="fa fa-spinner fa-spin"></i></span>
        </div>
        <div class="modal-body">
            @if(!empty($detail))
                @if(isset($detail->branch))
                <h5>BRANCH: [{{$detail->branch->branch_code}}] {{$detail->branch->branch_name}}</h5>
                @endif

                <ul class="list-group">
                    @if(!empty($detail->exact_location))
                    <li class="list-group-item">
                        <b>ADDRESS: </b>{{$detail->exact_location}}
                    </li>
                    @endif
                    @if(!empty($detail->activity))
                    <li class="list-group-item">
                        <b>ACTIVITY/PURPOSE: </b>{{$detail->activity}}
                    </li>
                    @endif
                    @if(isset($detail->user) || isset($detail->work_with))
                    <li class="list-group-item">
                        <b>WORK WITH: </b>{{$detail->user ? $detail->user->fullName() : $detail->work_with}}
                    </li>
                    @endif
                </ul>

                @if(!empty($trip_data))
                    @if($source == 'trips')
                        <div class="card card-primary card-outline mt-2">
                            <div class="card-header">
                                <h3 class="card-title">TRIP DETAILS</h3>
                                <div class="card-tools">
                                    @can('trip print')
                                        <a href="{{route('trip.print', $trip_data->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($trip_data->trip_number, 'C39', 1.5, 50, 'black', false) !!}
                                        <br>
                                        <strong class="text-muted">TRIP CODE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">{{$trip_data->trip_number}}</h3>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <strong>TRANSPORTATION TYPE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">
                                            @if($trip_data->transportation_type == 'AIR')
                                                <i class="fa fa-plane mr-1"></i>
                                            @endif
                                            @if($trip_data->transportation_type == 'LAND')
                                                <i class="fa fa-car mr-1"></i>
                                            @endif
                                            {{$trip_data->transportation_type}}
                                        </h3>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
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

                                <div class="row">
                                    <div class="{{$trip_data->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                                        <strong class="text-muted">NAME</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{$trip_data->user->fullName()}}</strong>
                                    </div>
                                    <div class="{{$trip_data->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                                        <strong class="text-muted">DEPARTURE DATE</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip_data->departure))}}</strong>
                                    </div>
                                    @if($trip_data->trip_type == 'round_trip')
                                        <div class="col-lg-3 text-center">
                                            <strong class="text-muted">RETURN DATE</strong>
                                            <br>
                                            <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip_data->return))}}</strong>
                                        </div>
                                    @endif
                                    <div class="{{$trip_data->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                                        <strong class="text-muted">TYPE</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{str_replace('_', ' ', $trip_data->trip_type)}}</strong>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card card-primary card-outline mt-2">
                            <div class="card-header">
                                <h3 class="card-title">TRIP DETAILS</h3>
                                <div class="card-tools">
                                    @can('trip print')
                                        <a href="{{route('trip.print', $trip_data->trip->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($trip_data->trip->trip_number, 'C39', 1.5, 50, 'black', false) !!}
                                        <br>
                                        <strong class="text-muted">TRIP CODE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">{{$trip_data->trip->trip_number}}</h3>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <strong>TRANSPORTATION TYPE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">
                                            @if($trip_data->trip->transportation_type == 'AIR')
                                                <i class="fa fa-plane mr-1"></i>
                                            @endif
                                            @if($trip_data->trip->transportation_type == 'LAND')
                                                <i class="fa fa-car mr-1"></i>
                                            @endif
                                            {{$trip_data->trip->transportation_type}}
                                        </h3>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
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

                                <div class="row">
                                    <div class="{{$trip_data->trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                                        <strong class="text-muted">NAME</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{$trip_data->user->fullName()}}</strong>
                                    </div>
                                    <div class="{{$trip_data->trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                                        <strong class="text-muted">DEPARTURE DATE</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip_data->departure))}}</strong>
                                    </div>
                                    @if($trip_data->trip->trip_type == 'round_trip')
                                        <div class="col-lg-3 text-center">
                                            <strong class="text-muted">RETURN DATE</strong>
                                            <br>
                                            <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip_data->return))}}</strong>
                                        </div>
                                    @endif
                                    <div class="{{$trip_data->trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                                        <strong class="text-muted">TYPE</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{str_replace('_', ' ', $trip_data->trip->trip_type)}}</strong>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
