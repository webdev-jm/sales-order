<?php

namespace App\Http\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

trait GlobalTrait {

    /**
     * Retrieve application settings, cached for 60 seconds.
     *
     * The settings row changes rarely (admin-only edits), so a 60-second TTL
     * eliminates the per-request DB hit in every controller constructor while
     * still propagating changes within a minute.
     */
    public function getSettings()
    {
        return Cache::remember('app_settings', 60, fn() => Setting::first());
    }
}
