
<div class="modal-content">
    <form wire:submit.prevent="select">
        <div class="modal-header">
            <h4 class="modal-title">Change Address</h4>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" class="form-control" wire:model="search_address" placeholder="Search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default" form="search_form">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-body text-left">

            <div class="row">
                <div class="col-12">
                    {{$shipping_addresses->links()}}
                </div>
            </div>

            <div class="row">

                <div class="col-lg-6">
                    <div class="card address-option {{$shipping_address_id == 'default' ? 'border border-success' : ''}}" data-id="default" data-val="{{$account->account_name}}::{{$account->ship_to_address1}}::{{$account->ship_to_address2}}::{{$account->ship_to_address3}}::{{$account->postal_code}}">
                        <div class="card-header">
                            <h3 class="card-title"><span class="badge badge-secondary">default</span>  {{$account->account_name}}</h3>
                        </div>
                        <div class="card-body">
                            <p>
                                {{$account->ship_to_address1}}<br>
                                {{$account->ship_to_address2}}<br>
                                {{$account->ship_to_address3}}<br>
                                {{$account->postal_code}}
                            </p>
                        </div>
                    </div>
                </div>

                @foreach($shipping_addresses as $shipping_address)
                <div class="col-lg-6">
                    <div class="card address-option {{$shipping_address_id == $shipping_address->id ? 'border border-success' : ''}}" data-id="{{$shipping_address->id}}" data-val="{{$shipping_address->address_code}} - {{$shipping_address->ship_to_name}}::{{$shipping_address->building}}::{{$shipping_address->street}}::{{$shipping_address->city}}::{{$shipping_address->postal}}">
                        <div class="card-header">
                            <h3 class="card-title">{{$shipping_address->address_code}} - {{$shipping_address->ship_to_name}}</h3>
                        </div>
                        <div class="card-body">
                            <p>
                                {{$shipping_address->building}}<br>
                                {{$shipping_address->street}}<br>
                                {{$shipping_address->city}}<br>
                                {{$shipping_address->postal}}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Select</button>
        </div>
    </form>

    <script>
        var id = 'default';
        var val = [
            '{{$account->account_name}}',
            '{{$account->ship_to_address1}}',
            '{{$account->ship_to_address2}}',
            '{{$account->ship_to_address3}}',
            '{{$account->postal_code}}',
        ];

        window.addEventListener('openModal', event => {
            var address_id = $('body').find('#shipping_address_id').val();
            $('body').find('.address-option').each(function() {
                if($(this).data('id') == address_id) {
                    $(this).addClass('border border-success');
                } else {
                    $(this).removeClass('border border-success');
                }
            });
            $("#address-modal").modal('show');
        });

        window.addEventListener('closeModal', event => {
            $("#address-modal").modal('hide');
        });

        window.addEventListener('changeAddress', event => {
            $('body').find('#shipping_address_id').val(id);
            $('body').find('#ship_to_name').val(val[0]);
            $('body').find('#ship_to_address1').val(val[1]);
            $('body').find('#ship_to_address2').val(val[2]);
            $('body').find('#ship_to_address3').val(val[3]);
            $('body').find('#postal_code').val(val[4]);
            
            @this.shipping_address_id = id;
        });

        document.addEventListener('livewire:load', function () {
            $(function() {
                $('body').on('click', '.address-option', function() {
                    $('body').find('.address-option').each(function() {
                        $(this).removeClass('border border-success')
                    });
                    $(this).addClass('border border-success');
                    id = $(this).data('id');
                    val = $(this).data('val').split('::');
                });
            });
        });
    </script>
</div>
