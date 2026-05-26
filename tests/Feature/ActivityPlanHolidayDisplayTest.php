<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Livewire\ActivityPlan\Detail2;

/**
 * Tests for holiday display in the activity plan create/edit views.
 *
 * Holidays stored in the local holidays table are loaded in Detail2::setMonthDays()
 * and attached as a 'holiday' key on each date entry so the view can display
 * the holiday name inline on the date row.
 */
class ActivityPlanHolidayDisplayTest extends TestCase
{
    // ── Detail2 component ─────────────────────────────────────────────────────

    /**
     * The Detail2 component must use the Google Calendar API (via cache) for holiday data.
     */
    public function test_detail2_uses_google_calendar_api_for_holidays(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/ActivityPlan/Detail2.php'));

        $this->assertStringContainsString(
            'Cache::get(',
            $source,
            'Detail2 must read the holiday cache before calling the Google Calendar API.'
        );

        $this->assertStringContainsString(
            'google_calendar_holidays_',
            $source,
            'Detail2 must use the same year-scoped cache key as HolidayController.'
        );

        $this->assertStringContainsString(
            "googleapis.com/calendar",
            $source,
            'Detail2 must call the Google Calendar API on a cache miss.'
        );
    }

    /**
     * setMonthDays() must include a 'holiday' key in each date entry.
     */
    public function test_detail2_set_month_days_includes_holiday_key(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/ActivityPlan/Detail2.php'));

        $this->assertStringContainsString(
            "'holiday'",
            $source,
            'Detail2::setMonthDays() must include a "holiday" key in each date entry.'
        );
    }

    /**
     * Detail2 must filter Google Calendar items by the current month prefix.
     */
    public function test_detail2_filters_holidays_by_month(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/ActivityPlan/Detail2.php'));

        $this->assertStringContainsString(
            'monthPrefix',
            $source,
            'Detail2 must filter Google Calendar items by month prefix to only include the current month.'
        );

        $this->assertStringContainsString(
            "str_starts_with",
            $source,
            'Detail2 must use str_starts_with to match holiday dates against the current month.'
        );
    }

    /**
     * The Detail2 public API must expose a 'holiday' key on month_days entries.
     */
    public function test_detail2_month_days_structure_has_holiday_key(): void
    {
        $component = new Detail2();
        $component->year  = '2026';
        $component->month = '06';
        $component->month_days['06']['2026-06-01'] = [
            'day'      => 'Mon',
            'date'     => 'Jun. 01',
            'class'    => 'bg-light',
            'lines'    => [],
            'on_leave' => false,
            'holiday'  => null,
        ];

        $this->assertArrayHasKey(
            'holiday',
            $component->month_days['06']['2026-06-01'],
            'Each date entry in month_days must have a "holiday" key.'
        );
    }

    // ── View ──────────────────────────────────────────────────────────────────

    /**
     * The view must check the holiday key and display the holiday name.
     */
    public function test_detail2_view_renders_holiday_badge(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/activity-plan/detail2.blade.php')
        );

        $this->assertStringContainsString(
            "holiday",
            $source,
            'detail2.blade.php must check the holiday key to display a holiday label.'
        );

        $this->assertStringContainsString(
            "\$day['holiday']",
            $source,
            "detail2.blade.php must reference \$day['holiday'] to show the holiday name."
        );
    }
}
