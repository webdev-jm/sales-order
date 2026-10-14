<div>
    <div class="form-row align-items-end mb-3">
        <div class="col-auto">
            <label for="usage_month" class="small font-weight-bold mb-1">Month</label>
            <input type="month" wire:model="month" id="usage_month" class="form-control form-control-sm" style="width:180px;">
        </div>
        <div class="col-auto">
            <label for="usage_status" class="small font-weight-bold mb-1">Status</label>
            <select wire:model="status" id="usage_status" class="form-control form-control-sm">
                <option value="">All calls</option>
                <option value="success">Successful</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div class="col-auto ml-auto">
            <div wire:loading class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i> Loading…</div>
        </div>
    </div>

    @php
        $used = (int) $totals->total_tokens;
        $percent = $limit ? min(100, (int) round($used / $limit * 100)) : null;
        $barClass = $percent === null ? 'bg-info' : ($percent >= 90 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success'));
    @endphp

    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tokens used — {{ $monthLabel }}</span>
                    <span class="info-box-number">{{ number_format($used) }}</span>
                    @if($limit)
                        <div class="progress"><div class="progress-bar {{ $barClass }}" style="width: {{ $percent }}%"></div></div>
                        <span class="progress-description small">
                            {{ $percent }}% of the {{ number_format($limit) }} monthly limit
                        </span>
                    @else
                        <span class="progress-description small text-muted">No monthly limit set</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-robot"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">AI calls</span>
                    <span class="info-box-number">{{ number_format($totals->calls) }}</span>
                    <span class="progress-description small {{ $totals->failed ? 'text-danger' : 'text-muted' }}">
                        {{ number_format($totals->failed) }} failed
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-file-import"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Input tokens</span>
                    <span class="info-box-number">{{ number_format($totals->input_tokens) }}</span>
                    <span class="progress-description small text-muted">PDF pages and instructions sent</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-file-export"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Output tokens</span>
                    <span class="info-box-number">{{ number_format($totals->output_tokens) }}</span>
                    <span class="progress-description small text-muted">Replies, including AI thinking</span>
                </div>
            </div>
        </div>
    </div>

    @if($isCurrentMonth && $limit && $used >= $limit)
        <div class="alert alert-danger">
            <i class="fas fa-ban mr-1"></i>
            The monthly limit has been reached, so PDF scanning is paused until next month or until the limit is raised in
            @can('settings')
                <a href="{{ route('setting.index') }}">Settings</a>.
            @else
                Settings.
            @endcan
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">By provider and model</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>Model</th><th class="text-right">Calls</th><th class="text-right">Tokens</th></tr>
                        </thead>
                        <tbody>
                            @forelse($byProvider as $row)
                                <tr wire:key="by-provider-{{ $row->provider }}-{{ $row->model }}">
                                    <td>
                                        <span class="badge badge-light">{{ $row->provider }}</span> {{ $row->model }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($row->calls) }}
                                        @if($row->failed)
                                            <span class="small text-danger">({{ number_format($row->failed) }} failed)</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($row->total_tokens) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No AI calls this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">By user</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>User</th><th class="text-right">Calls</th><th class="text-right">Tokens</th></tr>
                        </thead>
                        <tbody>
                            @forelse($byUser as $row)
                                <tr wire:key="by-user-{{ $row->user_id ?? 'none' }}">
                                    <td>{{ $row->user ? $row->user->firstname . ' ' . $row->user->lastname : '—' }}</td>
                                    <td class="text-right">{{ number_format($row->calls) }}</td>
                                    <td class="text-right">{{ number_format($row->total_tokens) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No AI calls this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">By account</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>Account</th><th class="text-right">Calls</th><th class="text-right">Tokens</th></tr>
                        </thead>
                        <tbody>
                            @forelse($byAccount as $row)
                                <tr wire:key="by-account-{{ $row->account_id ?? 'none' }}">
                                    <td>{{ $row->account ? '[' . $row->account->account_code . '] ' . $row->account->account_name : '—' }}</td>
                                    <td class="text-right">{{ number_format($row->calls) }}</td>
                                    <td class="text-right">{{ number_format($row->total_tokens) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No AI calls this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">AI calls — {{ $monthLabel }}</h3></div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-hover mb-0 text-nowrap" style="font-size:0.85rem;">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Account</th>
                        <th>File</th>
                        <th>Provider / Model</th>
                        <th class="text-right">Input</th>
                        <th class="text-right">Output</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr wire:key="ai-log-{{ $log->id }}">
                            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user ? $log->user->firstname . ' ' . $log->user->lastname : '—' }}</td>
                            <td>{{ $log->account->account_code ?? '—' }}</td>
                            <td>{{ $log->reference ?? '—' }}</td>
                            <td><span class="badge badge-light">{{ $log->provider }}</span> {{ $log->model }}</td>
                            <td class="text-right">{{ number_format($log->input_tokens) }}</td>
                            <td class="text-right">{{ number_format($log->output_tokens) }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($log->total_tokens) }}</td>
                            <td class="text-right">{{ $log->duration_ms !== null ? number_format($log->duration_ms / 1000, 1) . 's' : '—' }}</td>
                            <td>
                                @if($log->status === \App\Models\AiUsageLog::STATUS_SUCCESS)
                                    <span class="badge badge-success">success</span>
                                @else
                                    <span class="badge badge-danger" title="{{ $log->error }}">failed</span>
                                    <span class="small text-muted text-wrap">{{ \Illuminate\Support\Str::limit((string) $log->error, 80) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-3">No AI calls found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">{{ $logs->total() }} call(s)</span>
            {{ $logs->links() }}
        </div>
    </div>
</div>
