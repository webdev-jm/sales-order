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
                            <div class="card-tools">
                                @can('trip print')
                                    <a href="{{route('trip.print', $detail->trip->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 text-center align-middle">
                                    {!! DNS1D::getBarcodeSVG($detail->trip->trip_number, 'C39', 1.5, 50, 'black', false); !!}
                                    <br>
                                    <strong class="text-muted">TRIP CODE</strong>
                                    <br>
                                    <h3 class="font-weight-bold">{{$detail->trip->trip_number}}</h3>
                                </div>
                                <div class="col-lg-6 text-center">
                                    <strong>TRANSPORTATION TYPE</strong>
                                    <br>
                                    <h3 class="font-weight-bold">
                                        @if($detail->trip->transportation_type == 'AIR')
                                            <i class="fa fa-plane mr-1"></i>
                                        @endif
                                        @if($detail->trip->transportation_type == 'LAND')
                                            <i class="fa fa-car mr-1"></i>
                                        @endif
                                        {{$detail->trip->transportation_type}}
                                    </h3>
                                </div>
                            </div>

                            <hr>

                            <div class="timeline timeline-inverse">
                                {{-- DEPARTURE --}}
                                <div>
                                    @if($detail->trip->transportation_type == 'AIR')
                                        <i class="fas fa-plane-departure bg-info"></i>
                                    @else
                                        <i class="fas fa-car bg-info"></i>
                                    @endif
            
                                    <div class="timeline-item">
                                        <h3 class="timeline-header border-0"><a href="#">DEPARTURE: </a> <strong class="text-uppercase">{{$detail->trip->departure}}</strong>
                                        </h3>
                                    </div>
                                </div>
                                {{-- ARRIVAL --}}
                                <div>
                                    @if($detail->trip->transportation_type == 'AIR')
                                        <i class="fas fa-plane-arrival bg-info"></i>
                                    @else
                                        <i class="fas fa-car-side bg-info"></i>
                                    @endif
            
                                    <div class="timeline-item">
                                        <h3 class="timeline-header border-0"><a href="#">ARRIVAL: </a> <strong class="text-uppercase">{{$detail->trip->arrival}}</strong>
                                        </h3>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            
                            <div class="row">
                                <div class="col-lg-4 text-center">
                                    <strong class="text-muted">NAME</strong>
                                    <br>
                                    <strong class="text-uppercase text-lg">{{$detail->activity_plan->user->fullName()}}</strong>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <strong class="text-muted">DATE</strong>
                                    <br>
                                    <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($detail->date))}}</strong>
                                </div>
                                @if(!empty($detail->trip->reference_number))
                                    <div class="col-lg-4 text-center">
                                        <strong class="text-muted">REFERENCE NUMBER</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{$detail->trip->reference_number}}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($detail->activity_plan->status == 'approved' && auth()->user()->can('trip approve') && empty($detail->trip->status))
                        <div class="card mt-2">
                            <div class="card-header">
                                <h3 class="card-title">REMARKS</h3>
                            </div>
                            <div class="card-body p-0">
                                <textarea class="form-control border-0" wire:model.lazy="remarks"></textarea>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

        </div>
        <div class="modal-footer text-right">
            @if(!empty($detail->trip) && $detail->trip->status != 'approved' && $detail->activity_plan->status == 'approved' && auth()->user()->can('trip approve'))
                <button type="button" class="btn btn-success" wire:click.prevent="approve({{$detail->trip->id}})" wire:loading.attr="disabled">Approve</button>
            @endif
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
        });
    </script>
</div>
