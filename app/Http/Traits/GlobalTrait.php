<?php

namespace App\Http\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use App\Helpers\UploadDateHelper;

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

    /**
     * Normalize an uploaded cell value into a `Y-m-d` string, or null when the
     * value is empty or cannot be read as a date.
     *
     * Thin wrapper over {@see UploadDateHelper::parse()} so the components and
     * controllers already using this trait keep a method call, while the import
     * classes - which are plain objects - reach the same parser directly.
     */
    public function parseSpreadsheetDate(mixed $value): ?string
    {
        return UploadDateHelper::parse($value);
    }

    /**
     * The message to show for an uploaded date cell, or null when it is fine.
     *
     * @param  string  $label     how the column is named to the user
     * @param  bool    $required  whether an empty cell is itself an error
     */
    public function spreadsheetDateError(mixed $value, string $label, bool $required = true): ?string
    {
        return UploadDateHelper::error($value, $label, $required);
    }
}
