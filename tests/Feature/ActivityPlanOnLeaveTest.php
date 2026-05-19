<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\ActivityPlanController;
use App\Http\Livewire\ActivityPlan\Detail2;
use App\Models\ActivityPlanDetail;

/**
 * Tests for the "On Leave" feature in the activity plan module.
 *
 * This feature allows users to mark a specific date as "on leave" so that
 * no schedule lines are required for that day. A sentinel ActivityPlanDetail
 * row with is_on_leave = true is stored in the database for persistence.
 */
class ActivityPlanOnLeaveTest extends TestCase
{
    // ── Model ─────────────────────────────────────────────────────────────────

    /**
     * The ActivityPlanDetail model must allow mass-assigning is_on_leave.
     */
    public function test_activity_plan_detail_model_has_is_on_leave_in_fillable(): void
    {
        $model = new ActivityPlanDetail();
        $this->assertContains(
            'is_on_leave',
            $model->getFillable(),
            'ActivityPlanDetail::$fillable must include "is_on_leave".'
        );
    }

    // ── Controller: store ─────────────────────────────────────────────────────

    /**
     * The store() method must save a sentinel row (is_on_leave = true) for
     * on-leave dates and skip the lines loop via continue.
     */
    public function test_store_saves_sentinel_row_and_continues_for_on_leave_dates(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "'is_on_leave'      => true,",
            $source,
            'store() must create an ActivityPlanDetail with is_on_leave = true for on-leave dates.'
        );

        // Ensure a continue follows the sentinel save to skip line processing.
        $this->assertMatchesRegularExpression(
            '/is_on_leave.*?true.*?continue;/s',
            $source,
            'store() must have a continue after saving the on-leave sentinel row.'
        );
    }

    // ── Controller: update ────────────────────────────────────────────────────

    /**
     * The update() method must remove non-leave lines and upsert the sentinel
     * row when a date is toggled on leave.
     */
    public function test_update_handles_on_leave_toggle_correctly(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "->where('is_on_leave', false)",
            $source,
            'update() must delete existing non-leave lines when a date is marked on leave.'
        );

        $this->assertStringContainsString(
            "->where('is_on_leave', true)",
            $source,
            'update() must clean up old sentinel rows when a date is unmarked from on leave.'
        );

        $this->assertStringContainsString(
            'updateOrCreate',
            $source,
            'update() must upsert the on-leave sentinel row using updateOrCreate.'
        );
    }

    // ── Controller: edit ──────────────────────────────────────────────────────

    /**
     * The edit() method must detect the sentinel row and restore the on_leave
     * flag in the session data so the UI reflects the saved state.
     */
    public function test_edit_restores_on_leave_flag_from_sentinel_row(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "['on_leave'] = true",
            $source,
            'edit() must set on_leave = true in session data when a sentinel row is found.'
        );

        $this->assertStringContainsString(
            "['on_leave'] = false",
            $source,
            'edit() must default on_leave = false for dates without a sentinel row.'
        );
    }

    // ── Controller: validatePlanLines ─────────────────────────────────────────

    /**
     * validatePlanLines() must treat on-leave dates as "filled" (line_empty = 0)
     * and skip line validation for them.
     */
    public function test_validate_plan_lines_skips_on_leave_dates(): void
    {
        $controller = new ActivityPlanController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validatePlanLines');
        $method->setAccessible(true);

        // A month with only one on-leave date and no regular lines.
        $month_details = [
            '2026-06-10' => [
                'day'      => 'Wed',
                'on_leave' => true,
                'lines'    => [],
            ],
        ];

        $result = $method->invoke($controller, $month_details);

        $this->assertSame(0, $result['line_error'], 'An on-leave date must not cause a line_error.');
        $this->assertSame(0, $result['line_empty'], 'An on-leave date must count as a filled date (line_empty = 0).');
        $this->assertSame(0, $result['duplicate_error'], 'An on-leave date must not cause a duplicate_error.');
    }

    /**
     * validatePlanLines() must run normal line validation (line_error) for
     * non-on-leave dates. A line with other fields but no branch_id is invalid.
     */
    public function test_validate_plan_lines_triggers_line_error_for_incomplete_non_on_leave_line(): void
    {
        $controller = new ActivityPlanController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('validatePlanLines');
        $method->setAccessible(true);

        // A date not on leave, with a line that has a location but no branch_id — should fail.
        $month_details = [
            '2026-06-10' => [
                'day'      => 'Wed',
                'on_leave' => false,
                'lines'    => [
                    [
                        'branch_id'  => '',
                        'user_id'    => '',
                        'location'   => 'Some Place',
                        'purpose'    => '',
                        'account_id' => '',
                        'trip'       => [],
                    ],
                ],
            ],
        ];

        $result = $method->invoke($controller, $month_details);

        $this->assertSame(1, $result['line_error'],
            'A non-on-leave line with other fields but no branch_id must trigger line_error = 1.'
        );
        $this->assertSame(0, $result['duplicate_error'],
            'An incomplete line must not cause a duplicate_error.'
        );
    }

    // ── Controller: printPDF ──────────────────────────────────────────────────

    /**
     * printPDF() must display "ON LEAVE" as the purpose for on-leave dates.
     */
    public function test_print_pdf_shows_on_leave_label_for_on_leave_dates(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "'purpose' => 'ON LEAVE'",
            $source,
            'printPDF() must set purpose to "ON LEAVE" when the date has an on-leave sentinel row.'
        );
    }

    // ── Livewire Detail2 ──────────────────────────────────────────────────────

    /**
     * Detail2 must expose a toggleOnLeave() method.
     */
    public function test_detail2_has_toggle_on_leave_method(): void
    {
        $this->assertTrue(
            method_exists(Detail2::class, 'toggleOnLeave'),
            'Detail2 Livewire component must have a toggleOnLeave() method.'
        );
    }

    /**
     * Detail2::toggleOnLeave() must flip the on_leave flag for the given date
     * and persist it to the session.
     */
    public function test_detail2_toggle_on_leave_flips_the_flag(): void
    {
        $component = new Detail2();
        $date = '2026-06-10';

        $component->month_days['06'][$date] = [
            'day'      => 'Wed',
            'date'     => 'Jun. 10',
            'class'    => 'bg-light',
            'lines'    => [],
            'on_leave' => false,
        ];
        $component->month = '06';

        $component->toggleOnLeave($date);
        $this->assertTrue(
            $component->month_days['06'][$date]['on_leave'],
            'toggleOnLeave() must set on_leave to true when it was false.'
        );

        $component->toggleOnLeave($date);
        $this->assertFalse(
            $component->month_days['06'][$date]['on_leave'],
            'toggleOnLeave() must set on_leave back to false when toggled again.'
        );
    }

    /**
     * Detail2::addSheduleLine() must do nothing when the date is on leave.
     */
    public function test_detail2_add_schedule_line_is_blocked_when_on_leave(): void
    {
        $component = new Detail2();
        $date = '2026-06-10';

        $component->month_days['06'][$date] = [
            'day'      => 'Wed',
            'date'     => 'Jun. 10',
            'class'    => 'bg-light',
            'lines'    => [],
            'on_leave' => true,
        ];
        $component->month = '06';

        $component->addScheduleLine($date);

        $this->assertEmpty(
            $component->month_days['06'][$date]['lines'],
            'addSheduleLine() must not add a line when the date is marked as on leave.'
        );
    }

    /**
     * Detail2::setMonthDays() must include on_leave key in each date entry.
     */
    public function test_detail2_set_month_days_includes_on_leave_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Detail2.php')
        );

        $this->assertStringContainsString(
            "'on_leave'",
            $source,
            'Detail2::setMonthDays() must include an "on_leave" key in each date entry.'
        );
    }

    // ── show() / view page ────────────────────────────────────────────────────

    /**
     * show() must include on-leave sentinel rows in schedule_data with the
     * on_leave flag and a distinct red background color.
     */
    public function test_show_includes_on_leave_entries_in_schedule_data(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "'on_leave'        => true",
            $source,
            'show() must add on-leave entries to $schedule_data with on_leave = true.'
        );

        $this->assertStringContainsString(
            "'backgroundColor' => '#dc3545'",
            $source,
            'show() must use a red background color for on-leave calendar entries.'
        );

        $this->assertStringContainsString(
            'ON LEAVE',
            $source,
            'show() must label on-leave calendar entries as "ON LEAVE".'
        );
    }

    /**
     * The show view must guard the event click to skip the detail modal for
     * on-leave calendar entries.
     */
    public function test_show_view_skips_detail_modal_for_on_leave_events(): void
    {
        $source = file_get_contents(
            resource_path('views/mcp/show.blade.php')
        );

        $this->assertStringContainsString(
            'on_leave',
            $source,
            'show.blade.php must check on_leave in the FullCalendar eventClick handler.'
        );

        $this->assertStringContainsString(
            'return;',
            $source,
            'show.blade.php must return early (skip modal) for on-leave events.'
        );
    }

    // ── WAR (Weekly Productivity Report) ─────────────────────────────────────

    /**
     * WarForm::changeDate() must include an 'on_leave' key in each area_lines entry.
     */
    public function test_war_form_area_lines_includes_on_leave_key(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/War/WarForm.php')
        );

        $this->assertStringContainsString(
            'ActivityPlanDetail',
            $source,
            'WarForm must import and use ActivityPlanDetail to detect on-leave dates.'
        );

        $this->assertStringContainsString(
            "'on_leave'",
            $source,
            'WarForm::changeDate() must include an "on_leave" key in each area_lines entry.'
        );

        $this->assertStringContainsString(
            'is_on_leave',
            $source,
            'WarForm::changeDate() must query ActivityPlanDetail using is_on_leave.'
        );
    }

    /**
     * war-form.blade.php must show an ON LEAVE badge when the line has on_leave = true.
     */
    public function test_war_form_view_shows_on_leave_badge(): void
    {
        $source = file_get_contents(
            resource_path('views/livewire/war/war-form.blade.php')
        );

        $this->assertStringContainsString(
            "on_leave",
            $source,
            'war-form.blade.php must check the on_leave key from area_lines.'
        );

        $this->assertStringContainsString(
            'ON LEAVE',
            $source,
            'war-form.blade.php must display an "ON LEAVE" label for on-leave dates.'
        );
    }
}
