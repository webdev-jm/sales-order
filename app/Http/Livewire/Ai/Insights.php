<?php

namespace App\Http\Livewire\Ai;

use App\Exceptions\AiUnavailableException;
use App\Models\AiUsageLog;
use App\Services\Ai\AgentRunner;
use App\Services\Ai\Agents\ReportInsights;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiResponse;
use App\Services\Ai\AiUsageMeter;
use App\Services\Ai\SalesFigures;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * "Explain these figures" on the SO Dashboard and the Sales Order report. The same figures are
 * explained once; asking again within the hour reuses the answer without using tokens.
 */
class Insights extends Component
{
    /** Saved answers are reused for this long. */
    private const CACHE_MINUTES = 60;

    /** "so-dashboard" (follows the selected dates) or "so-report" (follows year, month and group). */
    public $source = 'so-dashboard';

    public $year;

    public $month;

    /** @var array<int|string, array<int|string, array<int, int|string>>>|null Days picked on the SO Dashboard */
    public $days;

    public $group_code;

    /** @var array{headline: string, highlights: array<int, string>, watch: array<int, string>}|null */
    public $insight;

    /** The period the shown insight was written for. */
    public $insightPeriod = '';

    public $error = '';

    protected $listeners = ['setBoxDate' => 'setDate'];

    public function mount(string $source = 'so-dashboard', $year = null, $month = null, $group_code = null): void
    {
        $this->source = $source;
        $this->year = $year ?: date('Y');
        $this->month = $month;
        $this->group_code = $group_code;

        if ($source === 'so-dashboard') {
            $this->month = $month ?: date('m');
            $this->days[$this->year][(int) $this->month][] = date('d');
        }
    }

    /**
     * Follows the dates picked on the SO Dashboard.
     */
    public function setDate($year, $month, $days): void
    {
        $this->year = $year;
        $this->month = $month;
        $this->days = $days;
    }

    public function explain(AgentRunner $runner, SalesFigures $figures, AiUsageMeter $meter): void
    {
        abort_unless(auth()->user()->can('ai insights access'), 403);

        $this->error = '';
        [$label, $current, $previous] = $this->periods();

        $data = [
            'report'          => $this->source === 'so-report' ? 'Sales Order report' : 'SO Dashboard',
            'period'          => $label,
            'salesman_group'  => $this->group_code ?: 'all',
            'counted_orders'  => 'finalized orders uploaded to SYSPRO',
            'totals'          => $figures->summary($current),
            'previous_period' => $previous === null ? null : ['label' => $previous['label'], 'totals' => $figures->summary($previous['filters'])],
            'top_accounts'    => $figures->byAccount($current, 10),
            'top_products'    => $figures->topProducts($current, 10, 'sales'),
            'brands'          => $figures->byBrand($current, 10),
        ];

        if ($data['totals']['orders'] === 0) {
            $this->insight = null;
            $this->error = 'There are no finalized, uploaded orders in ' . $label . ' to explain.';

            return;
        }

        $user = auth()->user();
        $cacheKey = 'ai-insights:' . hash('sha256', json_encode($data) . $user->id);

        if (\is_array($saved = Cache::get($cacheKey))) {
            $meter->record(['user_id' => $user->id, 'feature' => AiUsageLog::FEATURE_INSIGHTS, 'reference' => $data['report'] . ': ' . $label, 'status' => AiUsageLog::STATUS_CACHED],
                new AiResponse('', (string) ($saved['provider'] ?? ''), (string) ($saved['model'] ?? ''), 0, 0, 0, 0));
            $this->show($saved['insight'], $label);

            return;
        }

        try {
            $response = $runner->prompt(
                new ReportInsights,
                "Explain these figures:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                AiUsageLog::FEATURE_INSIGHTS,
                $user,
                reference: $data['report'] . ': ' . $label,
            );
        } catch (AiUnavailableException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $insight = [
            'headline'   => trim((string) ($response['headline'] ?? '')),
            'highlights' => array_values(array_filter(array_map('trim', (array) ($response['highlights'] ?? [])))),
            'watch'      => array_values(array_filter(array_map('trim', (array) ($response['watch'] ?? [])))),
        ];

        if ($insight['headline'] === '' && $insight['highlights'] === []) {
            $this->error = 'The AI reply could not be read. Try again.';

            return;
        }

        Cache::put($cacheKey, [
            'insight'  => $insight,
            'provider' => Str::after((string) $response->meta->provider, AiClient::LARAVEL_AI_PREFIX),
            'model'    => $response->meta->model,
        ], now()->addMinutes(self::CACHE_MINUTES));

        $this->show($insight, $label);
    }

    public function dismiss(): void
    {
        $this->reset('insight', 'insightPeriod', 'error');
    }

    public function render(): View
    {
        return view('livewire.ai.insights', ['currentPeriod' => $this->periods()[0]]);
    }

    /**
     * @param array{headline: string, highlights: array<int, string>, watch: array<int, string>} $insight
     */
    private function show(array $insight, string $label): void
    {
        $this->insight = $insight;
        $this->insightPeriod = $label;
    }

    /**
     * The period shown on the page, its filters, and the period before it to compare with.
     *
     * @return array{0: string, 1: array<string, mixed>, 2: array{label: string, filters: array<string, mixed>}|null}
     */
    private function periods(): array
    {
        $group = filled($this->group_code) ? ['group_code' => $this->group_code] : [];
        $dates = $this->source === 'so-dashboard' ? $this->pickedDates() : [];

        if ($dates !== []) {
            $label = \count($dates) === 1 ? Carbon::parse($dates[0])->format('M j, Y') : \count($dates) . ' days in ' . Carbon::parse($dates[0])->format('F Y');
            $previousDates = array_map(fn (string $date): string => Carbon::parse($date)->subMonthNoOverflow()->toDateString(), $dates);

            return [$label, ['dates' => $dates] + $group, ['label' => 'the same days a month earlier', 'filters' => ['dates' => $previousDates] + $group]];
        }

        if (filled($this->year) && filled($this->month)) {
            $start = Carbon::create((int) $this->year, (int) $this->month, 1)->startOfMonth();
            $previous = $start->copy()->subMonthNoOverflow();

            return [
                $start->format('F Y'),
                ['date_from' => $start->toDateString(), 'date_to' => $start->copy()->endOfMonth()->toDateString()] + $group,
                ['label' => $previous->format('F Y'), 'filters' => ['date_from' => $previous->toDateString(), 'date_to' => $previous->copy()->endOfMonth()->toDateString()] + $group],
            ];
        }

        $year = (int) ($this->year ?: date('Y'));

        return [(string) $year, ['date_from' => $year . '-01-01', 'date_to' => $year . '-12-31'] + $group,
            ['label' => (string) ($year - 1), 'filters' => ['date_from' => ($year - 1) . '-01-01', 'date_to' => ($year - 1) . '-12-31'] + $group]];
    }

    /**
     * @return array<int, string>
     */
    private function pickedDates(): array
    {
        $dates = [];

        foreach ((array) $this->days as $year => $months) {
            foreach ((array) $months as $month => $days) {
                foreach ((array) $days as $day) {
                    $dates[] = \sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
                }
            }
        }

        sort($dates);

        return array_values(array_unique($dates));
    }
}
