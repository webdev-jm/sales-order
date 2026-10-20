<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Records the tokens and estimated cost of every AI call and checks them against the limits set
 * on the Settings page: one for everybody per month and one for each user per day. A blank
 * limit means no limit.
 */
class AiUsageMeter
{
    public function __construct(private AiManager $manager)
    {
    }

    public function monthlyLimit(): ?int
    {
        return $this->limit('ai_monthly_token_limit');
    }

    public function dailyUserLimit(): ?int
    {
        return $this->limit('ai_daily_user_token_limit');
    }

    public function usedInMonth(?CarbonInterface $month = null): int
    {
        $month ??= now();

        return (int) AiUsageLog::query()
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('total_tokens');
    }

    public function usedByUserToday(User $user): int
    {
        return (int) AiUsageLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('total_tokens');
    }

    /**
     * Tokens left this month, or null when there is no limit.
     */
    public function remaining(): ?int
    {
        $limit = $this->monthlyLimit();

        return $limit === null ? null : max(0, $limit - $this->usedInMonth());
    }

    /**
     * Why the user cannot make another AI call now, or null when both limits allow it.
     */
    public function budgetProblem(?User $user): ?string
    {
        if ($this->remaining() === 0) {
            return 'This month\'s AI token limit has been reached. Ask an administrator to raise it on the Settings page.';
        }

        $dailyLimit = $this->dailyUserLimit();

        if ($user !== null && $dailyLimit !== null && $this->usedByUserToday($user) >= $dailyLimit) {
            return 'You have used your AI token limit for today (' . number_format($dailyLimit) . ' tokens). Try again tomorrow, or ask an administrator to raise it on the Settings page.';
        }

        return null;
    }

    /**
     * Logs one AI call. Pass the reply when there is one, so its tokens and cost are counted, or
     * the provider whose call failed before replying.
     *
     * @param array{user_id?: int|null, account_id?: int|null, feature: string, reference?: string|null, status: string, error?: string|null} $attributes
     */
    public function record(array $attributes, AiResponse|AiProviderInterface $source): AiUsageLog
    {
        $response = $source instanceof AiResponse ? $source : null;
        $provider = $response?->provider ?? $source->name();

        return AiUsageLog::create(array_merge([
            'provider'       => $provider,
            'model'          => $response?->model ?? $source->model(),
            'input_tokens'        => $response?->inputTokens ?? 0,
            'cached_input_tokens' => $response?->cachedInputTokens ?? 0,
            'cache_write_tokens'  => $response?->cacheWriteTokens ?? 0,
            'output_tokens'       => $response?->outputTokens ?? 0,
            'total_tokens'        => $response?->totalTokens ?? 0,
            'estimated_cost'      => $this->manager->costOf($provider, $response?->inputTokens ?? 0, $response?->outputTokens ?? 0, $response?->cachedInputTokens ?? 0, $response?->cacheWriteTokens ?? 0),
            'duration_ms'    => $response?->durationMs,
        ], $attributes));
    }

    private function limit(string $column): ?int
    {
        $limit = (int) Setting::query()->orderBy('id')->value($column);

        return $limit > 0 ? $limit : null;
    }
}
