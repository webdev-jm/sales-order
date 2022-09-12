<div>
    <div class="row">
        @foreach($accounts as $account)
        <div class="col-lg-3 my-1">
            <button class="btn {{isset($assigned) && $assigned->contains($account) ? 'btn-success' : 'btn-default'}}" wire:click.prevent="assign({{$account->id}})" wire:loading.attr="disabled">{{$account->account_code}} {{$account->short_name}}</button>
        </div>
        @endforeach
    </div>
</div>
