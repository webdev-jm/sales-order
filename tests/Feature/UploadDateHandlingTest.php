<?php

namespace Tests\Feature;

use App\Imports\ScheduleImport;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserBranchSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * The schedule upload used to understand exactly one text format, `Y-m-d`, and
 * threw on anything else - so a sheet written as `01/16/2025` either failed the
 * whole import or stored a date nobody asked for. It now reads every format the
 * shared parser accepts and names the rows it had to skip.
 */
class UploadDateHandlingTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('activitylog.enabled', false);

        $this->user = User::factory()->create();
        $this->branch = Branch::factory()->create();
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function scheduleDateProvider(): array
    {
        return [
            'iso text'        => ['2025-01-16', '2025-01-16'],
            'us slashes'      => ['01/16/2025', '2025-01-16'],
            'us dashes'       => ['01-16-2025', '2025-01-16'],
            'abbrev month'    => ['16-Jan-2025', '2025-01-16'],
            'excel serial'    => [45673, '2025-01-16'],
            'serial as float' => [45673.5, '2025-01-16'],
        ];
    }

    /**
     * @dataProvider scheduleDateProvider
     *
     * @param  mixed  $value
     */
    public function test_schedule_upload_accepts_any_date_format($value, string $expected): void
    {
        $import = new ScheduleImport;

        $schedule = $import->model([$this->user->email, $this->branch->branch_code, $value]);

        $this->assertInstanceOf(UserBranchSchedule::class, $schedule);
        $this->assertSame($expected, $schedule->date);
        $this->assertSame([], $import->rowErrors);
    }

    public function test_schedule_upload_skips_and_reports_a_value_that_is_not_a_date(): void
    {
        $import = new ScheduleImport;

        $schedule = $import->model([$this->user->email, $this->branch->branch_code, 'sometime next week']);

        $this->assertNull($schedule);
        $this->assertCount(1, $import->rowErrors);
        $this->assertStringContainsString('Schedule date is not a valid date', $import->rowErrors[0]);
        $this->assertStringContainsString('sometime next week', $import->rowErrors[0]);
    }

    public function test_schedule_upload_reports_an_empty_date_cell(): void
    {
        $import = new ScheduleImport;

        $this->assertNull($import->model([$this->user->email, $this->branch->branch_code, '']));
        $this->assertStringContainsString('Schedule date is required.', $import->rowErrors[0]);
    }

    /**
     * The message has to point at the sheet row, which is only useful if the
     * counter keeps up with the rows the import walks past.
     */
    public function test_reported_rows_match_their_position_in_the_sheet(): void
    {
        $import = new ScheduleImport;

        $import->model([$this->user->email, $this->branch->branch_code, '2025-01-16']);
        $import->model([$this->user->email, $this->branch->branch_code, 'not a date']);

        $this->assertStringStartsWith('Row 3:', $import->rowErrors[0]);
    }

    /**
     * A row for an unknown user or branch was always skipped silently; that is
     * unchanged, and must not be reported as a date problem.
     */
    public function test_an_unknown_user_is_still_skipped_without_a_date_error(): void
    {
        $import = new ScheduleImport;

        $this->assertNull($import->model(['nobody@example.com', $this->branch->branch_code, 'not a date']));
        $this->assertSame([], $import->rowErrors);
    }
}
