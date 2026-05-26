<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\HolidayController;
use App\Models\Holiday;

/**
 * Tests for HolidayController fixes:
 * - Uses config() instead of env() for API key and app URL
 * - Transforms Google Calendar items into FullCalendar format ($calendar_data)
 * - Merges local Holiday records into $calendar_data
 * - Always returns the view (never redirects back on API failure)
 */
class HolidayControllerTest extends TestCase
{
    // ── Config usage ──────────────────────────────────────────────────────────

    /**
     * The controller must not call env() directly for the API key or app URL.
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
     * The controller must read the API key from config('services.google_calendar.api_key').
     */
    public function test_controller_uses_config_for_api_key(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringContainsString(
            "config('services.google_calendar.api_key')",
            $source,
            'HolidayController must use config(\'services.google_calendar.api_key\') for the Google Calendar API key.'
        );

        $this->assertStringContainsString(
            "config('app.url')",
            $source,
            'HolidayController must use config(\'app.url\') instead of env(\'APP_URL\').'
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
     * The view must receive $calendar_data, not $holidays.
     */
    public function test_controller_passes_calendar_data_to_view(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringContainsString(
            'compact(\'calendar_data\')',
            $source,
            'index() must pass $calendar_data to the view, not $holidays.'
        );

        $this->assertStringNotContainsString(
            'compact(\'holidays\')',
            $source,
            'index() must not pass $holidays to the view.'
        );
    }

    // ── Data transformation ───────────────────────────────────────────────────

    /**
     * The controller must map Google Calendar item 'summary' to 'title'
     * for FullCalendar compatibility.
     */
    public function test_controller_maps_summary_to_title(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringContainsString(
            "'title'",
            $source,
            'index() must map Google Calendar item summary to a title key for FullCalendar.'
        );

        $this->assertStringContainsString(
            "['summary']",
            $source,
            'index() must read the summary field from Google Calendar items.'
        );
    }

    // ── Local holidays ────────────────────────────────────────────────────────

    /**
     * The controller must query the Holiday model to include local holidays.
     */
    public function test_controller_includes_local_holidays(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringContainsString(
            'Holiday::',
            $source,
            'index() must query the Holiday model to include locally-added holidays.'
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

        foreach (['year', 'month', 'day', 'title', 'repeat'] as $field) {
            $this->assertContains($field, $fillable, "Holiday model must have '{$field}' in \$fillable.");
        }
    }

    // ── No redirect on API failure ────────────────────────────────────────────

    /**
     * On API failure the controller must still return the view with an empty
     * $calendar_data array, not redirect back (which would loop on the index page).
     */
    public function test_controller_does_not_redirect_back_on_api_failure(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringNotContainsString(
            "return back()",
            $source,
            'index() must not call back() — it would cause a redirect loop on the index page.'
        );
    }

    // ── Caching ───────────────────────────────────────────────────────────────

    /**
     * The controller must use Cache::get / Cache::put with a year-scoped key
     * so successive page loads don't hit the Google Calendar API.
     */
    public function test_controller_caches_google_calendar_response(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertStringContainsString(
            'Cache::get(',
            $source,
            'index() must read from the cache before calling the Google Calendar API.'
        );

        $this->assertStringContainsString(
            'Cache::put(',
            $source,
            'index() must write the API response to the cache on a successful call.'
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
        $source = file_get_contents(app_path('Http/Controllers/HolidayController.php'));

        $this->assertMatchesRegularExpression(
            '/successful\(\).*?Cache::put/s',
            $source,
            'Cache::put must only be called inside the successful() branch, so failures are never cached.'
        );

        $this->assertStringContainsString(
            'if ($googleItems !== null)',
            $source,
            'index() must guard the render loop with a null check to skip rendering when the API failed.'
        );
    }
}
