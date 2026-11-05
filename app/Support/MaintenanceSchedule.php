<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * When the maintenance page is on, and the warning before it (config/maintenance.php).
 *
 * MAINTENANCE_MODE turns it on at once. MAINTENANCE_STARTS_AT turns it on by itself at that time:
 * for the MAINTENANCE_WARNING_MINUTES before it, every page counts down to it, so people can save
 * their work. Either way it stays on until the setting is taken out of the .env file.
 */
class MaintenanceSchedule
{
    /** Set on the request by the maintenance middleware for a visitor let in while it is on. */
    public const BYPASS_ATTRIBUTE = 'maintenance_bypassed';

    public function isOn(): bool
    {
        if (config('maintenance.enabled')) {
            return true;
        }

        $startsAt = $this->startsAt();

        return $startsAt !== null && ! $startsAt->isFuture();
    }

    /**
     * The time set in MAINTENANCE_STARTS_AT, or null when none (or no readable time) is set.
     */
    public function startsAt(): ?Carbon
    {
        return $this->parse(config('maintenance.starts_at'));
    }

    /**
     * The time it is expected back, from MAINTENANCE_UNTIL, shown on the maintenance page.
     */
    public function until(): ?Carbon
    {
        return $this->parse(config('maintenance.until'));
    }

    /**
     * Whole seconds until the scheduled start, while the warning is shown; null when there is nothing to warn
     * about (no start set, too early to warn, or already on).
     */
    public function warningSecondsLeft(): ?int
    {
        if (config('maintenance.enabled') || ($startsAt = $this->startsAt()) === null || ! $startsAt->isFuture()) {
            return null;
        }

        $secondsLeft = (int) ceil(now()->diffInMilliseconds($startsAt, true) / 1000);

        return $secondsLeft <= max(0, (int) config('maintenance.warning_minutes', 30)) * 60 ? $secondsLeft : null;
    }

    private function parse(mixed $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }
}
