<?php

namespace Tests\Unit;

use App\Services\Maps\Geo;
use App\Services\Maps\LocationTrail;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocationTrailTest extends TestCase
{
    /**
     * @return array{latitude: float, longitude: float, accuracy: ?float, recorded_at: ?Carbon}
     */
    private function reading(float $latitude, float $longitude, ?string $at, ?float $accuracy = 10.0): array
    {
        return ['latitude' => $latitude, 'longitude' => $longitude, 'accuracy' => $accuracy, 'recorded_at' => $at ? Carbon::parse($at) : null];
    }

    public function test_readings_at_the_same_place_extend_one_point(): void
    {
        $result = LocationTrail::record([], [
            $this->reading(14.5600000, 121.0200000, '2026-10-20 08:00:00'),
            $this->reading(14.5600400, 121.0200300, '2026-10-20 08:01:00'),
            $this->reading(14.5599800, 121.0199900, '2026-10-20 08:02:00'),
        ]);

        $this->assertCount(1, $result['trail']);
        $this->assertSame(1, $result['added']);
        $this->assertSame(2, $result['merged']);
        $this->assertSame('2026-10-20 08:00:00', $result['trail'][0]['recorded_at']);
        $this->assertSame('2026-10-20 08:02:00', $result['trail'][0]['last_seen_at']);
        $this->assertSame(3, $result['trail'][0]['samples']);
    }

    public function test_a_reading_that_moved_starts_a_new_point(): void
    {
        $result = LocationTrail::record([], [
            $this->reading(14.5600, 121.0200, '2026-10-20 08:00:00'),
            $this->reading(14.5650, 121.0200, '2026-10-20 08:05:00'),
        ]);

        $this->assertCount(2, $result['trail']);
        $this->assertSame(2, $result['added']);
    }

    public function test_a_merged_point_moves_to_the_more_accurate_reading(): void
    {
        $result = LocationTrail::record([], [
            $this->reading(14.5600000, 121.0200000, '2026-10-20 08:00:00', 40.0),
            $this->reading(14.5601000, 121.0200500, '2026-10-20 08:01:00', 5.0),
        ]);

        $this->assertCount(1, $result['trail']);
        $this->assertSame(14.5601, $result['trail'][0]['latitude']);
        $this->assertSame(5.0, $result['trail'][0]['accuracy']);
    }

    public function test_vague_readings_and_readings_without_a_time_are_dropped(): void
    {
        $result = LocationTrail::record([], [
            $this->reading(14.2922, 120.9998, '2026-10-20 08:00:00', 100000.0),
            $this->reading(14.5600, 121.0200, null),
            $this->reading(14.5600, 121.0200, '2026-10-20 08:01:00'),
        ]);

        $this->assertCount(1, $result['trail']);
        $this->assertSame(2, $result['ignored']);
    }

    public function test_a_batch_sent_twice_is_stored_once(): void
    {
        $batch = [
            $this->reading(14.5600, 121.0200, '2026-10-20 08:00:00'),
            $this->reading(14.5650, 121.0200, '2026-10-20 08:05:00'),
        ];

        $first = LocationTrail::record([], $batch);
        $second = LocationTrail::record($first['trail'], $batch);

        $this->assertSame($first['trail'], $second['trail']);
        $this->assertSame(2, $second['ignored']);
    }

    public function test_readings_arriving_out_of_order_are_stored_in_time_order(): void
    {
        $result = LocationTrail::record([], [
            $this->reading(14.5700, 121.0200, '2026-10-20 08:10:00'),
            $this->reading(14.5600, 121.0200, '2026-10-20 08:00:00'),
        ]);

        $this->assertSame(['2026-10-20 08:00:00', '2026-10-20 08:10:00'], array_column($result['trail'], 'recorded_at'));
    }

    public function test_points_saved_before_merging_existed_still_count_as_the_newest_time(): void
    {
        $legacy = [['latitude' => 14.56, 'longitude' => 121.02, 'accuracy' => '74.000 m', 'recorded_at' => '2026-10-20 08:00:00']];

        $result = LocationTrail::record($legacy, [
            $this->reading(14.5600, 121.0200, '2026-10-20 07:59:00'),
            $this->reading(14.5600100, 121.0200100, '2026-10-20 08:01:00'),
        ]);

        $this->assertSame(1, $result['ignored']);
        $this->assertCount(1, $result['trail']);
        $this->assertSame(2, $result['trail'][0]['samples']);
    }

    public function test_the_trail_keeps_the_newest_points_when_it_is_full(): void
    {
        $readings = [];
        for ($i = 0; $i < LocationTrail::MAX_POINTS + 5; $i++) {
            $readings[] = $this->reading(14.5 + $i * 0.001, 121.0, Carbon::parse('2026-10-20 00:00:00')->addMinutes($i)->toDateTimeString());
        }

        $trail = LocationTrail::record([], $readings)['trail'];

        $this->assertCount(LocationTrail::MAX_POINTS, $trail);
        $this->assertSame(Carbon::parse('2026-10-20 00:00:00')->addMinutes(LocationTrail::MAX_POINTS + 4)->toDateTimeString(), end($trail)['recorded_at']);
    }

    public function test_reading_time_keeps_the_phone_time_inside_the_branch_login(): void
    {
        $timeIn = Carbon::parse('2026-10-20 08:00:00');
        $now = Carbon::parse('2026-10-20 12:00:00');

        $this->assertSame('2026-10-20 09:30:00', LocationTrail::readingTime('2026-10-20 09:30:00', $timeIn, null, $now)->toDateTimeString());
        $this->assertSame('2026-10-20 09:30:00', LocationTrail::readingTime(Carbon::parse('2026-10-20 09:30:00')->getTimestampMs(), $timeIn, null, $now)->toDateTimeString());
        $this->assertSame('2026-10-20 12:00:00', LocationTrail::readingTime(null, $timeIn, null, $now)->toDateTimeString());
        $this->assertSame('2026-10-20 12:00:00', LocationTrail::readingTime('2026-10-20 15:00:00', $timeIn, null, $now)->toDateTimeString());
    }

    public function test_reading_time_drops_readings_from_outside_the_branch_login(): void
    {
        $timeIn = Carbon::parse('2026-10-20 08:00:00');
        $timeOut = Carbon::parse('2026-10-20 17:00:00');
        $now = Carbon::parse('2026-10-21 09:00:00');

        $this->assertNull(LocationTrail::readingTime('2026-10-20 07:00:00', $timeIn, null, $now));
        $this->assertNull(LocationTrail::readingTime('2026-10-20 18:00:00', $timeIn, $timeOut, $now));
        $this->assertNull(LocationTrail::readingTime(null, $timeIn, $timeOut, $now), 'A reading with no time cannot be placed in an ended visit.');
        $this->assertSame('2026-10-20 16:59:00', LocationTrail::readingTime('2026-10-20 16:59:00', $timeIn, $timeOut, $now)->toDateTimeString());
    }

    public function test_malformed_readings_are_left_out(): void
    {
        $readings = LocationTrail::readingsFrom([
            ['latitude' => 14.56, 'longitude' => 121.02, 'accuracy' => '8 m', 'recorded_at' => 1],
            ['latitude' => 'x', 'longitude' => 121.02],
            ['latitude' => 0, 'longitude' => 0],
            'not a point',
        ]);

        $this->assertCount(1, $readings);
        $this->assertSame(8.0, $readings[0]['accuracy']);
    }

    /**
     * @return array<string, array{0: mixed, 1: ?float}>
     */
    public static function accuracies(): array
    {
        return [
            'number'          => [12.5, 12.5],
            'integer'         => [8, 8.0],
            'text with unit'  => ['74.000 m', 74.0],
            'text, no unit'   => ['5', 5.0],
            'browser error'   => ['User denied Geolocation', null],
            'empty'           => [null, null],
        ];
    }

    #[DataProvider('accuracies')]
    public function test_accuracy_is_read_in_meters(mixed $accuracy, ?float $expected): void
    {
        $this->assertSame($expected, Geo::accuracyMeters($accuracy));
    }

    public function test_distance_is_measured_in_meters(): void
    {
        $this->assertEqualsWithDelta(111195, Geo::distance(14.0, 121.0, 15.0, 121.0), 50);
        $this->assertSame(0.0, Geo::distance(14.5, 121.0, 14.5, 121.0));
    }
}
