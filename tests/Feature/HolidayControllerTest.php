<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Holiday;

/**
 * Tests for the holiday module:
 * - HolidayController delegates all Google Calendar / data logic to HolidayIndex Livewire component
 * - HolidayIndex uses config() instead of env() for API key and app URL
 * - HolidayIndex transforms Google Calendar items and merges local Holiday records
 * - HolidayIndex caches API responses and never redirects on failure
 */
class HolidayControllerTest extends TestCase
{
    // ── Controller simplification ─────────────────────────────────────────────

    /**
     * Google Calendar logic moved to HolidayIndex — the controller must not call env() directly.
     */
    public function test_controller_does_not_use_env_directly(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringNotContainsString(
            "env('GOOGLE_CALENDAR_API_KEY')",
            $source,
            'HolidayController must not call env() directly for the Google Calendar API key.'
        );

        $this->assertStringNotContainsString(
            "env('APP_URL')",
            $source,
            'HolidayController must not call env() directly for APP_URL.'
        );
    }

    /**
     * HolidayIndex (Livewire) must read the API key from config(), never env().
     */
    public function test_controller_uses_config_for_api_key(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertStringContainsString(
            "config('services.google_calendar.api_key')",
            $source,
            'HolidayIndex must use config(\'services.google_calendar.api_key\') for the Google Calendar API key.'
        );

        $this->assertStringContainsString(
            "config('app.url')",
            $source,
            'HolidayIndex must use config(\'app.url\') instead of env(\'APP_URL\').'
        );
    }

    // ── services.php ──────────────────────────────────────────────────────────

    /**
     * config/services.php must expose the google_calendar.api_key key.
     */
    public function test_services_config_has_google_calendar_api_key(): void
    {
        $this->assertNotNull(
            config('services.google_calendar.api_key'),
            'config/services.php must define services.google_calendar.api_key bound to GOOGLE_CALENDAR_API_KEY.'
        );
    }

    // ── Variable name ─────────────────────────────────────────────────────────

    /**
     * HolidayIndex must build a $calendarData array (not $holidays) for FullCalendar.
     */
    public function test_controller_passes_calendar_data_to_view(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertStringContainsString(
            'calendarData',
            $source,
            'HolidayIndex must build and pass $calendarData to the view for FullCalendar.'
        );

        $this->assertStringNotContainsString(
            'compact(\'holidays\')',
            $source,
            'HolidayIndex must not pass $holidays directly to the view.'
        );
    }

    // ── Data transformation ───────────────────────────────────────────────────

    /**
     * HolidayIndex must map Google Calendar item 'summary' to 'title' for FullCalendar.
     */
    public function test_controller_maps_summary_to_title(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertStringContainsString(
            "'title'",
            $source,
            'HolidayIndex must map Google Calendar item summary to a title key for FullCalendar.'
        );

        $this->assertStringContainsString(
            "['summary']",
            $source,
            'HolidayIndex must read the summary field from Google Calendar items.'
        );
    }

    // ── Local holidays ────────────────────────────────────────────────────────

    /**
     * HolidayIndex must query the Holiday model to include local holidays.
     */
    public function test_controller_includes_local_holidays(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertStringContainsString(
            'Holiday::',
            $source,
            'HolidayIndex must query the Holiday model to include locally-added holidays.'
        );
    }

    // ── Holiday model ─────────────────────────────────────────────────────────

    /**
     * The Holiday model must declare the expected fillable fields.
     */
    public function test_holiday_model_fillable_fields(): void
    {
        $model = new Holiday();
        $fillable = $model->getFillable();

        foreach (['year', 'month', 'day', 'title', 'repeat', 'is_work_day', 'source'] as $field) {
            $this->assertContains($field, $fillable, "Holiday model must have '{$field}' in \$fillable.");
        }
    }

    // ── No redirect on API failure ────────────────────────────────────────────

    /**
     * HolidayIndex must not redirect back on API failure — it must still render the view.
     */
    public function test_controller_does_not_redirect_back_on_api_failure(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertStringNotContainsString(
            "return back()",
            $source,
            'HolidayIndex must not call back() — it would cause a redirect loop on the index page.'
        );
    }

    // ── Caching ───────────────────────────────────────────────────────────────

    /**
     * HolidayIndex must use Cache::get / Cache::put with a year-scoped key
     * so successive renders don't hit the Google Calendar API.
     */
    public function test_controller_caches_google_calendar_response(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertStringContainsString(
            'Cache::get(',
            $source,
            'HolidayIndex must read from the cache before calling the Google Calendar API.'
        );

        $this->assertStringContainsString(
            'Cache::put(',
            $source,
            'HolidayIndex must write the API response to the cache on a successful call.'
        );

        $this->assertStringContainsString(
            'google_calendar_holidays_',
            $source,
            'The cache key must be scoped to the current year (e.g. "google_calendar_holidays_2026").'
        );

        $this->assertStringContainsString(
            'addDay()',
            $source,
            'The cache TTL must be at least one day so the API is not called on every request.'
        );
    }

    /**
     * On API failure the result must NOT be cached (Cache::put is inside the
     * successful() guard), so the next request retries the API.
     */
    public function test_controller_does_not_cache_api_failure(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/Holidays/HolidayIndex.php'));

        $this->assertMatchesRegularExpression(
            '/successful\(\).*?Cache::put/s',
            $source,
            'Cache::put must only be called inside the successful() branch, so failures are never cached.'
        );

        $this->assertStringContainsString(
            'if ($items === null)',
            $source,
            'HolidayIndex must guard the API call with a null check so cached data is used when available.'
        );
    }
}
