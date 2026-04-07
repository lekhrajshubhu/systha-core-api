<?php

namespace Systha\Core\Support;



class Geo
{
    public static function distanceMilesFromTo($from, $to): ?string
    {
        [$fromLat, $fromLng] = self::extractLatLng($from);
        [$toLat, $toLng] = self::extractLatLng($to);

        if ($fromLat === null || $fromLng === null || $toLat === null || $toLng === null) {
            return null;
        }

        return self::formatMiles(self::haversineMiles($fromLat, $fromLng, $toLat, $toLng));
    }

    public static function distanceMilesRawFromTo($from, $to): ?float
    {
        [$fromLat, $fromLng] = self::extractLatLng($from);
        [$toLat, $toLng] = self::extractLatLng($to);

        if ($fromLat === null || $fromLng === null || $toLat === null || $toLng === null) {
            return null;
        }

        return self::haversineMiles($fromLat, $fromLng, $toLat, $toLng);
    }

    public static function formatMiles(float $miles, int $decimals = 2): string
    {
        return number_format($miles, $decimals) . ' miles';
    }

    private static function extractLatLng($value): array
    {
        if (is_array($value)) {
            $lat = $value['lat'] ?? null;
            $lng = $value['lng'] ?? null;
            return [$lat, $lng];
        }

        if (is_object($value)) {
            $lat = $value->lat ?? null;
            $lng = $value->lng ?? null;
            return [$lat, $lng];
        }

        return [null, null];
    }

    private static function haversineMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3958.7613; // miles
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
