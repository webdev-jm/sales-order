<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Schedule for [{{$date}}]</h4>
    </div>
    <div class="modal-body">

        @if(!empty($schedule_data))
            <div class="row">
                <div class="col-12">
                    <label>{{$schedule_data->user->firstname}} {{$schedule_data->user->lastname}}</label>
                    <h3>[{{$schedule_data->branch->branch_code}}] {{$schedule_data->branch->branch_name}}</h3>
                </div>

                @if(!empty($action))
                    <div class="col-12">
                        @if($action == 'reschedule-request')
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

                                    <div class="col-12">
                                        <button class="btn btn-primary" type="submit">Submit</button>
                                    </div>
                                </div>
                            </form>
                        @elseif($action == 'sign-in')
                            {{-- Sign In --}}
                            <form action="" wire:submit.prevent="sign_in">
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
                        @endif
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-default" wire:click.prevent="backAction" wire:loading.attr="disabled"><i class="fa fa-arrow-left mr-2"></i>Back</button>
                    </div>
                @else
                    <div class="col-12">
                        {{-- <button class="btn btn-warning" wire:click.prevent="setAction('reschedule-request')"><i class="fa fa-clock mr-2"></i>Reschedule Request</button>
                        <button class="btn btn-danger" wire:click.prevent="setAction('delete-request')"><i class="fa fa-trash-alt mr-2"></i>Delete Request</button> --}}
                        <button class="btn btn-info" wire:click.prevent="setAction('sign-in')"><i class="fa fa-sign-in-alt mr-2"></i>Sign In</button>
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
                    <span class="float-right">{{$schedule->user->firstname}} {{$schedule->user->lastname}}</span>
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

            getLocation();
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
