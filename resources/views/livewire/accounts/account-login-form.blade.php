<div class="modal-content">
    @if(!empty($account))
    @section('plugins.EkkoLightbox', true)

    {{-- The form only appears once an account is picked, so the position is read when Alpine starts it. --}}
    <form wire:submit.prevent="login" enctype="multipart/form-data" x-data x-init="SmsGeolocation.fill($wire)">
        <div class="modal-header">
            <h4 class="modal-title">Login to Account <span class="badge badge-primary">[{{$account->account_code}}] {{$account->short_name}}</span></h4>
            <div class="card-tools">
                <button type="button" class="btn btn-secondary" id="btn-reload-location" x-on:click.prevent="SmsGeolocation.fill($wire)">Reload Location</button>
            </div>
        </div>
        <div class="modal-body text-left">

            @if(!empty($logged_account) && $logged_account->account_id != $account->id)
            <div class="alert alert-warning">
                <h5 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Switching Account</h5>
                <p class="mb-0">
                    You are currently signed in to
                    <b>[{{$logged_account->account->account_code}}] {{$logged_account->account->short_name}}</b>.
                    Signing in here signs you out of it and any sales order or PPU form still in progress will be discarded.
                </p>
            </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Accuracy (m.)</label>
                        <input type="text" wire:model.live="accuracy" class="form-control bg-white {{$errors->has('accuracy') ? 'is-invalid' : ''}}" readonly>
                        <p class="text-danger">{{$errors->first('accuracy')}}</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" wire:model.live="longitude" class="form-control bg-white {{$errors->has('longitude') ? 'is-invalid' : ''}}" readonly>
                        <p class="text-danger">{{$errors->first('longitude')}}</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" wire:model.live="latitude" class="form-control bg-white {{$errors->has('latitude') ? 'is-invalid' : ''}}" readonly>
                        <p class="text-danger">{{$errors->first('latitude')}}</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                {{ (!empty($logged_account) && $logged_account->account_id != $account->id) ? 'Switch Account' : 'Sign In' }}
            </button>
        </div>
    </form>

    @endif

    <script>
        document.addEventListener('livewire:initialized', function () {
            $('body').on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox({
                    alwaysShowClose: true
                });
            });
        });
    </script>
</div>
