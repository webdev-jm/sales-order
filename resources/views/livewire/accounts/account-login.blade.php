<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Accounts</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" class="form-control float-right" placeholder="Search" wire:model="search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default" form="search_form">
                            <i class="fas fa-search" wire:loading.remove></i>
                            <i class="fa fa-spinner fa-sm fa-spin" wire:loading></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
            @foreach($accounts as $account)
                <div class="col-lg-3">
                    <div class="small-box h-90">
                        <div class="inner h-80">
                            <h3>{{$account->account_code}}</h3>
                            <p class="text-uppercase mb-0">{{$account->account_name}}</p>
                            <small class="text-muted">{{$account->short_name}} [{{$account->company->name}}]</small>
                            <br>
                            <button class="btn btn-primary btn-xs" wire:click.prevent="branchModal({{$account->id}})">Branches</button>
                        </div>
                        <div class="icon">
                            <i class="text-lg">{{number_format($count_data[$account->id])}}</i>
                        </div>
                        @can('sales order access')
                        <a href="#" class="small-box-footer text-dark get-location" wire:loading.attr="disabled" wire:click.prevent="loginModal({{$account->id}})">Sign In<i class="fas fa-arrow-circle-right ml-2"></i></a>
                        @endcan
                    </div>
                </div>
            @endforeach
            </div>

        </div>
        <div class="card-footer">
            {{$accounts->links()}}
        </div>
    </div>

    <div class="modal fade" id="login-modal">
        <div class="modal-dialog modal-lg">
            <livewire:accounts.account-login-form/>
        </div>
    </div>

    <div class="modal fade" id="branch-login-modal">
        <div class="modal-dialog modal-xl">
            <livewire:accounts.account-branch-login/>
        </div>
    </div>

    <script>
        window.addEventListener('openFormModal', event => {
            $("#login-modal").modal('show');
        });

        window.addEventListener('closeFormModal', event => {
            $("#login-modal").modal('hide');
        });

        window.addEventListener('openBranchModal', event => {
            $('#branch-login-modal').modal('show');
        });
    </script>
</div>
