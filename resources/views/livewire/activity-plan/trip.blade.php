<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">TRIP<i wire:loading class="fa fa-spinner fa-spin ml-2 fa-sm"></i></h4>
            <div class="card-tools align-middle">
                <h5 class="font-weight-bold px-2 py-1 mb-0 bg-primary">{{$date ?? ''}}</h5>
            </div>
        </div>
        <div class="modal-body">
            @if(!empty($form_error))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <b>NOTE:</b> {{$form_error}}
                        </div>
                    </div>
                </div>
            @enderror

            <div class="row">
                <div class="col-12">
                    <h4>TRIP CODE: <strong class="d-inline">{{$trip_number}}</strong>
                        <button class="btn btn-info btn-xs ml-1" wire:click.prevent="selectTicket">
                            <i class="fa fa-search"></i>
                        </button>
                        @if(!empty($ticket_selected))
                            <button class="btn btn-danger btn-xs" wire:click.prevent="cancelSelect">
                                <i class="fa fa-times mr-1"></i>Remove trip ticket
                            </button>
                        @endif
                    </h4>
                </div>
            </div>

            @if(empty($ticket_select))
                {{-- MANUAL ADD --}}
                <div class="row">

                    <div class="col-12 mb-2">
                        <button class="btn {{$type == 'one_way' ? 'btn-primary' : 'btn-default'}}" wire:click.prevent="changeType('one_way')">
                            ONE WAY
                        </button>
                        <button class="btn {{$type == 'round_trip' ? 'btn-primary' : 'btn-default'}}" wire:click.prevent="changeType('round_trip')">
                            ROUND TRIP
                        </button>
                    </div>

                    <div class="col-lg-12 row mb-2">
                        <div class="col-4">
                            <div class="input-group">
                                <input type="number" class="form-control{{$errors->has('passenger') ? ' is-invalid' : ''}}" placeholder="Passenger/s" wire:model="passenger" {{!empty($ticket_selected) ? 'readonly' : ''}}>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-danger mb-0">{{$errors->first('passenger')}}</p>
                        </div>
                        <div class="col-2">
                            <button id="switch" class="btn btn-info" wire:click="switch">
                                <i class="fa fa-exchange-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>FROM <i class="fa fa-plane-departure text-primary"></i></label>
                            <input type="text" class="form-control{{$errors->has('from') ? ' is-invalid' : ''}}" placeholder="From" wire:model="from" {{!empty($ticket_selected) ? 'readonly' : ''}}>
                            <p class="text-danger">{{$errors->first('from')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>TO <i class="fa fa-plane-arrival text-primary"></i></label>
                            <input type="text" class="form-control{{$errors->has('to') ? ' is-invalid' : ''}}" placeholder="To" wire:model="to" {{!empty($ticket_selected) ? 'readonly' : ''}}>
                            <p class="text-danger">{{$errors->first('to')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="departure">DEPARTURE<i class="fa fa-calendar-alt ml-1"></i></label>
                            <input type="date" class="form-control{{$errors->has('departure') ? ' is-invalid' : ''}}" wire:model="departure" {{!empty($ticket_selected) ? 'readonly' : ''}}>
                            <p class="text-danger">{{$errors->first('departure')}}</p>
                        </div>
                    </div>

                    @if($type == 'round_trip')
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="departure">RETURN<i class="fa fa-calendar-alt ml-1"></i></label>
                                <input type="date" class="form-control{{$errors->has('return') ? ' is-invalid' : ''}}" wire:model="return" {{!empty($ticket_selected) ? 'readonly' : ''}}>
                                <p class="text-danger">{{$errors->first('return')}}</p>
                            </div>
                        </div>
                    @endif

                </div>
            @else
                {{-- SELECT EXISTING --}}
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-secondary btn-sm mb-1" wire:click.prevent="cancelSelect">
                            <i class="fa fa-times mr-1"></i>
                            CANCEL
                        </button>
                    </div>
                    <div class="col-lg-12 table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>TRIP NUMBER</th>
                                    <th class="text-center">FROM</th>
                                    <th class="text-center">TO</th>
                                    <th class="text-center">DEPARTURE</th>
                                    <th class="text-center">RETURN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($trip_tickets))
                                    @foreach($trip_tickets as $trip_ticket)
                                    <tr>
                                        <td>
                                            <a href="#" wire:click.prevent="pickTicket({{$trip_ticket->id}})">
                                                {{$trip_ticket->trip_number}}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{$trip_ticket->from}}
                                        </td>
                                        <td class="text-center">
                                            {{$trip_ticket->to}}
                                        </td>
                                        <td class="text-center">
                                            {{$trip_ticket->departure}}
                                        </td>
                                        <td class="text-center">
                                            {{$trip_ticket->return ?? '-'}}
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="5" class="text-center">
                                        - no data available -
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-primary" wire:click.prevent="updateSession" data-dismiss="modal"><i class="fa fa-save mr-1"></i>SAVE TRIP</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    
        <script>
            document.addEventListener('livewire:load', function () {
            });
        </script>
    </div>
</div>
