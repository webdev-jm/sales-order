<?php

namespace App\Services\Maps;

/**
 * Small geometry helpers for GPS points.
 */
final class Geo
{
    private const EARTH_RADIUS_M = 6371000;

    /**
     * Great-circle distance in metres.
     */
    public static function distance(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB): float
    {
        $latitudeDelta = deg2rad($latitudeB - $latitudeA);
        $longitudeDelta = deg2rad($longitudeB - $longitudeA);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * sin($longitudeDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_M * asin(min(1.0, sqrt($a)));
    }

    /**
     * Accuracy in metres from what the browser sent: a number, or text such as "74.000 m". Error text that the
     * sign-in form stores when the browser refuses ("User denied Geolocation") gives null.
     */
    public static function accuracyMeters(mixed $accuracy): ?float
    {
        if (is_int($accuracy) || is_float($accuracy)) {
            return (float) $accuracy;
        }

        if (is_string($accuracy) && preg_match('/^\s*(\d+(?:\.\d+)?)\s*m?\s*$/i', $accuracy, $match)) {
            return (float) $match[1];
        }

        return null;
    }

    /**
     * Whether a coordinate pair looks like a real position, not an empty or 0,0 value.
     */
    public static function isUsable(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && abs((float) $latitude) <= 90 && abs((float) $longitude) <= 180
            && !((float) $latitude == 0.0 && (float) $longitude == 0.0);
    }
}
