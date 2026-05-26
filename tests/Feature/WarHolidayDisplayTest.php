<?php

namespace Tests\Feature;

use App\Http\Livewire\War\WarForm;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for holiday display in the Weekly Activity Report (WAR).
 *
 * Non-work holidays must appear as a HOLIDAY badge on each matching day row
 * in the show view, PDF export, and the create/edit Livewire form.
 * Work-day holidays must NOT be shown.
 */
class WarHolidayDisplayTest extends TestCase
{
    use RefreshDatabase;

    // ── WeeklyActivityReportController ───────────────────────────────────────

    public function test_controller_has_build_holiday_dates_method(): void
    {
        $reflection = new \ReflectionClass(\App\Http\Controllers\WeeklyActivityReportController::class);

        $this->assertTrue(
            $reflection->hasMethod('buildHolidayDates'),
            'WeeklyActivityReportController must have a buildHolidayDates() method.'
        );
    }

    public function test_controller_passes_holiday_dates_to_show_view(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/WeeklyActivityReportController.php')
        );

        $this->assertStringContainsString(
            'holiday_dates',
            $source,
            'WeeklyActivityReportController::show() must pass holiday_dates to the view.'
        );
    }

    public function test_controller_passes_holiday_dates_to_pdf_view(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/WeeklyActivityReportController.php')
        );

        $this->assertStringContainsString(
            "'holiday_dates'",
            $source,
            'WeeklyActivityReportController::printPDF() must pass holiday_dates to the PDF view.'
        );
    }

    public function test_controller_uses_shared_google_calendar_cache_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/WeeklyActivityReportController.php')
        );

        $this->assertStringContainsString(
            'google_calendar_holidays_',
            $source,
            'buildHolidayDates() must use the shared google_calendar_holidays_ cache key.'
        );
    }

    // ── war/show.blade.php ────────────────────────────────────────────────────

    public function test_show_view_renders_holiday_badge(): void
    {
        $source = file_get_contents(resource_path('views/war/show.blade.php'));

        $this->assertStringContainsString(
            '$holiday_dates',
            $source,
            'war/show.blade.php must check $holiday_dates for each day.'
        );

        $this->assertStringContainsString(
            'HOLIDAY',
            $source,
            'war/show.blade.php must display a HOLIDAY label.'
        );
    }

    // ── war/pdf.blade.php ─────────────────────────────────────────────────────

    public function test_pdf_view_renders_holiday_row(): void
    {
        $source = file_get_contents(resource_path('views/war/pdf.blade.php'));

        $this->assertStringContainsString(
            '$holiday_dates',
            $source,
            'war/pdf.blade.php must check $holiday_dates for each day.'
        );

        $this->assertStringContainsString(
            'HOLIDAY',
            $source,
            'war/pdf.blade.php must display a HOLIDAY label.'
        );
    }

    // ── WarForm Livewire component ────────────────────────────────────────────

    public function test_war_form_component_has_build_holiday_map_method(): void
    {
        $reflection = new \ReflectionClass(WarForm::class);

        $this->assertTrue(
            $reflection->hasMethod('buildHolidayMap'),
            'WarForm must have a buildHolidayMap() method.'
        );
    }

    public function test_war_form_area_lines_include_holiday_key(): void
    {
        $source = file_get_contents(app_path('Http/Livewire/War/WarForm.php'));

        $this->assertStringContainsString(
            "'holiday'",
            $source,
            'WarForm::changeDate() must set a holiday key in each area_lines entry.'
        );
    }

    // ── war-form.blade.php ────────────────────────────────────────────────────

    public function test_war_form_view_renders_holiday_badge(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/war/war-form.blade.php')
        );

        $this->assertStringContainsString(
            "\$line['holiday']",
            $source,
            'war-form.blade.php must check $line[\'holiday\'] for each day.'
        );

        $this->assertStringContainsString(
            'HOLIDAY',
            $source,
            'war-form.blade.php must display a HOLIDAY label.'
        );
    }

    // ── Livewire functional tests ─────────────────────────────────────────────

    public function test_war_form_area_lines_contain_holiday_title_for_matching_date(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->toDateString();

        Holiday::create([
            'year'        => Carbon::now()->year,
            'month'       => Carbon::now()->month,
            'day'         => Carbon::now()->day,
            'title'       => 'Independence Day',
            'repeat'      => false,
            'is_work_day' => false,
            'source'      => 'custom',
        ]);

        Cache::put('google_calendar_holidays_' . Carbon::now()->year, [], now()->addDay());

        $component = Livewire::actingAs($user)->test(WarForm::class, [
            'user_id' => $user->id,
            'war'     => null,
        ]);

        $areaLines = $component->get('area_lines');
        $match     = collect($areaLines)->firstWhere('date', $date);

        $this->assertNotNull($match, "area_lines must contain an entry for today ({$date}).");
        $this->assertEquals('Independence Day', $match['holiday']);
    }

    public function test_war_form_work_day_holiday_is_not_shown(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->toDateString();

        Holiday::create([
            'year'        => Carbon::now()->year,
            'month'       => Carbon::now()->month,
            'day'         => Carbon::now()->day,
            'title'       => 'Work Day Holiday',
            'repeat'      => false,
            'is_work_day' => true,
            'source'      => 'custom',
        ]);

        Cache::put('google_calendar_holidays_' . Carbon::now()->year, [], now()->addDay());

        $component = Livewire::actingAs($user)->test(WarForm::class, [
            'user_id' => $user->id,
            'war'     => null,
        ]);

        $areaLines = $component->get('area_lines');
        $match     = collect($areaLines)->firstWhere('date', $date);

        $this->assertNotNull($match);
        $this->assertNull($match['holiday'], 'A work-day holiday must not be flagged in area_lines.');
    }

    public function test_philippine_holiday_from_cache_appears_in_area_lines(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->toDateString();

        Cache::put('google_calendar_holidays_' . Carbon::now()->year, [
            [
                'summary' => 'National Heroes Day',
                'start'   => ['date' => $date],
            ],
        ], now()->addDay());

        $component = Livewire::actingAs($user)->test(WarForm::class, [
            'user_id' => $user->id,
            'war'     => null,
        ]);

        $areaLines = $component->get('area_lines');
        $match     = collect($areaLines)->firstWhere('date', $date);

        $this->assertNotNull($match);
        $this->assertEquals('National Heroes Day', $match['holiday']);
    }
}
