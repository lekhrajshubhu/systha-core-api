<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NearByVendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $distanceKm = is_numeric($this->distance_km ?? null) ? (float) $this->distance_km : 0.0;
        $durationMinutes = static::estimateDurationMinutes($distanceKm);
        $distancePayload = static::formatDistance($distanceKm);
        $addressParts = array_filter([
            $this->address->add1 ?? null,
            $this->address->city ?? null,
            $this->address->state ?? null,
        ], function ($part) {
            return $part !== null && trim((string) $part) !== '';
        });

        $totalReviews = mt_rand(0, 500);
        $averageRating = mt_rand(30, 50) / 10;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'vendor_code' => $this->vendor_code,
            'logo' => $this->logo,
            'address' => implode(', ', $addressParts),
            'distance' => [
                'value' => $distancePayload['value'],
                'unit' => $distancePayload['unit'],
                'text' => number_format($distancePayload['value'], 1) . ' ' . $distancePayload['unit'],
            ],
            'duration' => [
                'minutes' => $durationMinutes,
                'text' => static::formatDurationText($durationMinutes),
            ],
            'ratings' => [
                'total_reviews' => $totalReviews,
                'average_rating' => number_format($averageRating, 1, '.', ''),
            ],
            'availability' => [
                'day' => ['today', 'tomorrow', 'after 2 days'][array_rand(['today', 'tomorrow', 'after 2 days'])],
                'min_price' => number_format(mt_rand(2000, 25000) / 100, 2, '.', ''),
            ],
        ];
    }

    private static function formatDistance(float $distanceKm): array
    {
        $longDistanceThresholdKm = 50;
        if ($distanceKm >= $longDistanceThresholdKm) {
            $distanceMiles = $distanceKm * 0.621371;
            return [
                'value' => round($distanceMiles, 1),
                'unit' => 'mi',
            ];
        }

        return [
            'value' => round($distanceKm, 1),
            'unit' => 'km',
        ];
    }

    private static function estimateDurationMinutes(float $distanceKm): int
    {
        if ($distanceKm <= 0) {
            return 0;
        }

        $averageSpeedKmph = 40;

        return (int) ceil(($distanceKm / $averageSpeedKmph) * 60);
    }

    private static function formatDurationText(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' mins';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' hr';
        }

        return $hours . ' hr ' . $remainingMinutes . ' mins';
    }
}
