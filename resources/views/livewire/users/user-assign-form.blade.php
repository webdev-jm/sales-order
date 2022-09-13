<div>
    <div class="row mb-2">
        <div class="col-12 card-header">
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" wire:model="search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($accounts as $account)
        <div class="col-lg-3 my-1">
            <button class="btn {{isset($assigned) && $assigned->contains($account) ? 'btn-success' : 'btn-default'}}" wire:click.prevent="assign({{$account->id}})" wire:loading.attr="disabled">{{$account->account_code}} {{$account->short_name}}</button>
        </div>
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-12">
            {{$accounts->links()}}
        </div>
    </div>
</div>
