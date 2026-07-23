<div>
    @if(!empty($active_account))
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <i class="fas fa-store mr-1"></i>
                <b>ACTIVE ACCOUNT:</b>
                <span class="badge badge-primary">[{{$active_account->account_code}}] {{$active_account->short_name}}</span>
                <span class="text-uppercase ml-1">{{$active_account->account_name}}</span>
            </div>
            <button type="button" class="btn btn-sm btn-warning" wire:click.prevent="openSelector" wire:loading.attr="disabled">
                <i class="fas fa-exchange-alt mr-1"></i>
                CHANGE ACCOUNT
            </button>
        </div>
    @else
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <b>NO ACTIVE ACCOUNT.</b>
                Select an account to set as your active account before creating records.
            </div>
            <button type="button" class="btn btn-sm btn-primary" wire:click.prevent="openSelector" wire:loading.attr="disabled">
                <i class="fas fa-check mr-1"></i>
                SELECT ACCOUNT
            </button>
        </div>
    @endif

    <div class="modal fade" id="account-selector-modal" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Select Active Account</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                @if(!empty($confirm_account))
                    <div class="modal-body text-left">
                        <div class="alert alert-warning mb-0">
                            <h5 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Confirm Account Switch</h5>
                            <p class="mb-1">
                                Set <b>[{{$confirm_account->account_code}}] {{$confirm_account->short_name}}</b> as your active account?
                            </p>
                            <p class="mb-0">
                                @if(!empty($active_account))
                                    You are currently on <b>[{{$active_account->account_code}}] {{$active_account->short_name}}</b>.
                                @endif
                                Any sales order or PPU form still in progress will be discarded.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" class="btn btn-default" wire:click.prevent="cancelConfirm">Back</button>
                        <button type="button" class="btn btn-primary" wire:click.prevent="switchAccount" wire:loading.attr="disabled">
                            <i class="fa fa-spinner fa-sm fa-spin mr-1" wire:loading wire:target="switchAccount"></i>
                            YES, SWITCH ACCOUNT
                        </button>
                    </div>
                @else
                    <div class="modal-body text-left">
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" class="form-control" placeholder="Search account code or name" wire:model.debounce.400ms="search">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search" wire:loading.remove wire:target="search"></i>
                                    <i class="fa fa-spinner fa-sm fa-spin" wire:loading wire:target="search"></i>
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm text-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Account</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accounts as $account)
                                    <tr wire:key="account-{{$account->id}}">
                                        <td>{{$account->account_code}}</td>
                                        <td class="text-uppercase">
                                            {{$account->account_name}}
                                            <small class="text-muted d-block">{{$account->short_name}}</small>
                                        </td>
                                        <td class="text-right">
                                            @if(!empty($active_account) && $active_account->id == $account->id)
                                                <span class="badge badge-success">ACTIVE</span>
                                            @else
                                                <button type="button" class="btn btn-xs btn-primary" wire:click.prevent="confirmAccount({{$account->id}})">
                                                    SELECT
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No accounts assigned to you were found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($has_more)
                        <p class="text-muted small mt-2 mb-0">
                            Only the first {{$accounts->count()}} accounts are shown. Use the search to narrow the list.
                        </p>
                        @endif
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('openAccountSelector', function () {
            $('#account-selector-modal').modal('show');
            getActiveAccountLocation();
        });

        window.addEventListener('closeAccountSelector', function () {
            $('#account-selector-modal').modal('hide');
        });

        function getActiveAccountLocation() {
            if (!navigator.geolocation) {
                return;
            }

            navigator.geolocation.getCurrentPosition(function (position) {
                @this.accuracy = position.coords.accuracy.toFixed(3) + ' m';
                @this.longitude = position.coords.longitude;
                @this.latitude = position.coords.latitude;
            }, function () {
                // location is best effort only; the account may still be selected without it
            });
        }
    </script>
</div>
