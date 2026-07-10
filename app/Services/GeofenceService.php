<?php

namespace App\Services;

use App\Models\MemorialSite;
use App\Models\PersonOfInterest;

class GeofenceService
{
    private const EARTH_RADIUS_M = 6371000.0;

    /**
     * Calculate the great-circle (haversine) distance in meters between two coordinates.
     */
    public function distanceInMeters(
        float $fromLatitude,
        float $fromLongitude,
        float $toLatitude,
        float $toLongitude,
    ): float {
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_M * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Return the first memorial site of the person of interest whose geofence
     * contains the given coordinates, or null when outside all geofences.
     */
    public function isWithinMemorialSite(
        PersonOfInterest $personOfInterest,
        float $latitude,
        float $longitude,
    ): ?MemorialSite {
        return MemorialSite::query()
            ->where('person_of_interest_id', $personOfInterest->id)
            ->get()
            ->first(function (MemorialSite $site) use ($latitude, $longitude): bool {
                $distance = $this->distanceInMeters(
                    $latitude,
                    $longitude,
                    (float) $site->latitude,
                    (float) $site->longitude,
                );

                return $distance <= $site->geofence_radius_m;
            });
    }
}
