<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Models\AiUsageLog;
use App\Models\Setting;
use Carbon\CarbonInterface;

/**
 * Records the tokens every AI call uses and checks them against the monthly limit set on the
 * Settings page. A blank limit means no limit.
 */
class AiUsageMeter
{
    public function monthlyLimit(): ?int
    {
        $limit = (int) Setting::query()->orderBy('id')->value('ai_monthly_token_limit');

        return $limit > 0 ? $limit : null;
    }

    public function usedInMonth(?CarbonInterface $month = null): int
    {
        $month ??= now();

        return (int) AiUsageLog::query()
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
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

    public function hasBudget(): bool
    {
        $remaining = $this->remaining();

        return $remaining === null || $remaining > 0;
    }

    /**
     * Logs one AI call. Pass the reply when there is one, so its tokens are counted, or the
     * provider whose call failed before replying.
     *
     * @param array{user_id?: int|null, account_id?: int|null, feature: string, reference?: string|null, status: string, error?: string|null} $attributes
     */
    public function record(array $attributes, AiResponse|AiProviderInterface $source): AiUsageLog
    {
        $response = $source instanceof AiResponse ? $source : null;

        return AiUsageLog::create(array_merge([
            'provider'      => $response?->provider ?? $source->name(),
            'model'         => $response?->model ?? $source->model(),
            'input_tokens'  => $response?->inputTokens ?? 0,
            'output_tokens' => $response?->outputTokens ?? 0,
            'total_tokens'  => $response?->totalTokens ?? 0,
            'duration_ms'   => $response?->durationMs,
        ], $attributes));
    }
}
