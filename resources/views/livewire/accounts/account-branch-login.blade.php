<div class="modal-content">
@if(!empty($account))
    <form wire:submit.prevent="login" enctype="multipart/form-data">
        <div class="modal-header">
            <h4 class="modal-title">Login to Branch of <span class="badge badge-primary">[{{$account->account_code}}] {{$account->short_name}}</span></h4>
        </div>
        <div class="modal-body text-left">

            <div class="row">
                @foreach($branches as $branch)
                <div class="col-lg-4">
                    <button type="button" class="btn btn-default btn-block" wire:loading.attr="disabled">[{{$branch->branch_code}}] {{$branch->branch_name}}</button>
                </div>
                @endforeach
            </div>

        </div>
        <div class="modal-footer text-right">
            {{$branches->links()}}
        </div>
    </form>
    
    <script>
    </script>
@endif
</div>
