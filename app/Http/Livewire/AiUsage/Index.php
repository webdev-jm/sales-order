<?php

namespace App\Http\Livewire\AiUsage;

use App\Models\AiUsageLog;
use App\Services\Ai\AiUsageMeter;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Token usage monitor: how many AI tokens were used in a month, by whom and with which models,
 * for which account, what they cost by the prices on the AI Settings page, and against the
 * monthly and daily per-user limits set on the Settings page. Scans answered from a saved read are counted apart, since they made no AI call.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $month = '';
    public string $status = '';
    public string $userId = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function updatingMonth(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingUserId(): void
    {
        $this->resetPage();
    }

    /**
     * Shows only this user's calls in the list; an empty string shows everybody's.
     */
    public function showUser(string $userId): void
    {
        $this->userId = $userId;
        $this->resetPage();
    }

    private function selectedMonth(): Carbon
    {
        return preg_match('/^\d{4}-\d{2}$/', $this->month)
            ? Carbon::createFromFormat('Y-m-d', $this->month . '-01')->startOfMonth()
            : now()->startOfMonth();
    }

    private function monthQuery(Carbon $month): Builder
    {
        return AiUsageLog::query()
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
    }

    /**
     * The month's real AI calls, without the scans answered from a saved read.
     */
    private function callQuery(Carbon $month): Builder
    {
        return $this->monthQuery($month)->where('status', '!=', AiUsageLog::STATUS_CACHED);
    }

    /**
     * Every user who used AI in the month: their calls, tokens, cost, saved scans, the models
     * that answered them, and, in the current month, their tokens today.
     *
     * @return Collection<int, AiUsageLog>
     */
    private function usageByUser(Carbon $month): Collection
    {
        $cached = AiUsageLog::STATUS_CACHED;

        $users = $this->monthQuery($month)
            ->with('user:id,firstname,lastname')
            ->select('user_id')
            ->selectRaw('SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as calls', [$cached])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [AiUsageLog::STATUS_FAILED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cached_scans', [$cached])
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(total_tokens), 0) as total_tokens')
            ->selectRaw('SUM(estimated_cost) as estimated_cost')
            ->selectRaw('MAX(created_at) as last_used_at')
            ->groupBy('user_id')
            ->orderByDesc('total_tokens')
            ->get();

        $models = $this->callQuery($month)
            ->select('user_id', 'provider', 'model')
            ->selectRaw('COUNT(*) as calls, COALESCE(SUM(total_tokens), 0) as total_tokens')
            ->groupBy('user_id', 'provider', 'model')
            ->orderByDesc('total_tokens')
            ->get()
            ->groupBy(fn (AiUsageLog $row): string => (string) $row->user_id);

        $today = $month->isSameMonth(now())
            ? AiUsageLog::query()
                ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                ->selectRaw('user_id, COALESCE(SUM(total_tokens), 0) as total_tokens')
                ->groupBy('user_id')
                ->pluck('total_tokens', 'user_id')
            : collect();

        return $users->each(function (AiUsageLog $row) use ($models, $today): void {
            $row->setAttribute('models', $models->get((string) $row->user_id, collect()));
            $row->setAttribute('tokens_today', $row->user_id !== null ? (int) ($today[$row->user_id] ?? 0) : null);
        });
    }

    public function render(): View
    {
        $month = $this->selectedMonth();
        $meter = app(AiUsageMeter::class);

        $totals = $this->callQuery($month)
            ->selectRaw('COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [AiUsageLog::STATUS_FAILED])
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(total_tokens), 0) as total_tokens')
            ->selectRaw('SUM(estimated_cost) as estimated_cost')
            ->selectRaw('SUM(CASE WHEN estimated_cost IS NULL AND total_tokens > 0 THEN 1 ELSE 0 END) as unpriced')
            ->first();

        $cachedScans = $this->monthQuery($month)->where('status', AiUsageLog::STATUS_CACHED)->count();

        $byUser = $this->usageByUser($month);

        $byAccount = $this->callQuery($month)
            ->with('account:id,account_code,account_name')
            ->selectRaw('account_id, COUNT(*) as calls, SUM(total_tokens) as total_tokens')
            ->groupBy('account_id')
            ->orderByDesc('total_tokens')
            ->limit(10)
            ->get();

        $byProvider = $this->callQuery($month)
            ->selectRaw('provider, model, COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [AiUsageLog::STATUS_FAILED])
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw('SUM(estimated_cost) as estimated_cost')
            ->groupBy('provider', 'model')
            ->orderByDesc('total_tokens')
            ->get();

        $byFeature = $this->callQuery($month)
            ->selectRaw('feature, COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [AiUsageLog::STATUS_FAILED])
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw('SUM(estimated_cost) as estimated_cost')
            ->groupBy('feature')
            ->orderByDesc('total_tokens')
            ->get();

        $logs = $this->monthQuery($month)
            ->with(['user:id,firstname,lastname', 'account:id,account_code,account_name'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->userId === 'none', fn ($q) => $q->whereNull('user_id'))
            ->when(ctype_digit($this->userId), fn ($q) => $q->where('user_id', (int) $this->userId))
            ->latest('id')
            ->paginate(25);

        $isCurrentMonth = $month->isSameMonth(now());

        return view('livewire.ai-usage.index', [
            'monthLabel'     => $month->format('F Y'),
            'totals'         => $totals,
            'cachedScans'    => $cachedScans,
            'byUser'         => $byUser,
            'byAccount'      => $byAccount,
            'byProvider'     => $byProvider,
            'byFeature'      => $byFeature,
            'logs'           => $logs,
            'limit'          => $meter->monthlyLimit(),
            'dailyLimit'     => $meter->dailyUserLimit(),
            'isCurrentMonth' => $isCurrentMonth,
        ]);
    }
}
