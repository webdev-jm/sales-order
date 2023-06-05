<div>
    @if(isset($logged))
        <div class="card mx-2">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-lg-8 col-xl-10">
                        <h3 class="mb-0">[{{$logged->account->account_code}}] <span>{{$logged->account->short_name}}</span> - {{$logged->account->account_name}}</h3>
                    </div>
                    <div class="col-lg-4 col-xl-2 text-right">
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

    @elseif(isset($logged_branch))
        <div class="card mx-2">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-lg-8 col-xl-10">
                        <h3 class="mb-0">[{{$logged_branch->branch->branch_code}}] <span>{{$logged_branch->branch->branch_name}}</span></h3>
                    </div>
                    @if($sign_out_enabled)
                    <div class="col-lg-4 col-xl-2 text-right">
                        <button class="btn btn-danger" wire:click.prevent="loggedBranchForm">Sign Out</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="modal fade" id="logged-modal{{$logged_branch->id}}" style="z-index: 1050">
            <div class="modal-dialog modal-lg" style="z-index: 1050">
                <livewire:accounts.account-branch-login-form :logged_branch="$logged_branch"/>
            </div>
        </div>

        <script>
            window.addEventListener('openLoggedModal{{$logged_branch->id}}', event => {
                $("#logged-modal{{$logged_branch->id}}").modal('show');
            });

            window.addEventListener('closeLoggedModal{{$logged_branch->id}}', event => {
                $("#logged-modal{{$logged_branch->id}}").modal('hide');
            });
        </script>
    @endif
</div>
