<div>

    <div class="row mb-1">

        <div class="col-lg-12">
            <h3 class="font-weight-bold mb-0" style="font-size: 30px">TICKET #: {{$trip_number}}</h3>
        </div>
                
        <div class="col-lg-12">
            <button class="btn {{$type == 'one_way' ? 'btn-info' : 'btn-default'}}" wire:click.prevent="selectType('one_way')">
                One way
            </button>
            <button class="btn {{$type == 'round_trip' ? 'btn-info' : 'btn-default'}}" wire:click.prevent="selectType('round_trip')">
                Round trip
            </button>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <form wire:click.prevent="submitTrip">
                <div class="card card-primary shadow">
                    <div class="card-header">
                        <h3 class="card-title">TRIP</h3>
                        <div class="card-tools">
                            
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="row">

                            <div class="col-lg-12 row mb-2">
                                <div class="col-2">
                                    <div class="input-group">
                                        <input type="number" class="form-control{{$errors->has('passenger') ? ' is-invalid' : ''}}" placeholder="Passenger/s" wire:model="passenger">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-danger mb-0">{{$errors->first('passenger')}}</p>
                                </div>
                                <div class="col-10">
                                    <button id="switch" class="btn btn-info" wire:click="switch">
                                        <i class="fa fa-exchange-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="{{$type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                <div class="form-group">
                                    <label>FROM<i class="fa fa-plane-departure ml-1"></i></label>
                                    <input type="text" class="form-control{{$errors->has('from') ? ' is-invalid' : ''}}" placeholder="from" wire:model="from">
                                    <p class="text-danger mb-0">{{$errors->first('from')}}</p>
                                </div>
                            </div>

                            <div class="{{$type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                <div class="form-group">
                                    <label>TO<i class="fa fa-plane-arrival ml-1"></i></label>
                                    <input type="text" class="form-control{{$errors->has('to') ? ' is-invalid' : ''}}" placeholder="to" wire:model="to">
                                    <p class="text-danger mb-0">{{$errors->first('to')}}</p>
                                </div>
                            </div>

                            <div class="{{$type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}}">
                                <div class="form-group">
                                    <label>DEPARTURE<i class="fa fa-calendar-alt ml-1"></i></label>
                                    <input type="date" class="form-control{{$errors->has('departure') ? ' is-invalid' : ''}}" wire:model="departure">
                                    <p class="text-danger mb-0">{{$errors->first('departure')}}</p>
                                </div>
                            </div>

                            @if($type == 'round_trip')
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>RETURN<i class="fa fa-calendar-alt ml-1"></i></label>
                                        <input type="date" class="form-control{{$errors->has('return') ? ' is-invalid' : ''}}" wire:model="return">
                                        <p class="text-danger mb-0">{{$errors->first('return')}}</p>
                                    </div>
                                </div>
                            @endif

                    </div>
                
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i>
                            Add Trip
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            $('body').on('click', '#switch', function(e) {
                $(this).find('i')
                    .addClass('animate__animated animate__flipInY')
                    .one('animationend', function() {
                        // Remove the animation classes after the animation completes
                        $(this).removeClass('animate__animated animate__flipInY');
                    });
            });
        });
    </script>

</div>
