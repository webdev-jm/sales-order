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
        <div class="col-lg-5">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">TRIP</h3>
                    <div class="card-tools">
                        
                    </div>
                </div>
                <div class="card-body">
        
                    <div class="row">
                        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">DATE<i class="fa fa-calendar-alt ml-1"></i></label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
        
                    </div>
        
                    <div class="row">
        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">DEPARTURE<i class="fa fa-plane-departure ml-1"></i></label>
                                <input type="text" class="form-control" placeholder="Departure">
                            </div>
                        </div>
        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">ARRIVAL<i class="fa fa-plane-arrival ml-1"></i></label>
                                <input type="text" class="form-control" placeholder="Arrival">
                            </div>
                        </div>
        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">REFERENCE NUMBER<i class="fa fa-qrcode ml-1"></i></label>
                                <input type="text" class="form-control" placeholder="Reference Number">
                            </div>
                        </div>
        
                    </div>
            
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>

        @if($type == 'round_trip')
            <div class="col-lg-1 text-center align-middle">
                <i class="fa fa-exchange-alt" style="font-size: 100px"></i>
            </div>

            <div class="col-lg-5">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">TRIP</h3>
                        <div class="card-tools">
                            
                        </div>
                    </div>
                    <div class="card-body">
            
                        <div class="row">
                            
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="">DATE<i class="fa fa-calendar-alt ml-1"></i></label>
                                    <input type="date" class="form-control">
                                </div>
                            </div>
            
                        </div>
            
                        <div class="row">
            
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="">DEPARTURE<i class="fa fa-plane-departure ml-1"></i></label>
                                    <input type="text" class="form-control" placeholder="Departure">
                                </div>
                            </div>
            
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="">ARRIVAL<i class="fa fa-plane-arrival ml-1"></i></label>
                                    <input type="text" class="form-control" placeholder="Arrival">
                                </div>
                            </div>
            
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="">REFERENCE NUMBER<i class="fa fa-qrcode ml-1"></i></label>
                                    <input type="text" class="form-control" placeholder="Reference Number">
                                </div>
                            </div>
            
                        </div>
                
                    </div>
                    <div class="card-footer">
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>
