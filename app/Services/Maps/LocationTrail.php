<?php

namespace App\Services\Maps;

use App\Models\BranchLogin;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rules for storing the GPS trail of a branch login.
 *
 * The phone keeps every reading in the browser first (partials/branch-location-tracker), so nothing is lost while
 * it is offline, and sends what it holds every few minutes and again with the branch sign-out. A batch can arrive
 * late or twice, so readings at or before the newest one already stored are skipped.
 *
 * A user who stays in a store would otherwise add hundreds of near-identical points, so a reading close to the
 * previous point only extends it: last_seen_at moves forward and samples counts the readings it stands for.
 * Fixes too vague to place the user (the browser's network fallback reports kilometres) are not stored.
 */
final class LocationTrail
{
    /**
     * Points kept per branch login. With stationary readings merged this covers far more than a working day.
     */
    public const MAX_POINTS = 1500;

    /**
     * Readings less accurate than this are dropped.
     */
    public const MAX_ACCURACY_M = 1000;

    /**
     * A reading within this distance of the previous point extends it. The radius follows the reading's own
     * accuracy, kept between these bounds, so indoor GPS drift does not start a new point.
     */
    public const MERGE_RADIUS_MIN_M = 25;

    public const MERGE_RADIUS_MAX_M = 60;

    /**
     * Readings from a phone that was offline are still accepted for a branch login that ended this long ago.
     */
    public const LATE_SYNC_DAYS = 3;

    /**
     * Add raw readings from the phone to a branch login, under a row lock so two requests cannot overwrite
     * each other's points.
     *
     * @param array<int, mixed> $rawReadings
     * @return array{points: int, added: int, merged: int, ignored: int}
     */
    public static function save(BranchLogin $branchLogin, array $rawReadings): array
    {
        $readings = self::readingsFrom($rawReadings);

        return DB::connection($branchLogin->getConnectionName())->transaction(function () use ($branchLogin, $readings, $rawReadings) {
            $login = BranchLogin::whereKey($branchLogin->getKey())->lockForUpdate()->first();

            $now = now();
            $timeIn = Carbon::parse($login->time_in);
            $timeOut = $login->time_out ? Carbon::parse($login->time_out) : null;

            foreach ($readings as $index => $reading) {
                $readings[$index]['recorded_at'] = self::readingTime($reading['recorded_at'], $timeIn, $timeOut, $now);
            }

            $result = self::record($login->location_trail ?? [], $readings);

            if ($result['added'] > 0 || $result['merged'] > 0) {
                $login->update(['location_trail' => $result['trail']]);
                $branchLogin->setAttribute('location_trail', $result['trail']);
            }

            return [
                'points'  => count($result['trail']),
                'added'   => $result['added'],
                'merged'  => $result['merged'],
                'ignored' => $result['ignored'] + (count($rawReadings) - count($readings)),
            ];
        });
    }

    /**
     * Readings from the phone with a valid position, accuracy in metres. Anything malformed is left out.
     *
     * @param array<int, mixed> $rawReadings
     * @return array<int, array{latitude: float, longitude: float, accuracy: ?float, recorded_at: mixed}>
     */
    public static function readingsFrom(array $rawReadings): array
    {
        $readings = [];

        foreach (array_slice($rawReadings, 0, self::MAX_POINTS) as $raw) {
            if (!is_array($raw) || !Geo::isUsable($raw['latitude'] ?? null, $raw['longitude'] ?? null)) {
                continue;
            }

            $readings[] = [
                'latitude'    => (float) $raw['latitude'],
                'longitude'   => (float) $raw['longitude'],
                'accuracy'    => Geo::accuracyMeters($raw['accuracy'] ?? null),
                'recorded_at' => $raw['recorded_at'] ?? null,
            ];
        }

        return $readings;
    }

    /**
     * @param array<int, array<string, mixed>> $trail
     * @param array<int, array{latitude: float, longitude: float, accuracy: ?float, recorded_at: ?CarbonInterface}> $readings  readings with a null time are dropped
     * @return array{trail: array<int, array<string, mixed>>, added: int, merged: int, ignored: int}
     */
    public static function record(array $trail, array $readings): array
    {
        usort($readings, fn (array $a, array $b) => ($a['recorded_at']?->getTimestamp() ?? 0) <=> ($b['recorded_at']?->getTimestamp() ?? 0));

        $added = 0;
        $merged = 0;
        $ignored = 0;
        $newest = self::newestTime($trail);

        foreach ($readings as $reading) {
            if ($reading['recorded_at'] === null || ($reading['accuracy'] !== null && $reading['accuracy'] > self::MAX_ACCURACY_M)) {
                $ignored++;

                continue;
            }

            $recordedAt = $reading['recorded_at']->toDateTimeString();
            $lastIndex = array_key_last($trail);
            $last = $lastIndex === null ? null : $trail[$lastIndex];
            $samePlace = $last !== null && self::isSamePlace($last, $reading);

            if ($newest !== null && ($recordedAt < $newest || ($recordedAt === $newest && $samePlace))) {
                $ignored++;

                continue;
            }

            if ($samePlace) {
                $trail[$lastIndex] = self::extend($last, $reading, $recordedAt);
                $merged++;
            } else {
                $trail[] = [
                    'latitude'     => $reading['latitude'],
                    'longitude'    => $reading['longitude'],
                    'accuracy'     => $reading['accuracy'],
                    'recorded_at'  => $recordedAt,
                    'last_seen_at' => $recordedAt,
                    'samples'      => 1,
                ];
                $added++;
            }

            $newest = $recordedAt;
        }

        if (count($trail) > self::MAX_POINTS) {
            $trail = array_slice($trail, -self::MAX_POINTS);
        }

        return ['trail' => array_values($trail), 'added' => $added, 'merged' => $merged, 'ignored' => $ignored];
    }

    /**
     * The time of a reading: the phone's own time when it sent one (readings kept offline arrive late), otherwise
     * the time it reached the server. A reading from outside the branch login (before sign-in, or after sign-out)
     * belongs to no visit and gives null, so it is dropped. A phone clock running ahead is capped at now.
     */
    public static function readingTime(mixed $clientTime, CarbonInterface $timeIn, ?CarbonInterface $timeOut, CarbonInterface $now): ?CarbonInterface
    {
        if (!is_numeric($clientTime) && !is_string($clientTime)) {
            return $timeOut === null ? $now : null;
        }

        try {
            $time = is_numeric($clientTime)
                ? Carbon::createFromTimestampMs((int) $clientTime, $now->getTimezone())
                : Carbon::parse($clientTime)->setTimezone($now->getTimezone());
        } catch (\Throwable) {
            return $timeOut === null ? $now : null;
        }

        if ($time->gt($now)) {
            $time = $now->copy();
        }

        if ($time->lt($timeIn->copy()->subMinutes(5)) || ($timeOut !== null && $time->gt($timeOut->copy()->addMinutes(2)))) {
            return null;
        }

        return $time;
    }

    /**
     * @param array<int, array<string, mixed>> $trail
     */
    private static function newestTime(array $trail): ?string
    {
        $newest = null;

        foreach ($trail as $point) {
            $time = (string) ($point['last_seen_at'] ?? $point['recorded_at'] ?? '');
            if ($time !== '' && ($newest === null || $time > $newest)) {
                $newest = $time;
            }
        }

        return $newest;
    }

    /**
     * @param array<string, mixed> $point
     * @param array{latitude: float, longitude: float, accuracy: ?float} $reading
     */
    private static function isSamePlace(array $point, array $reading): bool
    {
        if (!Geo::isUsable($point['latitude'] ?? null, $point['longitude'] ?? null)) {
            return false;
        }

        $radius = min(self::MERGE_RADIUS_MAX_M, max(self::MERGE_RADIUS_MIN_M, $reading['accuracy'] ?? self::MERGE_RADIUS_MIN_M));

        return Geo::distance((float) $point['latitude'], (float) $point['longitude'], $reading['latitude'], $reading['longitude']) <= $radius;
    }

    /**
     * Extend a stored point with a new reading of the same place, moving it to the more accurate position.
     *
     * @param array<string, mixed> $point
     * @param array{latitude: float, longitude: float, accuracy: ?float} $reading
     * @return array<string, mixed>
     */
    private static function extend(array $point, array $reading, string $recordedAt): array
    {
        $pointAccuracy = Geo::accuracyMeters($point['accuracy'] ?? null);

        if ($reading['accuracy'] !== null && ($pointAccuracy === null || $reading['accuracy'] < $pointAccuracy)) {
            $point['latitude'] = $reading['latitude'];
            $point['longitude'] = $reading['longitude'];
            $point['accuracy'] = $reading['accuracy'];
        }

        $point['recorded_at'] ??= $recordedAt;
        $point['last_seen_at'] = $recordedAt;
        $point['samples'] = (int) ($point['samples'] ?? 1) + 1;

        return $point;
    }
}
