<div>
    @if(isset($logged))
        <div class="card mx-2">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col">
                        <h3 class="mb-0">[{{$logged->account->account_code}}] {{$logged->account->short_name}}</h3>
                    </div>
                    <div class="col text-right">
                        <button class="btn btn-danger" wire:click.prevent="loggedForm">Sign Out</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="logged-modal{{$logged->id}}" style="z-index: 1050">
            <div class="modal-dialog modal-lg" style="z-index: 1050">
                <livewire:accounts.account-logged-form :logged="$logged"/>
            </div>
        </div>

        <script>
            window.addEventListener('openLoggedModal{{$logged->id}}', event => {
                $("#logged-modal{{$logged->id}}").modal('show');
            });

            window.addEventListener('closeLoggedModal{{$logged->id}}', event => {
                $("#logged-modal{{$logged->id}}").modal('hide');
            });
        </script>
    @endif
</div>
