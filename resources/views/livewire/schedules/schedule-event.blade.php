<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Schedule for [{{$date}}]</h4>
    </div>
    <div class="modal-body">

        @if(!empty($schedule_data))
            <div class="row">
                <div class="col-12">
                    <label class="text-uppercase">{{$schedule_data->user->fullName()}}</label>
                    <h3>[{{$schedule_data->branch->branch_code}}] {{$schedule_data->branch->branch_name}}</h3>
                    <p>
                        <b>Objective</b><br>
                        {{$schedule_data->objective}}
                    </p>
                </div>

                {{-- trip --}}
                @if(!empty($schedule_data->trip))
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    TRIP DETAILS 
                                    @if($schedule_data->trip->status == 'approved')
                                        <span class="badge badge-success">APPROVED</span>
                                    @elseif($schedule_data->trip->source == 'schedule' && empty($schedule_data->trip->status))
                                        <span class="badge badge-secondary">FOR APPROVAL</span>
                                    @endif
                                </h3>
                                <div class="card-tools">
                                    @can('trip print')
                                        @if($schedule_data->trip->status == 'approved')
                                            <a href="{{route('trip.print', $schedule_data->trip->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($schedule_data->trip->trip_number, 'C39', 1.5, 50, 'black', false); !!}
                                        <br>
                                        <strong class="text-muted">TRIP CODE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">{{$schedule_data->trip->trip_number}}</h3>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <strong>TRANSPORTATION TYPE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">
                                            @if($schedule_data->trip->transportation_type == 'AIR')
                                                <i class="fa fa-plane mr-1"></i>
                                            @endif
                                            @if($schedule_data->trip->transportation_type == 'LAND')
                                                <i class="fa fa-car mr-1"></i>
                                            @endif
                                            {{$schedule_data->trip->transportation_type}}
                                        </h3>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                                        <h1 class="font-weight-bold w-100">{{$schedule_data->trip->departure}}</h1>
                                    </div>
                                    <div class="col-lg-2 text-center align-middle">
                                        @if($schedule_data->trip->transportation_type == 'AIR')
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
                                        <h1 class="font-weight-bold w-100">{{$schedule_data->trip->arrival}}</h1>
                                    </div>
                                </div>

                                <hr>
                                
                                <div class="row">
                                    <div class="col-lg-4 text-center">
                                        <strong class="text-muted">NAME</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{$schedule_data->user->fullName()}}</strong>
                                    </div>
                                    <div class="col-lg-4 text-center">
                                        <strong class="text-muted">DATE</strong>
                                        <br>
                                        <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($date))}}</strong>
                                    </div>
                                    @if($schedule_data->trip->status == 'approved')
                                        @if(!empty($schedule_data->trip->reference_number) && $reference_number_edit == 0)
                                            <div class="col-lg-4 text-center">
                                                <strong class="text-muted">
                                                    REFERENCE NUMBER
                                                    @if($schedule_data->trip->transportation_type == 'AIR')
                                                        <a href="#" class="ml-1" wire:click.prevent="editReference">
                                                            <i class="fa fa-pen-alt text-success"></i>
                                                        </a>
                                                    @endif
                                                </strong>
                                                <br>
                                                <strong class="text-uppercase text-lg">{{$schedule_data->trip->reference_number}}</strong>
                                            </div>
                                        @elseif(($schedule_data->trip->transportation_type == 'AIR' && $reference_number_edit == 1) || $schedule_data->trip->transportation_type == 'AIR' && empty($schedule_data->trip->reference_number))
                                            <div class="col-lg-4 text-center">
                                                <strong class="text-muted">
                                                    REFERENCE NUMBER
                                                    <a href="#" class="ml-1" wire:click.prevent="saveEditReference">
                                                        <i class="fa fa-check text-primary"></i>
                                                    </a>
                                                </strong>
                                                <br>
                                                <input type="text" class="form-control" wire:model.lazy="trip_reference_number">
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($action))
                    <div class="col-12">
                        @if($action == 'reschedule-request' && 1==0) {{-- temporary disable --}}
                            {{-- Reschedule --}}
                            <form wire:submit.prevent="submit">
                                <div class="row">
                                    <div class="col-12">
                                        <label>RESCHEDULE</label>
                                    </div>

                                    <div class="col-lg-6 mt-2">
                                        <div class="form-group">
                                            <label for="">Schedule Date</label>
                                            <input type="date" class="form-control @error('reschedule_date') is-invalid @enderror" wire:model.defer="reschedule_date">
                                            @error('reschedule_date')
                                            <p class="text-danger">{{$message}}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="">Remarks</label>
                                            <textarea class="form-control @error('remarks') is-invalid @enderror" rows="4" wire:model.defer="remarks"></textarea>
                                            @error('remarks')
                                            <p class="text-danger">{{$message}}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-warning float-right" type="submit">Reschedule</button>
                                    </div>
                                </div>
                            </form>
                        @elseif($action == 'delete-request' && 1==0) {{-- temporary disable --}}
                            {{-- Delete Request --}}
                            <form wire:submit.prevent="submit">
                                <div class="row">
                                    <div class="col-12">
                                        <label>DELETE REQUEST</label>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="">Remarks</label>
                                            <textarea class="form-control @error('remarks') is-invalid @enderror" rows="4" wire:model.defer="remarks"></textarea>
                                            @error('remarks')
                                            <p class="text-danger">{{$message}}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button class="btn btn-danger float-right" type="submit">Delete Request</button>
                                    </div>
                                </div>
                            </form>
                        @elseif($action == 'sign-in')
                            {{-- Sign In --}}
                            <form action="" wire:submit.prevent="sign_in">

                                {{-- @if(empty($schedule))
                                    <div class="alert alert-warning">
                                        <h5>
                                            <i class="fa fa-ban mr-1"></i>
                                            Warning
                                        </h5>
                                        This branch was not on your schedule for today.
                                    </div>
                                @endif --}}

                                <div class="row my-2">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-secondary" wire:loading.attr="disabled" wire:click.prevent="loadLocation">Reload Location</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Accuracy (m.)</label>
                                            <input type="text" wire:model="accuracy" class="form-control bg-white {{$errors->has('accuracy') ? 'is-invalid' : ''}}" readonly>
                                            <p class="text-danger">{{$errors->first('accuracy')}}</p>
                                        </div>
                                    </div>
                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Longitude</label>
                                            <input type="text" wire:model="longitude" class="form-control bg-white {{$errors->has('longitude') ? 'is-invalid' : ''}}" readonly>
                                            <p class="text-danger">{{$errors->first('longitude')}}</p>
                                        </div>
                                    </div>
                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Latitude</label>
                                            <input type="text" wire:model="latitude" class="form-control bg-white {{$errors->has('latitude') ? 'is-invalid' : ''}}" readonly>
                                            <p class="text-danger">{{$errors->first('latitude')}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary float-right" wire:loading.attr="disabled" type="submit">Sign In</button>
                                </div>
                            </form>
                        @elseif($action == 'add-trip')
                            {{-- add trip --}}
                            <form action="" wire:submit.prevent="submitTrip">
                                <div class="row">
                                    <div class="col-lg-12 text-center align-middle">
                                        {!! DNS1D::getBarcodeSVG($trip_number, 'C39', 1.5, 50, 'black', false); !!}
                                        <br>
                                        <strong class="text-muted">TRIP CODE</strong>
                                        <br>
                                        <h3 class="font-weight-bold">{{$trip_number}}</h3>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Departure <i class="fa fa-plane-departure text-primary"></i></label>
                                            <input type="text" class="form-control{{$errors->has('departure') ? ' is-invalid' : ''}}" placeholder="Departure" wire:model="departure">
                                            <p class="text-danger">{{$errors->first('departure')}}</p>
                                        </div>
                                    </div>
                    
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Arrival <i class="fa fa-plane-arrival text-primary"></i></label>
                                            <input type="text" class="form-control{{$errors->has('arrival') ? ' is-invalid' : ''}}" placeholder="Arrival" wire:model="arrival">
                                            <p class="text-danger">{{$errors->first('arrival')}}</p>
                                        </div>
                                    </div>
                    
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Transportation Type</label>
                                            <select class="form-control{{$errors->has('transportation_type') ? ' is-invalid' : ''}}" wire:model="transportation_type">
                                                <option value="" selected="selected">Select transportation type</option>
                                                @foreach($transportation_types as $type)
                                                    <option value="{{$type}}">{{$type}}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-danger">{{$errors->first('transportation_type')}}</p>
                                        </div>
                                    </div>
                    
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Reference Number</label>
                                            <input type="text" class="form-control{{$errors->has('reference_number') ? ' is-invalid' : ''}}" placeholder="Reference Number"  wire:model="reference_number">
                                            <p class="text-danger">{{$errors->first('reference_number')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-12 text-right">
                                        <button class="btn btn-primary" type="submit">Add Trip</button>
                                    </div>
                    
                                </div>

                            </form>
                        @endif
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-default" wire:click.prevent="backAction" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-2"></i>Back</button>
                    </div>
                @else
                    <div class="col-12">
                        @can('schedule reschedule')
                            {{-- <button class="btn btn-warning my-1" wire:click.prevent="setAction('reschedule-request')"><i class="fa fa-clock mr-2"></i>Reschedule Request</button> --}}
                        @endcan
                        @can('schedule delete request')
                            {{-- <button class="btn btn-danger my-1" wire:click.prevent="setAction('delete-request')"><i class="fa fa-trash-alt mr-2"></i>Delete Request</button> --}}
                        @endcan
                        @if($schedule_data->user_id == auth()->user()->id)
                            @if(empty($schedule_data->trip) && auth()->user()->can('trip create'))
                                <button class="btn btn-primary" wire:click.prevent="setAction('add-trip')">
                                    <i class="fa fa-plane mr-1"></i>
                                    Add Trip
                                </button>
                            @endif
                            <button class="btn btn-info my-1" wire:click.prevent="setAction('sign-in')"><i class="fa fa-sign-in-alt mr-2"></i>Sign In</button>
                        @endif
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-default" wire:click.prevent="back" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-2"></i>Back</button>
                    </div>
                @endif
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    {{$branch_schedules->links()}}
                </div>
            </div>

            @if(!empty($branch_schedules))
            <div class="list-group">
                @foreach($branch_schedules as $schedule)
                <a href="#" class="list-group-item list-group-item-action" wire:click.prevent="viewSchedule({{$schedule->id}})"  wire:loading.attr="disabled">
                    {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                    <span class="float-right">{{$schedule->user->fullName()}}</span>
                </a>
                @endforeach
            </div>
            @endif
        @endif

    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {

            window.addEventListener('reloadLocation', event => {
                getLocation();
            });

            // getLocation();
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        @this.accuracy = position.coords.accuracy.toFixed(3)+' m';
                        @this.longitude = position.coords.longitude;
                        @this.latitude = position.coords.latitude;
                    }, function(error) {
                        @this.accuracy = error.message;
                    });
                } else { 
                    console.log("Geolocation is not supported by this browser.");
                }
            }
        });
    </script>
</div>
