<?php

namespace Tests\Feature;

use App\Http\Controllers\ActivityPlanController;
use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Tests for the weekday coverage requirement on activity plan create/edit.
 *
 * Every non-weekend date in a plan must either have at least one schedule line
 * with a branch_id, or be explicitly marked as on leave. Saturday and Sunday
 * are exempt from this rule.
 */
class ActivityPlanWeekdayRequirementTest extends TestCase
{
    use RefreshDatabase;

    // ── validatePlanLines() ───────────────────────────────────────────────────

    /**
     * A weekday with no lines and no on-leave flag must set missing_weekday_error = 1
     * and include the date in missing_weekday_dates.
     */
    public function test_validate_plan_lines_flags_weekday_with_no_entry(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-06-01' => [
                'day'      => 'Mon',
                'on_leave' => false,
                'lines'    => [],
            ],
        ]);

        $this->assertSame(1, $result['missing_weekday_error'],
            'A weekday with no lines and no on-leave must set missing_weekday_error = 1.');
        $this->assertContains('2026-06-01', $result['missing_weekday_dates'],
            'The uncovered weekday date must appear in missing_weekday_dates.');
    }

    /**
     * A weekday marked on leave must not trigger missing_weekday_error.
     */
    public function test_validate_plan_lines_weekday_on_leave_passes(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-06-01' => [
                'day'      => 'Mon',
                'on_leave' => true,
                'lines'    => [],
            ],
        ]);

        $this->assertSame(0, $result['missing_weekday_error'],
            'A weekday with on_leave = true must not trigger missing_weekday_error.');
        $this->assertEmpty($result['missing_weekday_dates'],
            'missing_weekday_dates must be empty when the date is on leave.');
    }

    /**
     * A weekday with at least one non-deleted line with a branch_id must pass.
     */
    public function test_validate_plan_lines_weekday_with_valid_line_passes(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-06-02' => [
                'day'      => 'Tue',
                'on_leave' => false,
                'lines'    => [
                    [
                        'branch_id'  => '5',
                        'user_id'    => '',
                        'location'   => 'Office',
                        'purpose'    => 'Visit',
                        'account_id' => '',
                        'trip'       => [],
                    ],
                ],
            ],
        ]);

        $this->assertSame(0, $result['missing_weekday_error'],
            'A weekday with a valid branch_id line must not trigger missing_weekday_error.');
    }

    /**
     * Saturday with no lines and no on-leave must not trigger missing_weekday_error.
     */
    public function test_validate_plan_lines_saturday_excluded_from_check(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-05-30' => [
                'day'      => 'Sat',
                'on_leave' => false,
                'lines'    => [],
            ],
        ]);

        $this->assertSame(0, $result['missing_weekday_error'],
            'Saturday must be excluded from the weekday coverage check.');
    }

    /**
     * Sunday with no lines and no on-leave must not trigger missing_weekday_error.
     */
    public function test_validate_plan_lines_sunday_excluded_from_check(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-05-31' => [
                'day'      => 'Sun',
                'on_leave' => false,
                'lines'    => [],
            ],
        ]);

        $this->assertSame(0, $result['missing_weekday_error'],
            'Sunday must be excluded from the weekday coverage check.');
    }

    /**
     * A weekday whose only line is flagged as deleted must still fail the coverage check.
     */
    public function test_validate_plan_lines_deleted_line_does_not_satisfy_weekday(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-06-03' => [
                'day'      => 'Wed',
                'on_leave' => false,
                'lines'    => [
                    [
                        'branch_id'  => '5',
                        'user_id'    => '',
                        'location'   => 'Office',
                        'purpose'    => 'Visit',
                        'account_id' => '',
                        'trip'       => [],
                        'deleted'    => true,
                    ],
                ],
            ],
        ]);

        $this->assertSame(1, $result['missing_weekday_error'],
            'A weekday where all lines are deleted must still trigger missing_weekday_error = 1.');
        $this->assertContains('2026-06-03', $result['missing_weekday_dates']);
    }

    /**
     * Multiple uncovered weekdays must all appear in missing_weekday_dates.
     */
    public function test_validate_plan_lines_collects_all_missing_weekdays(): void
    {
        $result = $this->callValidatePlanLines([
            '2026-06-01' => ['day' => 'Mon', 'on_leave' => false, 'lines' => []],
            '2026-06-02' => ['day' => 'Tue', 'on_leave' => false, 'lines' => []],
            '2026-06-06' => ['day' => 'Sat', 'on_leave' => false, 'lines' => []],
        ]);

        $this->assertSame(1, $result['missing_weekday_error']);
        $this->assertCount(2, $result['missing_weekday_dates'],
            'Both uncovered weekdays must appear; Saturday must be excluded.');
        $this->assertContains('2026-06-01', $result['missing_weekday_dates']);
        $this->assertContains('2026-06-02', $result['missing_weekday_dates']);
    }

    // ── Source-level assertions for store() and update() ─────────────────────

    /**
     * store() must destructure and check missing_weekday_error from validatePlanLines().
     */
    public function test_store_returns_error_for_missing_weekday_entries(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            "\$missing_weekday_error = \$validation['missing_weekday_error']",
            $source,
            'store() must destructure missing_weekday_error from the validation result.'
        );

        $this->assertStringContainsString(
            'All weekdays must have an entry or be marked as on leave. Missing:',
            $source,
            'store() must return the missing-weekday error message.'
        );
    }

    /**
     * update() must destructure and check missing_weekday_error from validatePlanLines().
     */
    public function test_update_returns_error_for_missing_weekday_entries(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        // The string appears twice (store + update) — assertStringContainsString passes if it appears at least once.
        $this->assertStringContainsString(
            "\$missing_weekday_dates = \$validation['missing_weekday_dates']",
            $source,
            'update() must destructure missing_weekday_dates from the validation result.'
        );

        $this->assertMatchesRegularExpression(
            '/missing_weekday_error.*?missing_weekday_error.*?missing_weekday_error/s',
            $source,
            'Both store() and update() must reference missing_weekday_error (appears at least 3 times).'
        );
    }

    // ── Holiday exemption ─────────────────────────────────────────────────────

    /**
     * A weekday that falls on a holiday must be exempt — missing_weekday_error stays 0.
     */
    public function test_validate_plan_lines_holiday_weekday_is_exempt(): void
    {
        $result = $this->callValidatePlanLines(
            [
                '2026-06-01' => [
                    'day'      => 'Mon',
                    'on_leave' => false,
                    'lines'    => [],
                ],
            ],
            ['2026-06-01']
        );

        $this->assertSame(0, $result['missing_weekday_error'],
            'A weekday that is a holiday must not trigger missing_weekday_error.');
        $this->assertEmpty($result['missing_weekday_dates'],
            'missing_weekday_dates must be empty when the uncovered weekday is a holiday.');
    }

    /**
     * A weekday NOT in $holiday_dates must still be flagged as missing.
     */
    public function test_validate_plan_lines_non_holiday_weekday_still_required(): void
    {
        $result = $this->callValidatePlanLines(
            [
                '2026-06-02' => [
                    'day'      => 'Tue',
                    'on_leave' => false,
                    'lines'    => [],
                ],
            ],
            ['2026-06-01'] // 2026-06-02 is NOT a holiday
        );

        $this->assertSame(1, $result['missing_weekday_error'],
            'A weekday not in $holiday_dates must still trigger missing_weekday_error = 1.');
        $this->assertContains('2026-06-02', $result['missing_weekday_dates']);
    }

    /**
     * Only the holiday date is exempt; other uncovered weekdays in the same month are still flagged.
     */
    public function test_validate_plan_lines_only_matching_date_is_exempt(): void
    {
        $result = $this->callValidatePlanLines(
            [
                '2026-06-01' => ['day' => 'Mon', 'on_leave' => false, 'lines' => []], // holiday
                '2026-06-02' => ['day' => 'Tue', 'on_leave' => false, 'lines' => []], // not holiday
            ],
            ['2026-06-01']
        );

        $this->assertSame(1, $result['missing_weekday_error'],
            'The non-holiday weekday must still be flagged.');
        $this->assertNotContains('2026-06-01', $result['missing_weekday_dates'],
            'The holiday date must not appear in missing_weekday_dates.');
        $this->assertContains('2026-06-02', $result['missing_weekday_dates'],
            'The non-holiday weekday must appear in missing_weekday_dates.');
    }

    /**
     * store() and update() must delegate to getNoWorkHolidayDates() before calling validatePlanLines().
     */
    public function test_store_and_update_query_holidays_before_validation(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        $this->assertStringContainsString(
            'Holiday::',
            $source,
            'ActivityPlanController must query the Holiday model inside getNoWorkHolidayDates().'
        );

        $this->assertStringContainsString(
            'getNoWorkHolidayDates(',
            $source,
            'store() and update() must call getNoWorkHolidayDates() to build the $holiday_dates set.'
        );

        $this->assertStringContainsString(
            'validatePlanLines($data[\'details\'][$data[\'month\']], $holiday_dates)',
            $source,
            'validatePlanLines() must be called with the $holiday_dates argument in both store() and update().'
        );
    }

    // ── Work/no-work holiday exemption ────────────────────────────────────────

    /**
     * A Philippine holiday in the Google Calendar cache with no DB override must exempt the weekday.
     */
    public function test_philippine_no_work_holiday_exempts_weekday(): void
    {
        Cache::put('google_calendar_holidays_2026', [
            ['summary' => 'Independence Day', 'start' => ['date' => '2026-06-12']],
        ], now()->addDay());

        $dates = $this->callGetNoWorkHolidayDates('2026');

        $this->assertContains('2026-06-12', $dates,
            'A Philippine holiday with no work-day override must appear in no-work dates.');
    }

    /**
     * A Philippine holiday overridden to is_work_day=true must NOT appear in the no-work dates.
     */
    public function test_philippine_work_day_override_does_not_exempt_weekday(): void
    {
        Cache::put('google_calendar_holidays_2026', [
            ['summary' => 'Independence Day', 'start' => ['date' => '2026-06-12']],
        ], now()->addDay());

        Holiday::create([
            'source'      => 'philippine',
            'year'        => 2026,
            'month'       => 6,
            'day'         => 12,
            'title'       => 'Independence Day',
            'repeat'      => false,
            'is_work_day' => true,
        ]);

        $dates = $this->callGetNoWorkHolidayDates('2026');

        $this->assertNotContains('2026-06-12', $dates,
            'A Philippine holiday overridden to work-day must NOT appear in no-work dates.');
    }

    /**
     * A custom holiday with is_work_day=false must exempt the weekday.
     */
    public function test_custom_no_work_holiday_exempts_weekday(): void
    {
        Holiday::create([
            'source'      => 'custom',
            'year'        => 2026,
            'month'       => 3,
            'day'         => 15,
            'title'       => 'Company Day',
            'repeat'      => false,
            'is_work_day' => false,
        ]);

        $dates = $this->callGetNoWorkHolidayDates('2026');

        $this->assertContains('2026-03-15', $dates,
            'A custom no-work holiday must appear in no-work dates.');
    }

    /**
     * A custom holiday with is_work_day=true must NOT exempt the weekday.
     */
    public function test_custom_work_day_holiday_does_not_exempt_weekday(): void
    {
        Holiday::create([
            'source'      => 'custom',
            'year'        => 2026,
            'month'       => 3,
            'day'         => 15,
            'title'       => 'Company Work Day',
            'repeat'      => false,
            'is_work_day' => true,
        ]);

        $dates = $this->callGetNoWorkHolidayDates('2026');

        $this->assertNotContains('2026-03-15', $dates,
            'A custom work-day holiday must NOT appear in no-work dates.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function callValidatePlanLines(array $month_details, array $holiday_dates = []): array
    {
        $controller = new ActivityPlanController();
        $method = (new \ReflectionClass($controller))->getMethod('validatePlanLines');
        $method->setAccessible(true);

        return $method->invoke($controller, $month_details, $holiday_dates);
    }

    private function callGetNoWorkHolidayDates(string $year): array
    {
        $controller = new ActivityPlanController();
        $method = (new \ReflectionClass($controller))->getMethod('getNoWorkHolidayDates');
        $method->setAccessible(true);

        return $method->invoke($controller, $year);
    }
}
