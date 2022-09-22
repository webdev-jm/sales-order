<div class="d-inline">
    <a href="#" class="badge badge-info ml-2" wire:click="openModal"><i class="fa fa-edit mr-1"></i>Change Address</a>

    <div class="modal fade" id="address-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="select">
                    <div class="modal-header">
                        <h4 class="modal-title">Change Address</h4>
                    </div>
                    <div class="modal-body text-left">

                        <div class="row">

                            <div class="col-lg-6">
                                <div class="card address-option border border-success" data-id="default" data-val="{{$account->account_name}}::{{$account->ship_to_address1}}::{{$account->ship_to_address2}}::{{$account->ship_to_address3}}::{{$account->postal_code}}">
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
                                <div class="card address-option" data-id="{{$shipping_address->id}}" data-val="{{$shipping_address->address_code}} - {{$shipping_address->ship_to_name}}::{{$shipping_address->building}}::{{$shipping_address->street}}::{{$shipping_address->city}}::{{$shipping_address->postal}}">
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
            </div>
        </div>
    </div>

    <script>
        var id = null;
        var val = [];
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
