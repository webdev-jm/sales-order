<?php

namespace Tests\Feature;

use App\Http\Controllers\ActivityPlanController;
use App\Http\Requests\UpdateActivityPlanRequest;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Regression tests for duplicate schedule lines in the activity plan module.
 *
 * The insert guard in update() also matched on user_id. A line with no
 * "work with" person is stored as NULL but arrives from the form as an empty
 * string, and NULL = '' is never true in SQL, so the guard never matched and
 * every repeated save inserted another copy of the same line.
 */
class ActivityPlanDuplicateLinesTest extends TestCase
{
    use RefreshDatabase;

    private const PLAN_DATE = '2026-06-01';

    // ── update() ──────────────────────────────────────────────────────────────

    /**
     * Saving the same session payload twice must not insert a second detail row.
     */
    public function test_saving_the_same_plan_twice_does_not_duplicate_detail_lines(): void
    {
        [$plan, $branch] = $this->makePlanWithBranch();

        Session::put('activity_plan_data', $this->sessionPayload($plan, $branch));

        $this->saveDraft($plan);
        $this->saveDraft($plan);

        $this->assertSame(
            1,
            ActivityPlanDetail::where('activity_plan_id', $plan->id)
                ->where('date', self::PLAN_DATE)
                ->count(),
            'Saving the same line twice must update the existing row, not insert a duplicate.'
        );
    }

    /**
     * The first save must write the new row id back into the session so the
     * second save takes the update path instead of the insert path.
     */
    public function test_saving_writes_the_new_detail_id_back_into_the_session(): void
    {
        [$plan, $branch] = $this->makePlanWithBranch();

        Session::put('activity_plan_data', $this->sessionPayload($plan, $branch));

        $this->saveDraft($plan);

        $line = Session::get('activity_plan_data')[$plan->year]['details'][$plan->month][self::PLAN_DATE]['lines'][0];

        $this->assertArrayHasKey('id', $line,
            'update() must write the saved detail id back into the session line.');
        $this->assertSame(
            ActivityPlanDetail::where('activity_plan_id', $plan->id)->first()->id,
            $line['id'],
            'The session line id must point at the row that was just saved.'
        );
    }

    /**
     * A line that genuinely changed must still be updated on the second save.
     */
    public function test_second_save_updates_the_existing_line_instead_of_inserting(): void
    {
        [$plan, $branch] = $this->makePlanWithBranch();

        Session::put('activity_plan_data', $this->sessionPayload($plan, $branch));
        $this->saveDraft($plan);

        $payload = Session::get('activity_plan_data');
        $payload[$plan->year]['details'][$plan->month][self::PLAN_DATE]['lines'][0]['purpose'] = 'Updated purpose';
        Session::put('activity_plan_data', $payload);

        $this->saveDraft($plan);

        $details = ActivityPlanDetail::where('activity_plan_id', $plan->id)->get();

        $this->assertCount(1, $details, 'Editing a line must not create a second row.');
        $this->assertSame('Updated purpose', $details->first()->activity,
            'The existing row must carry the edited purpose.');
    }

    // ── show() ────────────────────────────────────────────────────────────────

    /**
     * Duplicate detail rows must collapse into a single calendar event.
     */
    public function test_show_does_not_emit_duplicate_calendar_events(): void
    {
        [$plan, $branch] = $this->makePlanWithBranch();

        foreach (range(1, 3) as $ignored) {
            ActivityPlanDetail::create([
                'activity_plan_id' => $plan->id,
                'branch_id'        => $branch->id,
                'day'              => 'Mon',
                'date'             => self::PLAN_DATE,
                'exact_location'   => 'Test location',
                'activity'         => 'Store visit',
            ]);
        }

        $schedule_data = (new ActivityPlanController())
            ->show($plan->id)
            ->getData()['schedule_data'];

        $plan_events = array_filter($schedule_data, fn (array $event) => isset($event['on_leave']));

        $this->assertCount(1, $plan_events,
            'Three identical detail rows must render as one calendar event.');
    }

    // ── printTrip() ───────────────────────────────────────────────────────────

    /**
     * The trip PDF must render the deduplicated collection from the controller
     * rather than iterating the raw relation.
     */
    public function test_trip_detail_view_renders_the_deduplicated_destinations(): void
    {
        $source = file_get_contents(
            resource_path('views/pages/mcp/trip-detail.blade.php')
        );

        $this->assertStringContainsString(
            '@foreach($destinations as $destination)',
            $source,
            'trip-detail.blade.php must iterate the deduplicated $destinations passed by printTrip().'
        );

        $this->assertStringNotContainsString(
            '@foreach($trip->destinations as $destination)',
            $source,
            'trip-detail.blade.php must not iterate the raw destinations relation.'
        );
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array{0: ActivityPlan, 1: Branch}
     */
    private function makePlanWithBranch(): array
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ActivityPlan::factory()->create([
            'user_id'    => $user->id,
            'year'       => 2026,
            'month'      => '06',
            'objectives' => 'Test objectives',
            'status'     => 'draft',
        ]);

        return [$plan, Branch::factory()->create()];
    }

    private function saveDraft(ActivityPlan $plan): void
    {
        $request = UpdateActivityPlanRequest::create(
            '/mcp/'.$plan->id, 'POST', ['status' => 'draft']
        );

        (new ActivityPlanController())->update($request, $plan->id);
    }

    /**
     * A session payload shaped the way the Detail2 Livewire component writes it:
     * a brand new line, with no id and an empty-string user_id.
     *
     * @return array<int|string, mixed>
     */
    private function sessionPayload(ActivityPlan $plan, Branch $branch): array
    {
        return [
            $plan->year => [
                'year'       => (string) $plan->year,
                'month'      => $plan->month,
                'objectives' => 'Test objectives',
                'details'    => [
                    $plan->month => [
                        self::PLAN_DATE => [
                            'day'      => 'Mon',
                            'date'     => 'Jun. 01',
                            'class'    => 'bg-light',
                            'on_leave' => false,
                            'lines'    => [
                                [
                                    'location'     => 'Test location',
                                    'account_id'   => '',
                                    'account_name' => '',
                                    'branch_id'    => $branch->id,
                                    'branch_name'  => $branch->branch_name,
                                    'purpose'      => 'Store visit',
                                    'user_id'      => '',
                                    'work_with'    => '',
                                    'trip'         => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
