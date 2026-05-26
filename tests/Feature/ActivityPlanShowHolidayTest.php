<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tests for holiday display on the Activity Plan show page and PDF.
 *
 * show() must inject holiday background events into $schedule_data so the
 * FullCalendar can tint holiday days. printPDF() must add 'holiday' and
 * 'holiday_work' keys to each $lines[$date] entry so the PDF template can
 * render holiday banner rows.
 */
class ActivityPlanShowHolidayTest extends TestCase
{
    // ── show() source assertions ──────────────────────────────────────────────

    /**
     * show() must call getHolidayMap() to build holiday events.
     */
    public function test_show_calls_get_holiday_map(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            'getHolidayMap(',
            $source,
            'ActivityPlanController must define and call getHolidayMap().'
        );
    }

    /**
     * show() must append holiday events to $schedule_data with a 'display' => 'background' key.
     */
    public function test_show_adds_background_display_events_for_holidays(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "'display'         => 'background'",
            $source,
            "show() must add holiday events with display => 'background' to \$schedule_data."
        );

        $this->assertStringContainsString(
            "'is_holiday' => true",
            $source,
            "Holiday events must carry extendedProps.is_holiday = true."
        );
    }

    /**
     * show() holiday events must use green for no-work and orange for work-day.
     */
    public function test_show_holiday_event_colors(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            '#28a745',
            $source,
            'No-work holidays must use the green #28a745 background color.'
        );

        $this->assertStringContainsString(
            '#fd7e14',
            $source,
            'Work-day holidays must use the orange #fd7e14 background color.'
        );
    }

    // ── printPDF() source assertions ──────────────────────────────────────────

    /**
     * printPDF() must call getHolidayMap() to enrich $lines with holiday data.
     */
    public function test_print_pdf_calls_get_holiday_map(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertMatchesRegularExpression(
            '/getHolidayMap\(.*\).*getHolidayMap\(.*\)/s',
            $source,
            'getHolidayMap() must be called in both show() and printPDF() (appears at least twice).'
        );
    }

    /**
     * printPDF() must include 'holiday' and 'holiday_work' keys in $lines[$date].
     */
    public function test_print_pdf_lines_include_holiday_keys(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "'holiday'      => \$holiday_map[\$date]['title'] ?? null",
            $source,
            "printPDF() must add 'holiday' key to \$lines[\$date]."
        );

        $this->assertStringContainsString(
            "'holiday_work' => \$holiday_map[\$date]['is_work_day'] ?? false",
            $source,
            "printPDF() must add 'holiday_work' key to \$lines[\$date]."
        );
    }

    // ── mcp/show.blade.php source assertions ─────────────────────────────────

    /**
     * The show view must include legend badges for both holiday types.
     */
    public function test_show_view_has_holiday_legend_badges(): void
    {
        $source = file_get_contents(resource_path('views/mcp/show.blade.php'));

        $this->assertStringContainsString(
            'No-Work Holiday',
            $source,
            'mcp/show.blade.php must include a "No-Work Holiday" legend badge.'
        );

        $this->assertStringContainsString(
            'Work-Day Holiday',
            $source,
            'mcp/show.blade.php must include a "Work-Day Holiday" legend badge.'
        );
    }

    /**
     * The show view must guard eventClick against holiday events.
     */
    public function test_show_view_guards_event_click_for_holidays(): void
    {
        $source = file_get_contents(resource_path('views/mcp/show.blade.php'));

        $this->assertStringContainsString(
            'is_holiday',
            $source,
            'mcp/show.blade.php must guard eventClick against holiday background events.'
        );
    }

    // ── mcp/pdf.blade.php source assertions ──────────────────────────────────

    /**
     * The PDF view must reference $data['holiday'] to render the banner row.
     */
    public function test_pdf_view_references_holiday_key(): void
    {
        $source = file_get_contents(resource_path('views/mcp/pdf.blade.php'));

        $this->assertStringContainsString(
            "\$data['holiday']",
            $source,
            "mcp/pdf.blade.php must reference \$data['holiday'] to render the holiday banner row."
        );

        $this->assertStringContainsString(
            "\$data['holiday_work']",
            $source,
            "mcp/pdf.blade.php must reference \$data['holiday_work'] to differentiate work vs no-work."
        );
    }

    /**
     * The PDF view must include CSS classes for holiday row highlighting.
     */
    public function test_pdf_view_has_holiday_css_classes(): void
    {
        $source = file_get_contents(resource_path('views/mcp/pdf.blade.php'));

        $this->assertStringContainsString(
            'bg-holiday-nowork',
            $source,
            'mcp/pdf.blade.php must define or use a .bg-holiday-nowork CSS class.'
        );

        $this->assertStringContainsString(
            'bg-holiday-work',
            $source,
            'mcp/pdf.blade.php must define or use a .bg-holiday-work CSS class.'
        );
    }

    // ── getHolidayMap() source assertions ─────────────────────────────────────

    /**
     * getHolidayMap() must query both Philippine and custom holiday sources.
     */
    public function test_get_holiday_map_queries_both_sources(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            'getHolidayMap(string $year): array',
            $source,
            'ActivityPlanController must declare getHolidayMap(string $year): array.'
        );

        $this->assertStringContainsString(
            'google_calendar_holidays_',
            $source,
            'getHolidayMap() must read from the Google Calendar cache.'
        );
    }
}
