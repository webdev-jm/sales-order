<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">TRIP<i wire:loading class="fa fa-spinner fa-spin ml-2 fa-sm"></i></h4>
            <div class="card-tools align-middle">
                <h5 class="font-weight-bold px-2 py-1 mb-0 bg-primary">{{$date ?? ''}}</h5>
            </div>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-12">
                    <h4>TRIP NUMBER: <strong class="d-inline">{{$trip_number}}</strong></h4>
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
                        <label>Reference Number</label>
                        <input type="text" class="form-control{{$errors->has('reference_number') ? ' is-invalid' : ''}}" placeholder="Reference Number" wire:model="reference_number">
                        <p class="text-danger">{{$errors->first('reference_number')}}</p>
                    </div>
                </div>

            </div>

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
