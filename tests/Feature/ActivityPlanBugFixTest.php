<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Livewire\ActivityPlan\Header;
use App\Http\Livewire\ActivityPlan\Trip;
use App\Http\Livewire\ActivityPlan\Approval;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanApproval;
use App\Models\ActivityPlanDetailTripApproval;
use Livewire\Livewire;

/**
 * Regression tests for activity plan module bug fixes.
 *
 * Bug 1: Undefined $activity_plan_detail_trip when source is 'other' in store()
 * Bug 2: Trip processing runs after a detail line is force-deleted in update()
 * Bug 3: $approval variable shadowing sends wrong model to notifications
 * Bug 4: Typo trasportation_type prevents activity log from firing
 * Bug 5: Return date validation message was backwards ("on or before" → "on or after")
 * Bug 6: 'february' was lowercase in the months array
 */
class ActivityPlanBugFixTest extends TestCase
{

    // ── Bug 6 ────────────────────────────────────────────────────────────────

    /**
     * Bug 6: Header months array must have 'February' capitalised.
     */
    public function test_header_months_array_has_february_capitalised(): void
    {
        $component = new Header();
        $months = (new \ReflectionClass($component))
            ->getMethod('render')
            ->invoke($component)
            ->getData()['months_arr'];

        $this->assertSame('February', $months['02'],
            "Month '02' should be 'February' (capital F), got: '{$months['02']}'");
    }

    /**
     * Bug 6: No month in the array should start with a lowercase letter.
     */
    public function test_header_months_array_all_capitalised(): void
    {
        $component = new Header();
        $months = (new \ReflectionClass($component))
            ->getMethod('render')
            ->invoke($component)
            ->getData()['months_arr'];

        foreach ($months as $key => $name) {
            $this->assertSame(ucfirst($name), $name,
                "Month '{$key}' ('{$name}') should start with an uppercase letter.");
        }
    }

    // ── Bug 5 ────────────────────────────────────────────────────────────────

    /**
     * Bug 5: Return date validation message must say "on or after", not "on or before".
     */
    public function test_trip_return_date_error_message_says_on_or_after(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Trip.php')
        );

        $this->assertStringContainsString(
            'The return date must be on or after the departure date.',
            $source,
            'The return-date validation message should say "on or after".'
        );

        $this->assertStringNotContainsString(
            'The return date must be on or before the departure date.',
            $source,
            'The backwards message "on or before" must not appear in Trip.php.'
        );
    }

    // ── Bug 4 ────────────────────────────────────────────────────────────────

    /**
     * Bug 4: Typo "trasportation_type" must not appear anywhere in Approval.php.
     */
    public function test_approval_transportation_type_not_misspelled(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Approval.php')
        );

        $this->assertStringNotContainsString(
            'trasportation_type',
            $source,
            'The typo "trasportation_type" must not appear in Approval.php.'
        );

        $this->assertStringContainsString(
            'transportation_type',
            $source,
            'Approval.php must reference "transportation_type" (correct spelling).'
        );
    }

    // ── Bug 3 ────────────────────────────────────────────────────────────────

    /**
     * Bug 3: The $approval variable used for notifications must never be
     * shadowed by the trip approval inside the loop.
     * We verify this by asserting that no assignment of ActivityPlanDetailTripApproval
     * to $approval (without a different variable name) remains in Approval.php.
     */
    public function test_approval_variable_not_shadowed_by_trip_approval(): void
    {
        $source = file_get_contents(
            app_path('Http/Livewire/ActivityPlan/Approval.php')
        );

        // The fix renames inner assignments to $trip_approval.
        // Verify the old pattern "$approval = new ActivityPlanDetailTripApproval" is gone.
        $this->assertStringNotContainsString(
            '$approval = new ActivityPlanDetailTripApproval(',
            $source,
            'Trip approvals inside the loop must be assigned to $trip_approval, not $approval.'
        );

        // Verify the fixed variable name is present.
        $this->assertStringContainsString(
            '$trip_approval = new ActivityPlanDetailTripApproval(',
            $source,
            'Approval.php must use $trip_approval for ActivityPlanDetailTripApproval instances.'
        );
    }

    // ── Bug 1 ────────────────────────────────────────────────────────────────

    /**
     * Bug 1: When source == 'other', the trip must be resolved from the
     * destination's parent trip (saveTripForDetail private method).
     */
    public function test_store_sets_activity_plan_detail_trip_from_destination_trip(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        // After refactor the fix lives inside saveTripForDetail:
        //   $trip = $destination->trip;
        $this->assertStringContainsString(
            '$trip = $destination->trip;',
            $source,
            'saveTripForDetail() must assign $trip from $destination->trip when source is "other".'
        );
    }

    // ── Bug 2 ────────────────────────────────────────────────────────────────

    /**
     * Bug 2: After force-deleting a detail line in update(), a continue
     * statement must prevent the trip section from running on the deleted record.
     */
    public function test_update_has_continue_after_force_delete(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/ActivityPlanController.php')
        );

        // Verify the continue appears right after the forceDelete / trip-nullification block.
        $this->assertMatchesRegularExpression(
            '/forceDelete\(\).*?continue;/s',
            $source,
            'update() must have a continue statement after forceDelete() to skip trip processing for deleted lines.'
        );
    }
}
