<?php

namespace App\Services\Maps;

use App\Models\BranchLogin;
use Illuminate\Support\Carbon;

/**
 * Turns a branch login's GPS trail into what the MAPS report draws: the route line, the places the user stayed,
 * and the totals for the route table.
 *
 * Phones report a slightly different position each minute even when nobody moves. Adding those steps up would
 * show kilometres of travel for a user who sat in one store, so the line only moves on when a point is clearly
 * away from the last one kept, and readings too vague to place the user are left off the line.
 */
final class RouteSummary
{
    /**
     * Readings vaguer than this are counted but not drawn.
     */
    public const LINE_ACCURACY_M = 200;

    /**
     * The line moves on once a point is this far from the last point kept, or further if both readings are vague.
     */
    public const MIN_STEP_M = 30;

    /**
     * A stay is time spent within this distance of where it began.
     */
    public const STOP_RADIUS_M = 75;

    public const STOP_MIN_MINUTES = 10;

    /**
     * No reading for this long while signed in counts as a tracking gap (app closed, GPS off, no signal).
     */
    public const GAP_MINUTES = 15;

    /**
     * @return array{
     *     coordinates: array<int, array{0: float, 1: float}>,
     *     points: array<int, array<string, mixed>>,
     *     stops: array<int, array<string, mixed>>,
     *     distance_m: float,
     *     duration_minutes: int,
     *     tracked_points: int,
     *     vague_points: int,
     *     gaps: int,
     *     longest_gap_minutes: int
     * }
     */
    public static function for(BranchLogin $login): array
    {
        $readings = self::readings($login);
        $usable = array_values(array_filter($readings, fn (array $r) => $r['accuracy'] === null || $r['accuracy'] <= self::LINE_ACCURACY_M));

        $line = [];
        if (Geo::isUsable($login->latitude, $login->longitude)) {
            $line[] = ['latitude' => (float) $login->latitude, 'longitude' => (float) $login->longitude, 'accuracy' => Geo::accuracyMeters($login->accuracy)];
        }
        foreach ($usable as $reading) {
            $line[] = $reading;
        }
        if (!is_null($login->time_out) && Geo::isUsable($login->time_out_latitude, $login->time_out_longitude)) {
            $line[] = ['latitude' => (float) $login->time_out_latitude, 'longitude' => (float) $login->time_out_longitude, 'accuracy' => Geo::accuracyMeters($login->time_out_accuracy)];
        }

        $kept = [];
        $distance = 0.0;
        foreach ($line as $index => $point) {
            $last = end($kept) ?: null;
            if ($last === null) {
                $kept[] = $point;

                continue;
            }

            $step = Geo::distance($last['latitude'], $last['longitude'], $point['latitude'], $point['longitude']);
            $threshold = max(self::MIN_STEP_M, min($last['accuracy'] ?? 0, $point['accuracy'] ?? 0));
            $isEnd = $index === array_key_last($line);

            if ($step >= $threshold || ($isEnd && $step > 0)) {
                $distance += $step;
                $kept[] = $point;
            }
        }

        $timeIn = Carbon::parse($login->time_in);
        $lastSeen = $readings === [] ? $timeIn : Carbon::parse(end($readings)['last_seen_at']);
        $end = $login->time_out ? Carbon::parse($login->time_out) : $lastSeen->max($timeIn);
        [$gaps, $longestGap] = self::gaps($readings, $timeIn, $end);

        return [
            'coordinates'         => array_map(fn (array $p) => [$p['longitude'], $p['latitude']], $kept),
            'points'              => $usable,
            'stops'               => self::stops($usable),
            'distance_m'          => round($distance, 1),
            'duration_minutes'    => (int) $timeIn->diffInMinutes($end, true),
            'tracked_points'      => array_sum(array_column($readings, 'samples')),
            'vague_points'        => count($readings) - count($usable),
            'gaps'                => $gaps,
            'longest_gap_minutes' => $longestGap,
        ];
    }

    /**
     * The stored trail in time order, in one shape whether a point was saved before or after readings were merged.
     *
     * @return array<int, array{latitude: float, longitude: float, accuracy: ?float, recorded_at: string, last_seen_at: string, samples: int}>
     */
    public static function readings(BranchLogin $login): array
    {
        $readings = [];

        foreach ($login->location_trail ?? [] as $point) {
            if (!is_array($point) || !Geo::isUsable($point['latitude'] ?? null, $point['longitude'] ?? null) || empty($point['recorded_at'])) {
                continue;
            }

            $readings[] = [
                'latitude'     => (float) $point['latitude'],
                'longitude'    => (float) $point['longitude'],
                'accuracy'     => Geo::accuracyMeters($point['accuracy'] ?? null),
                'recorded_at'  => (string) $point['recorded_at'],
                'last_seen_at' => (string) ($point['last_seen_at'] ?? $point['recorded_at']),
                'samples'      => max(1, (int) ($point['samples'] ?? 1)),
            ];
        }

        usort($readings, fn (array $a, array $b) => $a['recorded_at'] <=> $b['recorded_at']);

        return $readings;
    }

    /**
     * Places the user stayed at least STOP_MIN_MINUTES.
     *
     * @param array<int, array<string, mixed>> $readings
     * @return array<int, array{latitude: float, longitude: float, from: string, until: string, minutes: int, readings: int}>
     */
    private static function stops(array $readings): array
    {
        $stops = [];
        $group = [];

        $close = function (array $group) use (&$stops): void {
            if ($group === []) {
                return;
            }
            $from = Carbon::parse($group[0]['recorded_at']);
            $until = Carbon::parse(end($group)['last_seen_at']);
            $minutes = (int) $from->diffInMinutes($until, true);

            if ($minutes >= self::STOP_MIN_MINUTES) {
                $stops[] = [
                    'latitude'  => array_sum(array_column($group, 'latitude')) / count($group),
                    'longitude' => array_sum(array_column($group, 'longitude')) / count($group),
                    'from'      => $from->toDateTimeString(),
                    'until'     => $until->toDateTimeString(),
                    'minutes'   => $minutes,
                    'readings'  => array_sum(array_column($group, 'samples')),
                ];
            }
        };

        foreach ($readings as $reading) {
            $anchor = $group[0] ?? null;

            if ($anchor !== null && Geo::distance($anchor['latitude'], $anchor['longitude'], $reading['latitude'], $reading['longitude']) > self::STOP_RADIUS_M) {
                $close($group);
                $group = [];
            }

            $group[] = $reading;
        }
        $close($group);

        return $stops;
    }

    /**
     * @param array<int, array<string, mixed>> $readings
     * @return array{0: int, 1: int}
     */
    private static function gaps(array $readings, Carbon $timeIn, Carbon $end): array
    {
        $moments = [[$timeIn, $timeIn]];
        foreach ($readings as $reading) {
            $moments[] = [Carbon::parse($reading['recorded_at']), Carbon::parse($reading['last_seen_at'])];
        }
        $moments[] = [$end, $end];

        $gaps = 0;
        $longest = 0;
        for ($i = 1; $i < count($moments); $i++) {
            $minutes = (int) $moments[$i - 1][1]->diffInMinutes($moments[$i][0], false);
            if ($minutes >= self::GAP_MINUTES) {
                $gaps++;
                $longest = max($longest, $minutes);
            }
        }

        return [$gaps, $longest];
    }
}
