<div>
    <div class="small-box {{isset($logged) && $logged->account_id == $account->id ? 'bg-success' : ''}}">
        <div class="inner">
            <h3>{{$account->account_code}}</h3>
            <p class="text-uppercase">{{$account->account_name}}</p>
        </div>
        <div class="icon">
            <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer text-dark get-location" wire:click.prevent="loginModal">Sign In<i class="fas fa-arrow-circle-right ml-2"></i></a>
    </div>

    <div class="modal fade" id="login-modal{{$account->id}}">
        <div class="modal-dialog modal-lg">
            <livewire:accounts.account-login-form :account="$account"/>
        </div>
    </div>

    <script>
        window.addEventListener('openFormModal{{$account->id}}', event => {
            $("#login-modal{{$account->id}}").modal('show');
        });

        window.addEventListener('closeFormModal{{$account->id}}', event => {
            $("#login-modal{{$account->id}}").modal('hide');
        });
    </script>
</div>
