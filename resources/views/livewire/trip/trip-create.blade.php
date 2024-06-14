<div>
    @if($form_errors)
        <div class="row mb-1">
            <div class="col-12">
                <div class="alert alert-warning">
                    <h5>
                        <i class="icon fas fa-exclamation-triangle mr-1"></i>
                         NOTE:
                    </h5>
                    {{$form_errors}}
                </div>
            </div>
        </div>
    @endif

    <div class="row mb-1">
                
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
            <form>
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
                                    <button id="switch" class="btn btn-info" wire:click.prevent="switch">
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

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="purpose">OBJECTIVE</label>
                                    <textarea rows="2" class="form-control{{$errors->has('purpose') ? ' is-invalid' : ''}}" wire:model="purpose" placeholder="Purpose"></textarea>
                                    <p class="text-danger mb-0">{{$errors->first('purpose')}}</p>
                                </div>
                            </div>

                            @if($form_errors)
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="attachment">ATTACHMENT</label>
                                        <input type="file" class="form-control{{$errors->has('attachment') ? ' is-invalid' : ''}}" wire:model="attachment">
                                        <p class="text-danger mb-0">{{$errors->first('attachment')}}</p>
                                    </div>
                                </div>
                            @endif

                        </div>

                        @if($passenger >= 2)
                            @for($i = 2; $i <= $passenger; $i++)
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>PASSENGER<i class="fa fa-user ml-1"></i></label>
                                            <select class="form-control" wire:model="passenger_other.{{$i}}">
                                                <option value=""> - select - </option>
                                                @foreach($users as $user)
                                                    <option value="{{$user->id}}">{{$user->fullName()}}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-danger mb-0">{{$errors->first('passenger_name')}}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>FROM<i class="fa fa-plane-departure ml-1"></i></label>
                                            <input type="text" class="form-control{{$errors->has('from_other') ? ' is-invalid' : ''}}" placeholder="from" wire:model="from_other.{{$i}}">
                                            <p class="text-danger mb-0">{{$errors->first('from_other')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>TO<i class="fa fa-plane-arrival ml-1"></i></label>
                                            <input type="text" class="form-control{{$errors->has('to_other') ? ' is-invalid' : ''}}" placeholder="to" wire:model="to_other.{{$i}}">
                                            <p class="text-danger mb-0">{{$errors->first('to_other')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>DEPARTURE<i class="fa fa-calendar-alt ml-1"></i></label>
                                            <input type="date" class="form-control{{$errors->has('departure_other') ? ' is-invalid' : ''}}" wire:model="departure_other.{{$i}}">
                                            <p class="text-danger mb-0">{{$errors->first('departure_other')}}</p>
                                        </div>
                                    </div>

                                    @if($type == 'round_trip')
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>RETURN<i class="fa fa-calendar-alt ml-1"></i></label>
                                                <input type="date" class="form-control{{$errors->has('return_other') ? ' is-invalid' : ''}}" wire:model="return_other.{{$i}}">
                                                <p class="text-danger mb-0">{{$errors->first('return_other')}}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        @endif
                
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-secondary" wire:click.prevent="submitForm('draft')">
                            <i class="fa fa-save mr-1"></i>
                            Save as Draft
                        </button>
                        <button class="btn bg-indigo" wire:click.prevent="submitForm('submitted')">
                            <i class="fa fa-check mr-1"></i>
                            Submit for Approval
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
