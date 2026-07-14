<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SchoolDistanceService
{
    public function resolveAndPersistForSchool(string $schoolSmis, bool $force = false): ?string
    {
        $school = DB::table('system_school')
            ->where('smis', $schoolSmis)
            ->select('id', 'smis', 'lat', 'lng', 'length_km')
            ->first();

        if (! $school) {
            return null;
        }

        $existingDistance = $this->normalizeStoredDistance($school->length_km);

        if ($existingDistance !== null && ! $force) {
            return $existingDistance;
        }

        $distance = $this->fetchDrivingDistanceKm(
            trim((string) $school->lat),
            trim((string) $school->lng)
        );

        if ($distance === null) {
            return $force ? null : $existingDistance;
        }

        DB::table('system_school')
            ->where('id', $school->id)
            ->update([
                'length_km' => $distance,
            ]);

        return $distance;
    }

    public function fetchDrivingDistanceKm(string $schoolLat, string $schoolLng): ?string
    {
        if (! $this->isValidCoordinate($schoolLat, $schoolLng)) {
            return null;
        }

        $baseUrl = rtrim((string) config('services.school_distance.base_url'), '/');
        $profile = (string) config('services.school_distance.profile', 'driving');
        $timeout = (int) config('services.school_distance.timeout', 15);
        $officeLat = (string) config('services.school_distance.office_lat');
        $officeLng = (string) config('services.school_distance.office_lng');

        if ($baseUrl === '' || ! $this->isValidCoordinate($officeLat, $officeLng)) {
            return null;
        }

        $coordinates = $schoolLng.','.$schoolLat.';'.$officeLng.','.$officeLat;
        $url = $baseUrl.'/route/v1/'.$profile.'/'.$coordinates;

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->get($url, [
                    'overview' => 'false',
                    'skip_waypoints' => 'true',
                ]);

            if (! $response->successful()) {
                Log::warning('SchoolDistanceService route request failed', [
                    'school_lat' => $schoolLat,
                    'school_lng' => $schoolLng,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $distanceMeters = data_get($response->json(), 'routes.0.distance');

            if (! is_numeric($distanceMeters)) {
                return null;
            }

            $distanceKm = ((float) $distanceMeters) / 1000;

            if (! $this->isReasonableDistance($distanceKm)) {
                Log::warning('SchoolDistanceService unreasonable route distance', [
                    'school_lat' => $schoolLat,
                    'school_lng' => $schoolLng,
                    'distance_km' => $distanceKm,
                ]);

                return null;
            }

            return number_format($distanceKm, 1, '.', '');
        } catch (\Throwable $exception) {
            Log::warning('SchoolDistanceService route request exception', [
                'school_lat' => $schoolLat,
                'school_lng' => $schoolLng,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizeStoredDistance(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        if ($trimmed === '' || ! is_numeric($trimmed)) {
            return null;
        }

        $distance = (float) $trimmed;

        if (! $this->isReasonableDistance($distance)) {
            return null;
        }

        return number_format($distance, 1, '.', '');
    }

    private function isValidCoordinate(string $lat, string $lng): bool
    {
        return is_numeric($lat)
            && is_numeric($lng)
            && (float) $lat >= -90
            && (float) $lat <= 90
            && (float) $lng >= -180
            && (float) $lng <= 180;
    }

    private function isReasonableDistance(float $distanceKm): bool
    {
        $maxDistanceKm = (float) config('services.school_distance.max_distance_km', 200);

        return $distanceKm >= 0 && $distanceKm <= $maxDistanceKm;
    }
}
