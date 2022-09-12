<div>
    <div class="row">
    @foreach($accounts as $account)
        <div class="col-lg-3">
            <div class="small-box">
                <div class="inner">
                    <h3>{{$account->account_code}}</h3>
                    <p class="text-uppercase">{{$account->account_name}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="#" class="small-box-footer text-dark get-location" wire:click.prevent="loginModal({{$account->id}})">Sign In<i class="fas fa-arrow-circle-right ml-2"></i></a>
            </div>
        </div>
    @endforeach
    </div>
    

    <div class="modal fade" id="login-modal">
        <div class="modal-dialog modal-lg">
            <livewire:accounts.account-login-form/>
        </div>
    </div>

    <script>
        window.addEventListener('openFormModal', event => {
            $("#login-modal").modal('show');
        });

        window.addEventListener('closeFormModal', event => {
            $("#login-modal").modal('hide');
        });
    </script>
</div>
