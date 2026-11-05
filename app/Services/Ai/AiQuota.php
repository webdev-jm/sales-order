<?php

namespace App\Services\Ai;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Remembers a provider whose quota or credits are used up, so it is left out of the failover chain until its
 * quota resets instead of being called (and retried, and logged as failed) on every request. A provider that
 * only hit a per-minute rate limit is not remembered.
 */
class AiQuota
{
    private const PREFIX = 'ai-quota-used-up:';

    /**
     * How long a provider without a known reset time (credits used up) is left out before it is tried again.
     */
    public const UNKNOWN_RESET_MINUTES = 60;

    public static function block(string $provider, CarbonInterface $until, string $reason): void
    {
        Cache::put(self::PREFIX . $provider, ['until' => $until->toIso8601String(), 'reason' => $reason], $until);
    }

    /**
     * Why the provider is left out and until when, or null when it can be called.
     *
     * @return array{until: string, reason: string}|null
     */
    public static function blocked(string $provider): ?array
    {
        $blocked = Cache::get(self::PREFIX . $provider);

        return \is_array($blocked) && isset($blocked['until'], $blocked['reason']) ? $blocked : null;
    }

    public static function clear(string $provider): void
    {
        Cache::forget(self::PREFIX . $provider);
    }

    /**
     * Gemini's daily quotas reset at midnight Pacific time.
     */
    public static function nextPacificMidnight(): CarbonInterface
    {
        return now('America/Los_Angeles')->addDay()->startOfDay()->setTimezone(config('app.timezone'));
    }

    public static function unknownReset(): CarbonInterface
    {
        return now()->addMinutes(self::UNKNOWN_RESET_MINUTES);
    }

    /**
     * A reset time as people in the office read it, such as "3:00 PM" or "Nov 5, 3:00 PM".
     */
    public static function resetLabel(CarbonInterface $until): string
    {
        $local = $until->copy()->setTimezone(config('app.timezone'));

        return $local->isSameDay(now()) ? $local->format('g:i A') : $local->format('M j, g:i A');
    }
}
