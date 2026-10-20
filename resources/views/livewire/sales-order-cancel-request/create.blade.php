<div>
    @if($fixedOrder)
        {{-- On the sales order page: the order is already chosen. --}}
        @if($openRequest)
            <div class="callout callout-info">
                <i class="fas fa-ban mr-1"></i>
                Cancel request
                <a href="{{ route('so-cancel-request.show', $openRequest->id) }}">{{ $openRequest->request_number }}</a>
                is <span class="badge badge-{{ $openRequest->statusColor() }}">{{ ucfirst($openRequest->status) }}</span>.
                SCM will cancel this order in SYSPRO and SMS once it is completed.
            </div>
        @elseif($canCreate && $eligibilityError === null)
            @if(!$showForm)
                <div class="text-right mb-3">
                    <button type="button" wire:click="open" class="btn btn-outline-danger">
                        <i class="fas fa-ban mr-1"></i> Request Cancelation
                    </button>
                </div>
            @else
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Request cancelation of {{ $fixedOrder->control_number }}</h3>
                    </div>
                    <form wire:submit.prevent="submit">
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                SCM is notified and cancels the order in SYSPRO (SO {{ $fixedOrder->reference }}).
                                You are notified when they start and when they finish.
                            </p>
                            @error('selectedOrderId')
                                <div class="alert alert-danger py-2">{{ $message }}</div>
                            @enderror
                            <div class="form-group mb-0">
                                <label for="cancel_reason">Reason for cancelation</label>
                                <textarea id="cancel_reason" wire:model="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="e.g. The customer cancelled the PO on Oct 14"></textarea>
                                @error('reason')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" wire:click="close" class="btn btn-default">Cancel</button>
                            <button type="submit" class="btn btn-danger" wire:loading.attr="disabled" wire:target="submit">
                                <i class="fas fa-paper-plane mr-1"></i> Send to SCM
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endif
    @else
        {{-- On the SO Cancel Requests page: the salesman picks the order. --}}
        @if(!$showForm)
            <div class="text-right mb-3">
                <button type="button" wire:click="open" class="btn btn-danger">
                    <i class="fas fa-plus mr-1"></i> New Request
                </button>
            </div>
        @else
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">New cancel request</h3>
                </div>
                <form wire:submit.prevent="submit">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="cancel_search">Sales order</label>
                            @if($selectedOrder)
                                <div class="d-flex align-items-center border rounded p-2">
                                    <div class="flex-grow-1">
                                        <strong>{{ $selectedOrder->control_number }}</strong>
                                        <span class="text-muted">· PO {{ $selectedOrder->po_number }} · SYSPRO {{ $selectedOrder->reference }}</span><br>
                                        <small class="text-muted">{{ $selectedOrder->account_login->account->short_name ?? '' }} · ordered {{ $selectedOrder->order_date }} · ship {{ $selectedOrder->ship_date }}</small>
                                    </div>
                                    <button type="button" wire:click="$set('selectedOrderId', null)" class="btn btn-sm btn-default">Change</button>
                                </div>
                            @else
                                <input type="text" id="cancel_search" wire:model.live.debounce.400ms="search" class="form-control" placeholder="Search by control number, PO number or SYSPRO SO number" autocomplete="off">
                                <small class="form-text text-muted">Only orders already in SYSPRO, in accounts assigned to you, are listed.</small>
                                <div class="list-group mt-2">
                                    @forelse($orders as $order)
                                        <button type="button" wire:key="cancel-order-{{ $order->id }}" wire:click="selectOrder({{ $order->id }})" class="list-group-item list-group-item-action py-2">
                                            <strong>{{ $order->control_number }}</strong>
                                            <span class="text-muted">· PO {{ $order->po_number }} · SYSPRO {{ $order->reference }}</span>
                                            <small class="d-block text-muted">{{ $order->account_login->account->short_name ?? '' }} · ordered {{ $order->order_date }} · ship {{ $order->ship_date }}</small>
                                        </button>
                                    @empty
                                        <div class="list-group-item text-muted py-2">No orders in SYSPRO match{{ $search !== '' ? ' "' . $search . '"' : '' }}.</div>
                                    @endforelse
                                </div>
                            @endif
                            @error('selectedOrderId')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="cancel_reason_new">Reason for cancelation</label>
                            <textarea id="cancel_reason_new" wire:model="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="e.g. The customer cancelled the PO on Oct 14"></textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="button" wire:click="close" class="btn btn-default">Cancel</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled" wire:target="submit">
                            <i class="fas fa-paper-plane mr-1"></i> Send to SCM
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>
