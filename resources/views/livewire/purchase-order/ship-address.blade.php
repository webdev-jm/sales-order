<div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Shipping Address</h3>
            <div class="card-tools">
                <input type="text" class="form-control" placeholder="search" wire:model="search">
            </div>
        </div>
        <div class="modal-body">
            <div class="row">
                @foreach($shipping_addresses as $address)
                <div class="col-lg-6">
                    <div class="card card-outline card-primary" wire:click="selectAddressCode({{$address->id}})">
                        <div class="card-header">
                            <h3 class="card-title"><strong>{{$address->address_code}}</strong></h3>
                            @if(!empty($selected_address) && $selected_address->id == $address->id)
                                <div class="card-tools">
                                    <span class="badge badge-primary">SELECTED</span>
                                </div>
                            @endif
                        </div>
                        <div class="card-body py-0">
                            <address>
                                {!! $address->ship_to_name ? '<span>'.$address->ship_to_name.'</span><br>' : '' !!}
                                {!! $address->building ? '<span>'.$address->building.'</span><br>' : '' !!}
                                {!! $address->street ? '<span>'.$address->street.'</span><br>' : '' !!}
                                {!! $address->city ? '<span>'.$address->city.'</span><br>' : '' !!}
                                {!! $address->tin ? '<span>'.$address->tin.'</span><br>' : '' !!}
                                {!! $address->postal ? '<span>'.$address->postal.'</span>' : '' !!}
                            </address>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="col-12">
                    {{$shipping_addresses->links()}}
                </div>
            </div>
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal" wire:click.prevent="cancel">CANCEL</button>
            <button class="btn btn-primary" data-dismiss="modal" wire:click.prevent="setSelectedAddress">SELECT ADDRESS</button>
        </div>
    
    </div>
</div>
