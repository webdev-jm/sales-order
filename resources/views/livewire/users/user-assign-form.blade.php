<div>
    @foreach($accounts as $account)
        <button class="btn {{isset($assigned) && $assigned->contains($account) ? 'btn-success' : 'btn-default'}}" wire:click.prevent="assign({{$account->id}})" wire:loading.attr="disabled">{{$account->account_code}} {{$account->short_name}}</button>
    @endforeach
</div>
