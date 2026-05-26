<?php

namespace Tests\Feature;

use App\Http\Livewire\Holidays\HolidayAdd;
use App\Http\Livewire\Holidays\HolidayIndex;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class HolidayIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, array<string, mixed>> */
    private array $fakeGoogleItems = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeGoogleItems = [
            [
                'summary' => 'New Year\'s Day',
                'start'   => ['date' => Carbon::now()->year . '-01-01'],
            ],
            [
                'summary' => 'Independence Day',
                'start'   => ['date' => Carbon::now()->year . '-06-12'],
            ],
        ];

        Cache::put(
            'google_calendar_holidays_' . Carbon::now()->year,
            $this->fakeGoogleItems,
            now()->addDay()
        );
    }

    // ── View toggle ───────────────────────────────────────────────────────────

    public function test_default_active_view_is_list(): void
    {
        Livewire::test(HolidayIndex::class)
            ->assertSet('activeView', 'list');
    }

    public function test_switch_view_to_calendar(): void
    {
        Livewire::test(HolidayIndex::class)
            ->call('switchView', 'calendar')
            ->assertSet('activeView', 'calendar');
    }

    public function test_switch_view_back_to_list(): void
    {
        Livewire::test(HolidayIndex::class)
            ->call('switchView', 'calendar')
            ->call('switchView', 'list')
            ->assertSet('activeView', 'list');
    }

    // ── Philippine holiday work-day toggle ────────────────────────────────────

    public function test_toggle_philippine_holiday_to_work_creates_override_record(): void
    {
        $date = Carbon::now()->year . '-01-01';

        Livewire::test(HolidayIndex::class)
            ->call('toggleWorkDay', $date, "New Year's Day");

        $this->assertDatabaseHas('holidays', [
            'source'      => 'philippine',
            'year'        => Carbon::now()->year,
            'month'       => 1,
            'day'         => 1,
            'is_work_day' => true,
        ]);
    }

    public function test_toggle_philippine_holiday_back_to_no_work(): void
    {
        $date   = Carbon::now()->year . '-01-01';
        $parsed = Carbon::parse($date);

        Holiday::create([
            'source'      => 'philippine',
            'year'        => $parsed->year,
            'month'       => $parsed->month,
            'day'         => $parsed->day,
            'title'       => "New Year's Day",
            'repeat'      => false,
            'is_work_day' => true,
        ]);

        Livewire::test(HolidayIndex::class)
            ->call('toggleWorkDay', $date, "New Year's Day");

        $this->assertDatabaseHas('holidays', [
            'source'      => 'philippine',
            'year'        => $parsed->year,
            'month'       => 1,
            'day'         => 1,
            'is_work_day' => false,
        ]);
    }

    // ── Delete custom holiday ─────────────────────────────────────────────────

    public function test_delete_custom_holiday(): void
    {
        $holiday = Holiday::create([
            'year'        => Carbon::now()->year,
            'month'       => 3,
            'day'         => 15,
            'title'       => 'Company Anniversary',
            'repeat'      => false,
            'is_work_day' => false,
            'source'      => 'custom',
        ]);

        Livewire::test(HolidayIndex::class)
            ->call('deleteHoliday', $holiday->id);

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    public function test_delete_philippine_override_is_rejected(): void
    {
        $override = Holiday::create([
            'year'        => Carbon::now()->year,
            'month'       => 1,
            'day'         => 1,
            'title'       => "New Year's Day",
            'repeat'      => false,
            'is_work_day' => true,
            'source'      => 'philippine',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(HolidayIndex::class)
            ->call('deleteHoliday', $override->id);
    }

    // ── Add custom holiday ────────────────────────────────────────────────────

    public function test_add_custom_holiday_saves_with_custom_source(): void
    {
        Livewire::test(HolidayAdd::class)
            ->call('setDate', Carbon::now()->year . '-08-21')
            ->set('title', 'Ninoy Aquino Day')
            ->set('is_work_day', false)
            ->call('addHoliday');

        $this->assertDatabaseHas('holidays', [
            'title'  => 'Ninoy Aquino Day',
            'month'  => 8,
            'day'    => 21,
            'source' => 'custom',
        ]);
    }

    public function test_add_holiday_requires_title(): void
    {
        Livewire::test(HolidayAdd::class)
            ->call('setDate', Carbon::now()->year . '-08-21')
            ->call('addHoliday')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_add_holiday_rejects_invalid_month(): void
    {
        Livewire::test(HolidayAdd::class)
            ->set('month', 13)
            ->set('day', 1)
            ->set('title', 'Test')
            ->call('addHoliday')
            ->assertHasErrors(['month']);
    }

    // ── Merged list ───────────────────────────────────────────────────────────

    public function test_list_includes_philippine_holidays_from_cache(): void
    {
        Livewire::test(HolidayIndex::class)
            ->assertSee("New Year's Day")
            ->assertSee('Independence Day');
    }

    public function test_list_includes_custom_holidays(): void
    {
        Holiday::create([
            'year'        => Carbon::now()->year,
            'month'       => 3,
            'day'         => 15,
            'title'       => 'Company Day',
            'repeat'      => false,
            'is_work_day' => false,
            'source'      => 'custom',
        ]);

        Livewire::test(HolidayIndex::class)
            ->assertSee('Company Day');
    }

    public function test_philippine_holiday_marked_as_work_shows_set_no_work_button(): void
    {
        Holiday::create([
            'source'      => 'philippine',
            'year'        => Carbon::now()->year,
            'month'       => 1,
            'day'         => 1,
            'title'       => "New Year's Day",
            'repeat'      => false,
            'is_work_day' => true,
        ]);

        Livewire::test(HolidayIndex::class)
            ->assertSee('Set No Work');
    }

    // ── Uses cache, not live API ───────────────────────────────────────────────

    public function test_component_uses_cache_and_does_not_call_api_when_cached(): void
    {
        Http::fake();

        Livewire::test(HolidayIndex::class)
            ->assertSee("New Year's Day");

        Http::assertNothingSent();
    }
}
