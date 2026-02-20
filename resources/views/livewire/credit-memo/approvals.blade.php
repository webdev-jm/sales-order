<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                APPROVALS
                <span class="badge badge-{{ $status_arr[$creditMemo->status] ?? 'secondary' }}">
                    {{ strtoupper($creditMemo->status) }}
                </span>
            </h3>
            @if($canReview && ($creditMemo->status == 'submitted' || $creditMemo->status == 'rejected'))
                <div class="card-tools">
                    <button class="btn btn-sm btn-warning" wire:click.prevent="approve('returned')">RETURN</button>
                    <button class="btn btn-sm btn-primary" wire:click.prevent="approve('for approval')">FOR APPROVAL</button>
                </div>
            @endif
            @if($canApprove && $creditMemo->status == 'for approval')
                <div class="card-tools">
                    <button class="btn btn-sm btn-danger" wire:click.prevent="approve('rejected')">RETURN</button>
                    <button class="btn btn-sm btn-success" wire:click.prevent="approve('approved')">APPROVE</button>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <livewire:credit-memo.approvals.timeline :creditMemo="$creditMemo"/>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <form action="#" method="post">
                <div class="input-group">
                    <input type="text" placeholder="Type Remarks ..." class="form-control" wire:model="message">
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-primary" wire:click.prevent="saveRemarks">ADD</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
