<?php

namespace App\Services;

use App\Support\AreaSettings;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Collection;
use RuntimeException;

class ObecSafetySchoolService
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function fetchSchools(): Collection
    {
        $config = config('services.obec_safety');
        $registerUrl = (string) ($config['register_url'] ?? '');
        $searchUrl = (string) ($config['school_search_url'] ?? '');
        $areaCode = AreaSettings::code();
        $timeout = max(5, (int) ($config['timeout'] ?? 20));

        if ($registerUrl === '' || $searchUrl === '' || $areaCode === '') {
            throw new RuntimeException('การตั้งค่า OSDS สำหรับดึงพิกัดโรงเรียนไม่ครบ');
        }

        $cookies = new CookieJar();

        $registerResponse = $this->http
            ->withoutVerifying()
            ->withOptions(['cookies' => $cookies])
            ->withHeaders(['User-Agent' => 'BigDataCPN1/1.0'])
            ->timeout($timeout)
            ->get($registerUrl)
            ->throw();

        preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $registerResponse->body(), $matches);
        $csrfToken = $matches[1] ?? null;

        if (! is_string($csrfToken) || trim($csrfToken) === '') {
            throw new RuntimeException('ไม่พบ CSRF token จากระบบ OSDS');
        }

        $response = $this->http
            ->withoutVerifying()
            ->withOptions(['cookies' => $cookies])
            ->withHeaders([
                'User-Agent' => 'BigDataCPN1/1.0',
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer' => $registerUrl,
            ])
            ->asForm()
            ->timeout($timeout)
            ->post($searchUrl, [
                'code' => $areaCode,
                'csrf_test_name' => $csrfToken,
            ])
            ->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('รูปแบบข้อมูลพิกัดจาก OSDS ไม่ถูกต้อง');
        }

        return collect($payload)
            ->map(function ($row) {
                $latitude = $this->normalizeCoordinate($row['latitude'] ?? null, -90, 90);
                $longitude = $this->normalizeCoordinate($row['longitude'] ?? null, -180, 180);

                return [
                    'ministry' => trim((string) ($row['sch_code'] ?? '')),
                    'school_name' => trim((string) ($row['sch_name'] ?? '')),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'maplink' => ($latitude !== null && $longitude !== null)
                        ? 'https://www.google.com/maps?q='.$latitude.','.$longitude
                        : null,
                ];
            })
            ->filter(fn (array $row) => $row['ministry'] !== '')
            ->values();
    }

    private function normalizeCoordinate(mixed $value, float $min, float $max): ?string
    {
        $coordinate = trim((string) $value);

        if ($coordinate === '' || ! is_numeric($coordinate)) {
            return null;
        }

        $number = (float) $coordinate;

        if ($number < $min || $number > $max) {
            return null;
        }

        return rtrim(rtrim(number_format($number, 6, '.', ''), '0'), '.');
    }
}
