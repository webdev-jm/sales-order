<div>
    <div class="form-row align-items-end mb-3">
        <div class="col-auto">
            <label for="usage_month" class="small font-weight-bold mb-1">Month</label>
            <input type="month" wire:model.live="month" id="usage_month" class="form-control form-control-sm" style="width:180px;">
        </div>
        <div class="col-auto">
            <label for="usage_status" class="small font-weight-bold mb-1">Status</label>
            <select wire:model.live="status" id="usage_status" class="form-control form-control-sm">
                <option value="">All calls</option>
                <option value="success">Successful</option>
                <option value="failed">Failed</option>
                <option value="cached">From a saved scan</option>
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
                    <span class="progress-description small">
                        <span class="{{ $totals->failed ? 'text-danger' : 'text-muted' }}">{{ number_format($totals->failed) }} failed</span>
                        <span class="text-muted">· {{ number_format($cachedScans) }} from saved scans</span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-exchange-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Input / output tokens</span>
                    <span class="info-box-number">{{ number_format($totals->input_tokens) }} / {{ number_format($totals->output_tokens) }}</span>
                    <span class="progress-description small text-muted">
                        Pages sent / replies, including AI thinking.
                        @if($totals->input_tokens > 0)
                            {{ number_format($totals->cached_input_tokens) }} input ({{ round(100 * $totals->cached_input_tokens / max(1, $totals->input_tokens)) }}%) came from the provider's prompt cache at a lower price.
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Estimated cost</span>
                    <span class="info-box-number">{{ $totals->estimated_cost !== null ? '$' . number_format((float) $totals->estimated_cost, 4) : '—' }}</span>
                    <span class="progress-description small text-muted">
                        @if($totals->unpriced)
                            {{ number_format($totals->unpriced) }} call(s) had no price set
                        @else
                            From the prices on the AI Settings page
                        @endif
                    </span>
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

    <div class="card">
        <div class="card-header"><h3 class="card-title">By feature</h3></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr><th>Feature</th><th class="text-right">Calls</th><th class="text-right">Tokens</th><th class="text-right">Cost</th></tr>
                </thead>
                <tbody>
                    @forelse($byFeature as $row)
                        <tr wire:key="by-feature-{{ $row->feature }}">
                            <td>{{ \App\Models\AiUsageLog::FEATURES[$row->feature] ?? $row->feature }}</td>
                            <td class="text-right">
                                {{ number_format($row->calls) }}
                                @if($row->failed)
                                    <span class="small text-danger">({{ number_format($row->failed) }} failed)</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($row->total_tokens) }}</td>
                            <td class="text-right">{{ $row->estimated_cost !== null ? '$' . number_format((float) $row->estimated_cost, 4) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No AI calls this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">By provider and model</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr><th>Model</th><th class="text-right">Calls</th><th class="text-right">Tokens</th><th class="text-right">Cost</th></tr>
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
                                    <td class="text-right">{{ $row->estimated_cost !== null ? '$' . number_format((float) $row->estimated_cost, 4) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No AI calls this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
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
        <div class="card-header">
            <h3 class="card-title">Token usage per user — {{ $monthLabel }}</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                <thead class="thead-light">
                    <tr>
                        <th>User</th>
                        <th class="text-right">Calls</th>
                        <th class="text-right">Input</th>
                        <th class="text-right">Output</th>
                        <th class="text-right">Total tokens</th>
                        <th class="text-right">Share</th>
                        <th class="text-right">Cost</th>
                        <th>AI models used</th>
                        @if($isCurrentMonth)
                            <th style="min-width:140px;">Today</th>
                        @endif
                        <th>Last used</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byUser as $row)
                        @php
                            $userKey = $row->user_id !== null ? (string) $row->user_id : 'none';
                            $share = (int) $totals->total_tokens > 0 ? round($row->total_tokens / $totals->total_tokens * 100, 1) : 0;
                            $todayPercent = $dailyLimit && $row->tokens_today !== null ? min(100, (int) round($row->tokens_today / $dailyLimit * 100)) : null;
                        @endphp
                        <tr wire:key="by-user-{{ $userKey }}" class="{{ $userId === $userKey ? 'table-active' : '' }}">
                            <td class="text-nowrap">
                                <a href="#" wire:click.prevent="showUser('{{ $userKey }}')" title="Show this user's calls below">
                                    {{ $row->user ? $row->user->firstname . ' ' . $row->user->lastname : 'No user' }}
                                </a>
                            </td>
                            <td class="text-right text-nowrap">
                                {{ number_format($row->calls) }}
                                @if($row->failed)
                                    <span class="small text-danger">({{ number_format($row->failed) }} failed)</span>
                                @endif
                                @if($row->cached_scans)
                                    <span class="small text-muted d-block">+{{ number_format($row->cached_scans) }} saved scans</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($row->input_tokens) }}</td>
                            <td class="text-right">{{ number_format($row->output_tokens) }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($row->total_tokens) }}</td>
                            <td class="text-right">{{ $share }}%</td>
                            <td class="text-right">{{ $row->estimated_cost !== null ? '$' . number_format((float) $row->estimated_cost, 4) : '—' }}</td>
                            <td>
                                @forelse($row->models as $model)
                                    <span class="badge badge-light border mb-1" title="{{ number_format($model->calls) }} call(s)">
                                        {{ $model->provider }} · {{ $model->model }}
                                        <span class="text-muted">{{ number_format($model->total_tokens) }}</span>
                                    </span>
                                @empty
                                    <span class="text-muted small">Saved scans only</span>
                                @endforelse
                            </td>
                            @if($isCurrentMonth)
                                <td>
                                    @if($row->tokens_today === null)
                                        <span class="text-muted">—</span>
                                    @else
                                        {{ number_format($row->tokens_today) }}
                                        @if($todayPercent !== null)
                                            <span class="small text-muted">/ {{ number_format($dailyLimit) }}</span>
                                            <div class="progress progress-xs mb-0">
                                                <div class="progress-bar {{ $todayPercent >= 100 ? 'bg-danger' : ($todayPercent >= 70 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $todayPercent }}%"></div>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            @endif
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($row->last_used_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isCurrentMonth ? 10 : 9 }}" class="text-center text-muted py-3">No AI calls this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($isCurrentMonth)
            <div class="card-footer small text-muted">
                @if($dailyLimit)
                    Each user may use {{ number_format($dailyLimit) }} tokens a day.
                @else
                    No daily limit per user is set.
                @endif
                Saved scans reuse an earlier read and use no tokens. Click a name to see that user's calls.
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-auto">AI calls — {{ $monthLabel }}</h3>
            <select wire:model.live="userId" class="form-control form-control-sm ml-2" style="width:auto;" aria-label="User">
                <option value="">All users</option>
                @foreach($byUser as $row)
                    <option value="{{ $row->user_id ?? 'none' }}">{{ $row->user ? $row->user->firstname . ' ' . $row->user->lastname : 'No user' }}</option>
                @endforeach
            </select>
        </div>
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
                        <th class="text-right">Cost</th>
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
                            <td class="text-right">{{ $log->estimated_cost !== null ? '$' . number_format($log->estimated_cost, 4) : '—' }}</td>
                            <td class="text-right">{{ $log->duration_ms !== null ? number_format($log->duration_ms / 1000, 1) . 's' : '—' }}</td>
                            <td>
                                @if($log->status === \App\Models\AiUsageLog::STATUS_SUCCESS)
                                    <span class="badge badge-success">success</span>
                                @elseif($log->status === \App\Models\AiUsageLog::STATUS_CACHED)
                                    <span class="badge badge-info" title="Read from an earlier scan of the same file, no tokens used">saved scan</span>
                                @else
                                    <span class="badge badge-danger" title="{{ $log->error }}">failed</span>
                                    <span class="small text-muted text-wrap">{{ \Illuminate\Support\Str::limit((string) $log->error, 80) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-3">No AI calls found.</td></tr>
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
