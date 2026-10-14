<?php

namespace App\Http\Livewire\AiUsage;

use App\Models\AiUsageLog;
use App\Services\Ai\AiUsageMeter;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Token usage monitor: how many AI tokens were used in a month, by whom, for which account, and
 * against the monthly limit set on the Settings page.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $month = '';
    public string $status = '';

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

    public function render(): View
    {
        $month = $this->selectedMonth();
        $meter = app(AiUsageMeter::class);

        $totals = $this->monthQuery($month)
            ->selectRaw('COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [AiUsageLog::STATUS_FAILED])
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(total_tokens), 0) as total_tokens')
            ->first();

        $byUser = $this->monthQuery($month)
            ->with('user:id,firstname,lastname')
            ->selectRaw('user_id, COUNT(*) as calls, SUM(total_tokens) as total_tokens')
            ->groupBy('user_id')
            ->orderByDesc('total_tokens')
            ->limit(10)
            ->get();

        $byAccount = $this->monthQuery($month)
            ->with('account:id,account_code,account_name')
            ->selectRaw('account_id, COUNT(*) as calls, SUM(total_tokens) as total_tokens')
            ->groupBy('account_id')
            ->orderByDesc('total_tokens')
            ->limit(10)
            ->get();

        $byProvider = $this->monthQuery($month)
            ->selectRaw('provider, model, COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [AiUsageLog::STATUS_FAILED])
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->groupBy('provider', 'model')
            ->orderByDesc('total_tokens')
            ->get();

        $logs = $this->monthQuery($month)
            ->with(['user:id,firstname,lastname', 'account:id,account_code,account_name'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(25);

        $isCurrentMonth = $month->isSameMonth(now());

        return view('livewire.ai-usage.index', [
            'monthLabel'     => $month->format('F Y'),
            'totals'         => $totals,
            'byUser'         => $byUser,
            'byAccount'      => $byAccount,
            'byProvider'     => $byProvider,
            'logs'           => $logs,
            'limit'          => $meter->monthlyLimit(),
            'isCurrentMonth' => $isCurrentMonth,
        ]);
    }
}
