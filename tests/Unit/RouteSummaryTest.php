<?php

namespace Tests\Unit;

use App\Models\BranchLogin;
use App\Services\Maps\RouteSummary;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RouteSummaryTest extends TestCase
{
    /**
     * @param array<int, array<string, mixed>> $trail
     */
    private function login(array $trail, ?string $timeOut = '2026-10-20 10:00:00'): BranchLogin
    {
        return new BranchLogin([
            'latitude'           => 14.5600,
            'longitude'          => 121.0200,
            'accuracy'           => '10.000 m',
            'time_in'            => '2026-10-20 08:00:00',
            'time_out'           => $timeOut,
            'time_out_latitude'  => $timeOut ? 14.5600 : null,
            'time_out_longitude' => $timeOut ? 121.0200 : null,
            'location_trail'     => $trail,
        ]);
    }

    /**
     * One reading a minute between two times, drifting a few metres around one spot.
     *
     * @return array<int, array<string, mixed>>
     */
    private function stayingPut(string $from, int $minutes, float $latitude = 14.5600, float $longitude = 121.0200): array
    {
        $points = [];
        for ($i = 0; $i < $minutes; $i++) {
            $points[] = [
                'latitude'    => $latitude + (($i % 3) - 1) * 0.00003,
                'longitude'   => $longitude + (($i % 2) ? 0.00003 : -0.00003),
                'accuracy'    => '12.000 m',
                'recorded_at' => Carbon::parse($from)->addMinutes($i)->toDateTimeString(),
            ];
        }

        return $points;
    }

    public function test_gps_drift_while_staying_in_one_store_adds_no_distance(): void
    {
        $summary = RouteSummary::for($this->login($this->stayingPut('2026-10-20 08:00:00', 120)));

        $this->assertLessThan(0.05, $summary['distance_m'] / 1000);
        $this->assertCount(1, $summary['stops']);
        $this->assertGreaterThanOrEqual(110, $summary['stops'][0]['minutes']);
        $this->assertSame(120, $summary['tracked_points']);
        $this->assertSame(120, $summary['duration_minutes']);
    }

    public function test_moving_between_two_stores_counts_the_trip_and_both_stays(): void
    {
        $trail = array_merge(
            $this->stayingPut('2026-10-20 08:00:00', 30),
            [['latitude' => 14.5650, 'longitude' => 121.0200, 'accuracy' => 15.0, 'recorded_at' => '2026-10-20 08:35:00']],
            $this->stayingPut('2026-10-20 08:40:00', 30, 14.5700, 121.0200),
        );

        $summary = RouteSummary::for($this->login($trail));

        // 08:00 store -> 1.1 km north -> back to sign-out at the first store.
        $this->assertEqualsWithDelta(2.2, $summary['distance_m'] / 1000, 0.15);
        $this->assertCount(2, $summary['stops']);
    }

    public function test_merged_points_count_every_reading_they_stand_for(): void
    {
        $trail = [[
            'latitude' => 14.5600, 'longitude' => 121.0200, 'accuracy' => 9.0,
            'recorded_at' => '2026-10-20 08:00:00', 'last_seen_at' => '2026-10-20 09:30:00', 'samples' => 91,
        ]];

        $summary = RouteSummary::for($this->login($trail));

        $this->assertSame(91, $summary['tracked_points']);
        $this->assertCount(1, $summary['stops']);
        $this->assertSame(90, $summary['stops'][0]['minutes']);
        $this->assertSame(1, $summary['gaps'], 'Nothing was received between 09:30 and the 10:00 sign-out.');
        $this->assertSame(30, $summary['longest_gap_minutes']);
    }

    public function test_vague_readings_are_counted_but_not_drawn(): void
    {
        $trail = array_merge(
            $this->stayingPut('2026-10-20 08:00:00', 5),
            [['latitude' => 14.2922, 'longitude' => 120.9998, 'accuracy' => '100000.000 m', 'recorded_at' => '2026-10-20 08:06:00']],
        );

        $summary = RouteSummary::for($this->login($trail, null));

        $this->assertSame(1, $summary['vague_points']);
        $this->assertCount(5, $summary['points']);
        $this->assertLessThan(100, $summary['distance_m']);
    }

    public function test_an_open_route_ends_at_its_last_reading(): void
    {
        $summary = RouteSummary::for($this->login($this->stayingPut('2026-10-20 08:00:00', 31), null));

        $this->assertSame(30, $summary['duration_minutes']);
        $this->assertSame(0, $summary['gaps']);
    }
}
