<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Invoice Details</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item p-1">
                                    <strong>Trip Code:</strong> 
                                    <span class="float-right" wire:loading><i class="fa fa-spin fa-spinner"></i></span> 
                                    <span class="float-right" wire:loading.remove>{{ $trip->trip_number ?? '-' }}</span>
                                </li>
                                <li class="list-group-item p-1">
                                    <strong>From:</strong>
                                    <span class="float-right" wire:loading><i class="fa fa-spin fa-spinner"></i></span>
                                    <span class="float-right" wire:loading.remove>{{ $trip->from ?? '-' }}</span>
                                </li>
                                <li class="list-group-item p-1">
                                    <strong>To:</strong> 
                                    <span class="float-right" wire:loading><i class="fa fa-spin fa-spinner"></i></span>
                                    <span class="float-right" wire:loading.remove>{{ $trip->to ?? '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item p-1">
                                    <strong>User:</strong> 
                                    <span class="float-right" wire:loading><i class="fa fa-spin fa-spinner"></i></span>
                                    <span class="float-right" wire:loading.remove>{{ !empty($trip->user) ? $trip->user->fullName() : '-' }}</span>
                                </li>
                                <li class="list-group-item p-1">
                                    <strong>Departure:</strong> 
                                    <span class="float-right" wire:loading><i class="fa fa-spin fa-spinner"></i></span>
                                    <span class="float-right" wire:loading.remove>{{ $trip->departure ?? '-' }}</span>
                                </li>
                                <li class="list-group-item p-1">
                                    <strong>Return:</strong> 
                                    <span class="float-right" wire:loading><i class="fa fa-spin fa-spinner"></i></span>
                                    <span class="float-right" wire:loading.remove>{{ $trip->return ?? '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" class="form-control{{$errors->has('invoice') ? ' is-invalid' : ''}}" id="invoice_number" wire:model="invoice" placeholder="Enter Invoice Number">
                        <small class="text-danger mb-0">{{$errors->first('invoice')}}</small>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <input type="text" class="form-control{{$errors->has('supplier') ? ' is-invalid' : ''}}" id="supplier" wire:model="supplier" placeholder="Enter Supplier Name">
                        <small class="text-danger mb-0">{{$errors->first('supplier')}}</small>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" wire:click="saveInvoice">Save Invoice</button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () { 
        });
    </script>
</div>
