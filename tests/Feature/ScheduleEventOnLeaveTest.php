<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Livewire\Schedules\ScheduleEvent;

/**
 * Tests for on-leave awareness in the ScheduleEvent Livewire component
 * and the schedule-calendar.blade.php eventClick handler.
 */
class ScheduleEventOnLeaveTest extends TestCase
{
    // ── ScheduleEvent component ───────────────────────────────────────────────

    public function test_schedule_event_imports_activity_plan_detail(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/Schedules/ScheduleEvent.php')
        );

        $this->assertStringContainsString(
            'use App\Models\ActivityPlanDetail;',
            $source,
            'ScheduleEvent must import ActivityPlanDetail.'
        );
    }

    public function test_schedule_event_has_is_on_leave_property(): void
    {
        $properties = array_map(
            fn (\ReflectionProperty $p) => $p->getName(),
            (new \ReflectionClass(ScheduleEvent::class))->getProperties(\ReflectionProperty::IS_PUBLIC)
        );

        $this->assertContains(
            'is_on_leave',
            $properties,
            'ScheduleEvent must have a public $is_on_leave property.'
        );
    }

    public function test_schedule_event_render_queries_activity_plan_detail_for_on_leave(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/Schedules/ScheduleEvent.php')
        );

        $this->assertStringContainsString(
            'is_on_leave',
            $source,
            'ScheduleEvent::render() must query ActivityPlanDetail using is_on_leave.'
        );

        $this->assertStringContainsString(
            "->where('status', 'approved')",
            $source,
            'ScheduleEvent::render() must filter on-leave entries from approved activity plans.'
        );
    }

    public function test_schedule_event_set_date_resets_schedule_data_when_no_schedule_id(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/Schedules/ScheduleEvent.php')
        );

        $this->assertStringContainsString(
            "reset('schedule_data')",
            $source,
            'ScheduleEvent::setDate() must reset schedule_data when no schedule_id is provided.'
        );
    }

    public function test_schedule_event_passes_is_on_leave_to_view(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/Schedules/ScheduleEvent.php')
        );

        $this->assertStringContainsString(
            "'is_on_leave'",
            $source,
            'ScheduleEvent::render() must pass is_on_leave to the view.'
        );
    }

    // ── schedule-event.blade.php ──────────────────────────────────────────────

    public function test_schedule_event_view_shows_on_leave_banner(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/schedules/schedule-event.blade.php')
        );

        $this->assertStringContainsString(
            '$is_on_leave',
            $source,
            'schedule-event.blade.php must check $is_on_leave.'
        );

        $this->assertStringContainsString(
            'ON LEAVE',
            $source,
            'schedule-event.blade.php must display an ON LEAVE banner.'
        );
    }

    // ── schedule-calendar.blade.php ───────────────────────────────────────────

    public function test_schedule_calendar_handles_on_leave_event_click(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/schedules/schedule-calendar.blade.php')
        );

        $this->assertStringContainsString(
            "type == 'on_leave'",
            $source,
            'schedule-calendar.blade.php eventClick must handle the on_leave event type.'
        );

        $this->assertStringContainsString(
            '#event-modal',
            $source,
            'schedule-calendar.blade.php must open #event-modal for on_leave events.'
        );
    }
}
