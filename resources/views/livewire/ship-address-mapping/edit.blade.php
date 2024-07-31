<div>
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Ship Address Mapping</h3>
                <div class="card-tools">
                </div>
            </div>
            <div class="card-body">
        
                <div class="row">
        
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="account_id">Account</label>
                            <select wire:model="account_id" id="account_id" class="form-control{{$errors->has('account_id') ? ' is-invalid' : ''}}">
                                <option value="">SELECT ACCOUNT</option>
                                @foreach($accounts as $account)
                                    <option value="{{$account->id}}">[{{$account->account_code}}] {{$account->short_name}}</option>
                                @endforeach
                            </select>
                            <p class="text-danger">{{$errors->first('account_id')}}</p>
                        </div>
                    </div>
        
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="shipping_address_id">Shipping Address</label>
                            <select wire:model="shipping_address_id" id="shipping_address_id" class="form-control{{$errors->has('shipping_address_id') ? ' is-invalid' : ''}}">
                                <option value="">SELECT SHIPPING ADDRESS</option>
                                @foreach($shipping_addresses as $shipping_address)
                                    <option value="{{$shipping_address->id}}">[{{$shipping_address->address_code}}] {{$shipping_address->ship_to_name}}</option>
                                @endforeach
                            </select>
                            <p class="text-danger">{{$errors->first('shipping_address_id')}}</p>
                        </div>
                    </div>
        
                </div>
    
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reference1">Reference 1</label>
                            <input type="text" wire:model="reference1" class="form-control{{$errors->has('reference1') ? ' is-invalid' : ''}}">
                            <p class="text-danger">{{$errors->first('reference1')}}</p>
                        </div>
                    </div>
    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reference2">Reference 2</label>
                            <input type="text" wire:model="reference2" class="form-control{{$errors->has('reference2') ? ' is-invalid' : ''}}">
                            <p class="text-danger">{{$errors->first('reference2')}}</p>
                        </div>
                    </div>
    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reference3">Reference 3</label>
                            <input type="text" wire:model="reference3" class="form-control{{$errors->has('reference3') ? ' is-invalid' : ''}}">
                            <p class="text-danger">{{$errors->first('reference3')}}</p>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-success" wire:click.prevent="saveShipAddressMapping">
                    <i class="fa fa-pen-alt mr-1"></i>
                    EDIT SHIP ADDRESS MAPPING
                </button>
            </div>
        </div>
    </div>
    
</div>
