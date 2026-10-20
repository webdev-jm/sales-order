<div>
    @php
        $tabs = ['' => 'All'] + collect(\App\Models\SalesOrderCancelRequest::STATUS_COLORS)->keys()->mapWithKeys(fn ($status) => [$status => ucfirst($status)])->all();
    @endphp

    <div class="card card-outline card-success">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-2 mb-lg-0">
                    <ul class="nav nav-pills">
                        @foreach($tabs as $key => $label)
                            <li class="nav-item">
                                <a href="#" wire:click.prevent="setStatus('{{ $key }}')" class="nav-link py-1 px-2 {{ $status === $key ? 'active' : '' }}">
                                    {{ $label }}
                                    <span class="badge {{ $status === $key ? 'badge-light' : 'badge-secondary' }} ml-1">{{ $key === '' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-4">
                    <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm" placeholder="Search request, SO, PO, SYSPRO SO or salesman">
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th class="pl-3">Request</th>
                        <th>Sales Order</th>
                        <th>SYSPRO SO</th>
                        <th>Account</th>
                        <th>Requested by</th>
                        <th>Filed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $cancelRequest)
                        <tr wire:key="cancel-request-{{ $cancelRequest->id }}">
                            <td class="pl-3 text-nowrap">
                                <a href="{{ route('so-cancel-request.show', $cancelRequest->id) }}" class="font-weight-bold">{{ $cancelRequest->request_number }}</a>
                            </td>
                            <td class="text-nowrap">
                                {{ $cancelRequest->sales_order->control_number ?? '-' }}
                                <small class="d-block text-muted">PO {{ $cancelRequest->sales_order->po_number ?? '-' }}</small>
                            </td>
                            <td>{{ $cancelRequest->sales_order->reference ?? '-' }}</td>
                            <td>{{ $cancelRequest->sales_order->account_login->account->short_name ?? '-' }}</td>
                            <td>{{ $cancelRequest->user?->fullName() ?? '-' }}</td>
                            <td class="text-nowrap">{{ $cancelRequest->created_at->format('M j, Y g:i A') }}</td>
                            <td>
                                <span class="badge badge-{{ $cancelRequest->statusColor() }}">{{ ucfirst($cancelRequest->status) }}</span>
                                @if($cancelRequest->processor && $cancelRequest->status !== \App\Models\SalesOrderCancelRequest::STATUS_PENDING)
                                    <small class="d-block text-muted">{{ $cancelRequest->processor->fullName() }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ $status !== '' || trim($search) !== '' ? 'No cancel requests match these filters.' : 'No cancel requests yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
