<?php

namespace Tests\Feature;

use App\Http\Livewire\Schedules\ScheduleCalendar;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for holiday display in the schedule calendars.
 *
 * Holidays (Philippine via Google Calendar API, custom from DB) must appear
 * as 'holiday' type events in both the controller-rendered schedules/index.blade.php
 * calendar and the Livewire ScheduleCalendar component.
 */
class ScheduleHolidayDisplayTest extends TestCase
{
    use RefreshDatabase;

    // ── UserBranchScheduleController ─────────────────────────────────────────

    public function test_controller_has_build_holiday_events_method(): void
    {
        $reflection = new \ReflectionClass(\App\Http\Controllers\UserBranchScheduleController::class);

        $this->assertTrue(
            $reflection->hasMethod('buildHolidayEvents'),
            'UserBranchScheduleController must have a buildHolidayEvents() method.'
        );
    }

    public function test_controller_merges_holiday_events_into_schedule_data(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/UserBranchScheduleController.php')
        );

        $this->assertStringContainsString(
            'buildHolidayEvents',
            $source,
            'UserBranchScheduleController::index() must call buildHolidayEvents().'
        );

        $this->assertStringContainsString(
            'array_merge',
            $source,
            'UserBranchScheduleController must merge holiday events into $schedule_data.'
        );
    }

    public function test_controller_uses_shared_google_calendar_cache_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/UserBranchScheduleController.php')
        );

        $this->assertStringContainsString(
            'google_calendar_holidays_',
            $source,
            'buildHolidayEvents() must use the shared google_calendar_holidays_ cache key.'
        );
    }

    // ── schedules/index.blade.php ─────────────────────────────────────────────

    public function test_schedule_index_view_guards_holiday_event_click(): void
    {
        $source = file_get_contents(
            resource_path('views/pages/schedules/index.blade.php')
        );

        $this->assertStringContainsString(
            "type == 'holiday'",
            $source,
            'schedules/index.blade.php eventClick must guard against holiday event clicks.'
        );
    }

    public function test_schedule_index_view_shows_holiday_color_badges(): void
    {
        $source = file_get_contents(
            resource_path('views/pages/schedules/index.blade.php')
        );

        $this->assertStringContainsString(
            'Holiday (No Work)',
            $source,
            'schedules/index.blade.php must show a Holiday (No Work) color badge.'
        );

        $this->assertStringContainsString(
            'Holiday (Work Day)',
            $source,
            'schedules/index.blade.php must show a Holiday (Work Day) color badge.'
        );

        $this->assertStringContainsString(
            'Custom Holiday',
            $source,
            'schedules/index.blade.php must show a Custom Holiday color badge.'
        );
    }

    public function test_schedule_index_view_has_separate_events_and_holidays_sections(): void
    {
        $source = file_get_contents(
            resource_path('views/pages/schedules/index.blade.php')
        );

        $this->assertStringContainsString(
            'Events',
            $source,
            'schedules/index.blade.php must have a separate Events label for event color codes.'
        );

        $this->assertStringContainsString(
            'Holidays',
            $source,
            'schedules/index.blade.php must have a separate Holidays label for holiday color codes.'
        );
    }

    // ── ScheduleCalendar Livewire component ───────────────────────────────────

    public function test_schedule_calendar_component_has_build_holiday_events_method(): void
    {
        $reflection = new \ReflectionClass(ScheduleCalendar::class);

        $this->assertTrue(
            $reflection->hasMethod('buildHolidayEvents'),
            'ScheduleCalendar must have a buildHolidayEvents() method.'
        );
    }

    public function test_schedule_calendar_component_uses_shared_cache_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/Schedules/ScheduleCalendar.php')
        );

        $this->assertStringContainsString(
            'google_calendar_holidays_',
            $source,
            'ScheduleCalendar::buildHolidayEvents() must use the shared google_calendar_holidays_ cache key.'
        );
    }

    public function test_schedule_calendar_view_guards_holiday_event_click(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/schedules/schedule-calendar.blade.php')
        );

        $this->assertStringContainsString(
            "type == 'holiday'",
            $source,
            'schedule-calendar.blade.php eventClick must guard against holiday event clicks.'
        );
    }

    // ── Livewire functional tests ─────────────────────────────────────────────

    public function test_schedule_calendar_includes_custom_holiday_in_schedule_data(): void
    {
        $user = User::factory()->create();

        Holiday::create([
            'year'        => Carbon::now()->year,
            'month'       => Carbon::now()->month,
            'day'         => 15,
            'title'       => 'Company Anniversary',
            'repeat'      => false,
            'is_work_day' => false,
            'source'      => 'custom',
        ]);

        Cache::put('google_calendar_holidays_' . Carbon::now()->year, [], now()->addDay());

        $component = Livewire::actingAs($user)->test(ScheduleCalendar::class);

        $scheduleData  = $component->get('schedule_data');
        $holidayEvents = array_values(array_filter(
            $scheduleData,
            fn (array $e) => ($e['type'] ?? '') === 'holiday'
        ));

        $this->assertNotEmpty($holidayEvents, 'schedule_data must contain at least one holiday event.');
        $this->assertEquals('Company Anniversary', $holidayEvents[0]['title']);
        $this->assertEquals('background', $holidayEvents[0]['display']);
        $this->assertEquals('#e74c3c', $holidayEvents[0]['backgroundColor']);
    }

    public function test_schedule_calendar_includes_philippine_holiday_from_cache(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->year . '-06-12';

        Cache::put('google_calendar_holidays_' . Carbon::now()->year, [
            [
                'summary' => 'Independence Day',
                'start'   => ['date' => $date],
            ],
        ], now()->addDay());

        $component = Livewire::actingAs($user)->test(ScheduleCalendar::class);

        $scheduleData  = $component->get('schedule_data');
        $holidayEvents = array_values(array_filter(
            $scheduleData,
            fn (array $e) => ($e['type'] ?? '') === 'holiday' && $e['title'] === 'Independence Day'
        ));

        $this->assertNotEmpty($holidayEvents, 'schedule_data must include Philippine holidays from cache.');
        $this->assertEquals('#3498db', $holidayEvents[0]['backgroundColor'], 'Default Philippine holiday color must be #3498db (no-work).');
    }

    public function test_philippine_holiday_marked_as_work_day_uses_green_color(): void
    {
        $user   = User::factory()->create();
        $date   = Carbon::now()->year . '-06-12';
        $parsed = Carbon::parse($date);

        Holiday::create([
            'source'      => 'philippine',
            'year'        => $parsed->year,
            'month'       => $parsed->month,
            'day'         => $parsed->day,
            'title'       => 'Independence Day',
            'repeat'      => false,
            'is_work_day' => true,
        ]);

        Cache::put('google_calendar_holidays_' . Carbon::now()->year, [
            [
                'summary' => 'Independence Day',
                'start'   => ['date' => $date],
            ],
        ], now()->addDay());

        $component = Livewire::actingAs($user)->test(ScheduleCalendar::class);

        $scheduleData  = $component->get('schedule_data');
        $holidayEvents = array_values(array_filter(
            $scheduleData,
            fn (array $e) => ($e['type'] ?? '') === 'holiday' && $e['title'] === 'Independence Day'
        ));

        $this->assertNotEmpty($holidayEvents);
        $this->assertEquals('#27ae60', $holidayEvents[0]['backgroundColor'], 'Work-day Philippine holiday must use green #27ae60.');
    }
}
