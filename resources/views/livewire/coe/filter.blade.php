<div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">USER FILTER</h3>
                    <div class="card-tools">
                        @if(!empty($user_data))
                        <a href="#" class="text-dark" wire:click.prevent="clearUserFilter"><i class="fa fa-filter"></i></a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="row">
                        @foreach($users as $user)
                        <div class="col-lg-6">
                            @php
                            $btn_class = 'btn-default';
                            if(!empty($user_data) && in_array($user->id, $user_data)) {
                                $btn_class = 'btn-danger';
                            }
                            @endphp
                            <button class="btn {{$btn_class}} btn-block btn-sm mb-1" wire:click.prevent="selectUser({{$user->id}})" wire:loading.attr="disabled">{{$user->name}}</button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ACCOUNT FILTER</h3>
                    <div class="card-tools">
                        @if(!empty($account_data))
                        <a href="#" class="text-dark" wire:click.prevent="clearAccountFilter"><i class="fa fa-filter"></i></a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($accounts as $account)
                        <div class="col-lg-6">
                            @php
                            $btn_class = 'btn-default';
                            if(!empty($account_data) && in_array($account->id, $account_data)) {
                                $btn_class = 'btn-danger';
                            }
                            @endphp
                            <button class="btn {{$btn_class}} btn-block btn-sm mb-1" wire:click.prevent="selectAccount({{$account->id}})">{{$account->short_name}}</button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
