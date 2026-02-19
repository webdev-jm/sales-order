<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                APPROVALS
                <span class="badge badge-{{ $status_arr[$rud->status] ?? 'secondary' }}">
                    {{ strtoupper($rud->status) }}
                </span>
            </h3>
            @if($rud->status == 'submitted')
                <div>
                    <button class="btn btn-sm btn-danger" wire:click.prevent="approve('returned')">RETURN</button>
                    <button class="btn btn-sm btn-success" wire:click.prevent="approve('approved')">APPROVE</button>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <livewire:credit-memo.approvals.history :rud="$rud"/>
                </div>
                <div class="col-lg-6">
                    <livewire:credit-memo.approvals.remarks :rud="$rud"/>
                </div>
            </div>
        </div>
    </div>
</div>
