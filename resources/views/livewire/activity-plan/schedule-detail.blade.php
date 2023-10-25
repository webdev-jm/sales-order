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

                @if(!empty($detail->trip))
                    <div class="card card-primary card-outline mt-2">
                        <div class="card-header">
                            <h3 class="card-title">TRIP DETAILS</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 text-center align-middle">
                                    <strong>TRIP NUMBER</strong>
                                    <br>
                                    <h3 class="font-weight-bold">{{$detail->trip->trip_number}}</h3>
                                </div>
                                <div class="col-lg-6 text-center">
                                    {!! QrCode::generate($detail->trip->trip_number); !!}
                                </div>
                            </div>

                            <hr>

                            <div class="timeline timeline-inverse">
                                {{-- DEPARTURE --}}
                                <div>
                                    <i class="fas fa-plane-departure bg-info"></i>
            
                                    <div class="timeline-item">
                                        <h3 class="timeline-header border-0"><a href="#">DEPARTURE: </a> <strong class="text-uppercase">{{$detail->trip->departure}}</strong>
                                        </h3>
                                    </div>
                                </div>
                                {{-- ARRIVAL --}}
                                <div>
                                    <i class="fas fa-plane-arrival bg-info"></i>
            
                                    <div class="timeline-item">
                                        <h3 class="timeline-header border-0"><a href="#">ARRIVAL: </a> <strong class="text-uppercase">{{$detail->trip->arrival}}</strong>
                                        </h3>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            @if(!empty($detail->trip->reference_number))
                                <div class="row">
                                    <div class="col-lg-12 text-center">
                                        <strong>REFERENCE NUMBER</strong>
                                        <br>
                                        <h3 class="font-weight-bold">{{$detail->trip->reference_number}}</h3>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
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
