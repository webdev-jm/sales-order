<div>

    <div class="row mb-1">

        <div class="col-lg-12">
            <h3 class="font-weight-bold mb-0">TICKET #: {{$trip_number}}</h3>
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
        <div class="col-lg-7">
            <div class="card card-primary shadow">
                <div class="card-header">
                    <h3 class="card-title">TRIP</h3>
                    <div class="card-tools">
                        
                    </div>
                </div>
                <div class="card-body">
        
                    <div class="row">

                        <div class="col-12">
                            <label for="">DATE
                                <i class="fa fa-calendar-alt ml-1"></i>
                            </label>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="form-group">
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                        </div>

                        @if($type == 'round_trip')
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        @endif
        
                    </div>
        
                    <div class="row">
        
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">DEPARTURE<i class="fa fa-plane-departure ml-1"></i></label>
                                <input type="text" class="form-control" placeholder="Departure">
                            </div>
                        </div>
        
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">ARRIVAL<i class="fa fa-plane-arrival ml-1"></i></label>
                                <input type="text" class="form-control" placeholder="Arrival">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="no_of_pax">
                                    NO. OF PAX
                                    <i class="fa fa-user ml-1"></i>
                                </label>
                                <input type="number" class="form-control" placeholder="No. of pax">
                            </div>
                        </div>
        
                    </div>
            
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>
    </div>

</div>
