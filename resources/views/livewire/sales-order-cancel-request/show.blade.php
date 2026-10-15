<div>
    @php
        $order = $cancelRequest->sales_order;
        $statuses = \App\Models\SalesOrderCancelRequest::class;
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-{{ $cancelRequest->statusColor() === 'light' ? 'secondary' : $cancelRequest->statusColor() }}">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ $cancelRequest->request_number }}
                        <span class="badge badge-{{ $cancelRequest->statusColor() }} ml-1">{{ ucfirst($cancelRequest->status) }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Requested by</dt>
                        <dd class="col-sm-9">{{ $cancelRequest->user?->fullName() ?? '-' }}</dd>

                        <dt class="col-sm-3">Filed</dt>
                        <dd class="col-sm-9">{{ $cancelRequest->created_at->format('M j, Y g:i A') }}</dd>

                        <dt class="col-sm-3">Reason</dt>
                        <dd class="col-sm-9" style="white-space: pre-line;">{{ $cancelRequest->reason }}</dd>

                        @if($cancelRequest->remarks)
                            <dt class="col-sm-3">SCM remarks</dt>
                            <dd class="col-sm-9" style="white-space: pre-line;">{{ $cancelRequest->remarks }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">History</h3>
                </div>
                <div class="card-body">
                    <div class="timeline mb-0">
                        <div>
                            <i class="fas fa-paper-plane bg-secondary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock mr-1"></i>{{ $cancelRequest->created_at->format('M j, Y g:i A') }}</span>
                                <h3 class="timeline-header border-0">Filed by {{ $cancelRequest->user?->fullName() ?? '-' }}; SCM notified</h3>
                            </div>
                        </div>
                        @if($cancelRequest->in_progress_at)
                            <div>
                                <i class="fas fa-cog bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock mr-1"></i>{{ $cancelRequest->in_progress_at->format('M j, Y g:i A') }}</span>
                                    <h3 class="timeline-header border-0">Marked In progress{{ $cancelRequest->status === $statuses::STATUS_IN_PROGRESS && $cancelRequest->processor ? ' by ' . $cancelRequest->processor->fullName() : '' }}</h3>
                                </div>
                            </div>
                        @endif
                        @if($cancelRequest->closed_at)
                            @php
                                $closedIcon = [
                                    $statuses::STATUS_COMPLETED => 'fa-check bg-success',
                                    $statuses::STATUS_REJECTED  => 'fa-times bg-danger',
                                    $statuses::STATUS_WITHDRAWN => 'fa-undo bg-gray',
                                ][$cancelRequest->status] ?? 'fa-flag bg-gray';
                                $closedBy = $cancelRequest->status === $statuses::STATUS_WITHDRAWN
                                    ? ($cancelRequest->user?->fullName() ?? '-')
                                    : ($cancelRequest->processor?->fullName() ?? '-');
                            @endphp
                            <div>
                                <i class="fas {{ $closedIcon }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock mr-1"></i>{{ $cancelRequest->closed_at->format('M j, Y g:i A') }}</span>
                                    <h3 class="timeline-header border-0">{{ ucfirst($cancelRequest->status) }} by {{ $closedBy }}{{ $cancelRequest->status === $statuses::STATUS_COMPLETED ? '; sales order cancelled' : '' }}</h3>
                                </div>
                            </div>
                        @endif
                        <div class="mb-0" style="min-height: 30px;">
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales order</h3>
                </div>
                <div class="card-body">
                    @if($order)
                        <dl class="mb-0">
                            <dt>Control number</dt>
                            <dd>
                                @can('sales order access')
                                    <a href="{{ route('sales-order.show', $order->id) }}">{{ $order->control_number }}</a>
                                @else
                                    {{ $order->control_number }}
                                @endcan
                                <span class="badge {{ $order->status === 'cancelled' ? 'badge-danger' : 'badge-success' }} ml-1">{{ $order->status }}</span>
                            </dd>
                            <dt>SYSPRO SO</dt>
                            <dd>{{ $order->reference ?: '-' }}</dd>
                            <dt>PO number</dt>
                            <dd>{{ $order->po_number }}</dd>
                            <dt>Account</dt>
                            <dd>{{ $order->account_login->account->account_code ?? '' }} {{ $order->account_login->account->short_name ?? '-' }}</dd>
                            <dt>Order date / ship date</dt>
                            <dd>{{ $order->order_date }} / {{ $order->ship_date }}</dd>
                            <dt>Total less discount</dt>
                            <dd class="mb-0">{{ number_format((float) $order->grand_total, 2) }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">The sales order was deleted.</p>
                    @endif
                </div>
            </div>

            @php
                $canWithdraw = $isRequestor && $cancelRequest->status === $statuses::STATUS_PENDING;
                $canStart    = $canProcess && $cancelRequest->status === $statuses::STATUS_PENDING;
                $canClose    = $canProcess && $cancelRequest->status === $statuses::STATUS_IN_PROGRESS;
            @endphp

            @if($canWithdraw || $canStart || $canClose)
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Next step</h3>
                    </div>
                    <div class="card-body">
                        @error('action')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror

                        @if($canStart)
                            <p class="text-muted">Mark the request In progress, cancel SO {{ $order->reference ?? '' }} in SYSPRO, then complete it here.</p>
                            <button type="button" wire:click="markInProgress" wire:loading.attr="disabled" class="btn btn-info btn-block mb-2">
                                <i class="fas fa-cog mr-1"></i> Mark In Progress
                            </button>
                        @endif

                        @if($canClose)
                            @if($action === null)
                                <p class="text-muted">Once SO {{ $order->reference ?? '' }} is cancelled in SYSPRO, complete the request. This also cancels the order in SMS.</p>
                                <button type="button" wire:click="startComplete" class="btn btn-success btn-block">
                                    <i class="fas fa-check mr-1"></i> Complete
                                </button>
                                <button type="button" wire:click="startReject" class="btn btn-outline-danger btn-block">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                            @else
                                <form wire:submit.prevent="{{ $action }}">
                                    <div class="form-group">
                                        <label for="cancel_remarks">
                                            {{ $action === 'complete' ? 'Remarks (optional)' : 'Reason for rejecting' }}
                                        </label>
                                        <textarea id="cancel_remarks" wire:model.defer="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                                            placeholder="{{ $action === 'complete' ? 'e.g. Cancelled in SYSPRO; stock released' : 'e.g. The order was already delivered on Oct 14' }}"></textarea>
                                        @error('remarks')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">The salesman sees this in the notification.</small>
                                    </div>
                                    <button type="submit" wire:loading.attr="disabled" class="btn btn-block {{ $action === 'complete' ? 'btn-success' : 'btn-danger' }}">
                                        {{ $action === 'complete' ? 'Complete and cancel the order' : 'Reject the request' }}
                                    </button>
                                    <button type="button" wire:click="back" class="btn btn-default btn-block">Back</button>
                                </form>
                            @endif
                        @endif

                        @if($canWithdraw)
                            <button type="button" wire:click="withdraw" onclick="confirm('Withdraw this cancel request? SCM will be told.') || event.stopImmediatePropagation()" wire:loading.attr="disabled" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-undo mr-1"></i> Withdraw Request
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
