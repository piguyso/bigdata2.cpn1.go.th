<?php

namespace App\Services;

use App\Support\AreaSettings;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ObecAssetImportService
{
    private const BASE_URL = 'https://asset.bopp-obec.info';

    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function getRemoteContext(?int $academicYear = null, ?string $term = null): array
    {
        return [
            'area_code' => AreaSettings::code(),
            'area_name' => AreaSettings::name(),
            'academic_year' => (string) ($academicYear ?: ''),
            'term' => (string) ($term ?: ''),
            'source_url' => $this->schoolReportUrl(),
        ];
    }

    public function preview(?int $academicYear = null, ?string $term = null): array
    {
        $schools = $this->fetchSchoolRows();
        $localSchools = $this->localSchoolMap();
        $summary = $this->summarizeSchools($schools, $localSchools);

        return [
            ...$this->getRemoteContext($academicYear, $term),
            'summary' => [
                ...$summary,
                'building_records_count' => 0,
                'detail_fetch_note' => 'ข้อมูลสิ่งก่อสร้างจะดึงจากหน้ารายโรงเรียนในขั้นตอนนำเข้า',
            ],
            'schools_sample' => array_slice(array_map(fn (array $school) => [
                'school_smis' => $school['school_smis'],
                'school_name' => $school['school_name'],
                'logo_bytes' => $school['logo_bytes'],
                'matched' => isset($localSchools[$school['school_smis']]),
            ], $schools), 0, 12),
            'warnings' => $summary['warnings'],
        ];
    }

    public function import(string $mode = 'replace', ?int $createdBy = null, ?int $academicYear = null, ?string $term = null): array
    {
        $schools = $this->fetchSchoolRows();
        $localSchools = $this->localSchoolMap();
        $summary = $this->summarizeSchools($schools, $localSchools);
        $details = $this->fetchBuildingDetails($schools);

        $buildingRows = [];
        foreach ($schools as $school) {
            foreach ($details[$school['school_smis']]['buildings'] ?? [] as $building) {
                $buildingRows[] = [
                    ...$building,
                    'school_id' => $localSchools[$school['school_smis']]['id'] ?? null,
                    'school_smis' => $school['school_smis'],
                    'school_name' => $school['school_name'],
                ];
            }
        }

        $warnings = array_values(array_unique(array_merge($summary['warnings'], $this->detailWarnings($details))));

        $importId = DB::transaction(function () use ($schools, $localSchools, $buildingRows, $warnings, $summary, $mode, $createdBy) {
            if ($mode === 'replace') {
                DB::table('obec_asset_imports')
                    ->where('area_code', AreaSettings::code())
                    ->where('academic_year', (string) $academicYear)
                    ->where('term', (string) $term)
                    ->delete();
            }

            $importId = DB::table('obec_asset_imports')->insertGetId([
                'area_code' => AreaSettings::code(),
                'area_name' => AreaSettings::name(),
                'academic_year' => (string) $academicYear,
                'term' => (string) $term,
                'source_url' => $this->schoolReportUrl(),
                'mode' => $mode,
                'school_rows_count' => count($schools),
                'school_logos_count' => $summary['school_logos_count'],
                'building_records_count' => count($buildingRows),
                'matched_schools_count' => $summary['matched_schools_count'],
                'unmatched_schools_count' => $summary['unmatched_schools_count'],
                'warnings' => json_encode($warnings, JSON_UNESCAPED_UNICODE),
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->storeSchools($importId, $schools, $localSchools);
            $this->storeBuildings($importId, $buildingRows);

            return $importId;
        });

        return [
            ...$this->getRemoteContext($academicYear, $term),
            'import_id' => $importId,
            'summary' => [
                ...$summary,
                'building_records_count' => count($buildingRows),
            ],
            'warnings' => $warnings,
        ];
    }

    public function deleteImport(int $importId): array
    {
        $import = DB::table('obec_asset_imports')->where('id', $importId)->first();

        if (! $import) {
            throw new RuntimeException('ไม่พบชุดข้อมูล OBEC Asset ที่ต้องการลบ');
        }

        $schoolCount = DB::table('obec_asset_schools')->where('import_id', $importId)->count();
        $buildingCount = DB::table('obec_asset_buildings')->where('import_id', $importId)->count();

        DB::table('obec_asset_imports')->where('id', $importId)->delete();

        return [
            'deleted_schools' => $schoolCount,
            'deleted_buildings' => $buildingCount,
        ];
    }

    private function fetchSchoolRows(): array
    {
        $html = $this->fetchHtml($this->schoolReportUrl());
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $tbodyStart = stripos($html, '<tbody');
        $tbodyEnd = stripos($html, '</tbody>', $tbodyStart === false ? 0 : $tbodyStart);
        $tbody = ($tbodyStart !== false && $tbodyEnd !== false)
            ? substr($html, $tbodyStart, $tbodyEnd - $tbodyStart + 8)
            : '';

        if ($tbody === '') {
            throw new RuntimeException('ไม่พบตารางรายชื่อโรงเรียนจาก OBEC Asset');
        }

        $rows = [];
        preg_match_all('/<tr[\s\S]*?<\/tr>/i', $tbody, $matches);

        foreach ($matches[0] as $rowHtml) {
            preg_match_all('/<td[^>]*>([\s\S]*?)<\/td>/i', $rowHtml, $cells);
            $values = array_map(fn (string $value) => $this->cleanText($value), $cells[1] ?? []);

            $logoSrc = $this->matchFirst('/<img[^>]+src="([^"]+)"/iu', $rowHtml, 1);
            $detailPath = $this->matchFirst('/href="([^"]*BuildingBySchoolID\?SchoolID=[^"]+)"/iu', $rowHtml, 1);
            $logo = $this->decodeBase64Image($logoSrc);
            $schoolSmis = preg_replace('/\D+/', '', (string) ($values[1] ?? ''));

            if ($schoolSmis === '') {
                continue;
            }

            $rows[] = [
                'school_smis' => $schoolSmis,
                'school_name' => (string) ($values[2] ?? ''),
                'subdistrict' => (string) ($values[3] ?? ''),
                'district' => (string) ($values[4] ?? ''),
                'province' => (string) ($values[5] ?? ''),
                'detail_url' => $this->absoluteUrl($detailPath),
                'logo' => $logo,
                'logo_bytes' => strlen($logo['bytes'] ?? ''),
                'raw_row' => $values,
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('ไม่สามารถอ่านข้อมูลโรงเรียนจาก OBEC Asset ได้');
        }

        return $rows;
    }

    private function fetchBuildingDetails(array $schools): array
    {
        $details = [];

        foreach (array_chunk($schools, 12) as $chunk) {
            $responses = $this->http->pool(function (Pool $pool) use ($chunk) {
                $requests = [];
                foreach ($chunk as $school) {
                    $requests[] = $pool->as($school['school_smis'])
                        ->timeout($this->timeout())
                        ->get($school['detail_url']);
                }

                return $requests;
            });

            foreach ($chunk as $school) {
                $response = $responses[$school['school_smis']] ?? null;
                if (! $response || $response->failed()) {
                    $details[$school['school_smis']] = [
                        'buildings' => [],
                        'warning' => 'ไม่สามารถดึงรายละเอียดสิ่งก่อสร้าง: '.$school['school_name'].' ['.$school['school_smis'].']',
                    ];
                    continue;
                }

                $details[$school['school_smis']] = [
                    'buildings' => $this->parseBuildingCards((string) $response->body()),
                    'warning' => null,
                ];
            }
        }

        return $details;
    }

    private function parseBuildingCards(string $html): array
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sectionIndex = mb_strpos($html, 'ข้อมูลสิ่งก่อสร้างของโรงเรียน');
        $section = $sectionIndex === false ? $html : mb_substr($html, $sectionIndex);
        $section = preg_replace('/data:image\/[^"]+/iu', '[base64-image]', $section) ?? $section;

        preg_match_all('/<div class="col-md-4 d-flex align-items-stretch">[\s\S]*?<div>\s*<ul class="list-group list-group-flush">[\s\S]*?<\/ul>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/u', $section, $matches);

        $buildings = [];
        foreach ($matches[0] as $card) {
            $lines = [];
            preg_match_all('/<div class="text-bold">([\s\S]*?)<\/div>/u', $card, $lineMatches);
            foreach ($lineMatches[1] ?? [] as $lineHtml) {
                $line = $this->cleanText($lineHtml);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }

            $extraImages = [];
            preg_match_all('/<img(?![^>]*card-img-top)[^>]+src="([^"]+)"/iu', $card, $imageMatches);
            foreach ($imageMatches[1] ?? [] as $src) {
                if (trim($src) !== '') {
                    $extraImages[] = $this->absoluteUrl($src);
                }
            }

            $buildings[] = [
                'building_type' => $this->cleanText($this->matchFirst('/<div class="ribbon bg-success text-lg">\s*([\s\S]*?)\s*<\/div>/u', $card, 1)),
                'building_model' => $this->stripPrefix($this->firstLine($lines, 'แบบสิ่งก่อสร้าง'), 'แบบสิ่งก่อสร้าง'),
                'main_image_url' => $this->absoluteUrl($this->matchFirst('/<img class="card-img-top" src="([^"]+)"/iu', $card, 1)),
                'rooms_design' => $this->lineInt($lines, 'จำนวนห้องตามแบบ'),
                'rooms_actual' => $this->lineInt($lines, 'จำนวนห้องจริง'),
                'rooms_special' => $this->lineInt($lines, 'จำนวนห้องพิเศษ'),
                'extension_classroom' => $this->lineInt($lines, 'ต่อเติมเป็นห้องเรียน'),
                'extension_special' => $this->lineInt($lines, 'ต่อเติมเป็นห้องพิเศษ'),
                'construction_year' => $this->lineInt($lines, 'ก่อสร้างเมื่อปี พ.ศ.'),
                'age_years' => $this->lineInt($lines, 'อายุการใช้งาน'),
                'budget' => $this->lineMoney($lines, 'งบประมาณ'),
                'budget_source' => $this->stripPrefix($this->firstLine($lines, 'แหล่งที่มาของงบประมาณ'), 'แหล่งที่มาของงบประมาณ'),
                'condition' => $this->stripPrefix($this->firstLine($lines, 'สภาพการใช้งาน'), 'สภาพการใช้งาน'),
                'usage_status' => $this->stripPrefix($this->firstLine($lines, 'สถานะการใช้งาน'), 'สถานะการใช้งาน'),
                'extra_images' => $extraImages,
                'payload' => ['lines' => $lines],
            ];
        }

        return $buildings;
    }

    private function storeSchools(int $importId, array $schools, array $localSchools): void
    {
        $rows = [];
        foreach ($schools as $school) {
            $logoPath = $this->storeLogo($school['school_smis'], $school['logo']);

            if ($logoPath !== null && isset($localSchools[$school['school_smis']])) {
                DB::table('system_school')
                    ->where('id', $localSchools[$school['school_smis']]['id'])
                    ->update(['logo_path' => $logoPath]);
            }

            $rows[] = [
                'import_id' => $importId,
                'school_id' => $localSchools[$school['school_smis']]['id'] ?? null,
                'school_smis' => $school['school_smis'],
                'school_name' => $school['school_name'],
                'subdistrict' => $school['subdistrict'],
                'district' => $school['district'],
                'province' => $school['province'],
                'detail_url' => $school['detail_url'],
                'logo_path' => $logoPath,
                'logo_mime' => $school['logo']['mime'] ?? null,
                'logo_hash' => isset($school['logo']['bytes']) ? hash('sha256', $school['logo']['bytes']) : null,
                'logo_bytes' => $school['logo_bytes'],
                'raw_row' => json_encode($school['raw_row'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('obec_asset_schools')->insert($chunk);
        }
    }

    private function storeBuildings(int $importId, array $buildings): void
    {
        $rows = array_map(fn (array $row) => [
            'import_id' => $importId,
            'school_id' => $row['school_id'],
            'school_smis' => $row['school_smis'],
            'school_name' => $row['school_name'],
            'building_type' => $row['building_type'],
            'building_model' => $row['building_model'],
            'main_image_url' => $row['main_image_url'],
            'rooms_design' => $row['rooms_design'],
            'rooms_actual' => $row['rooms_actual'],
            'rooms_special' => $row['rooms_special'],
            'extension_classroom' => $row['extension_classroom'],
            'extension_special' => $row['extension_special'],
            'construction_year' => $row['construction_year'],
            'age_years' => $row['age_years'],
            'budget' => $row['budget'],
            'budget_source' => $row['budget_source'],
            'condition' => $row['condition'],
            'usage_status' => $row['usage_status'],
            'extra_images' => json_encode($row['extra_images'], JSON_UNESCAPED_UNICODE),
            'payload' => json_encode($row['payload'], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ], $buildings);

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('obec_asset_buildings')->insert($chunk);
        }
    }

    private function storeLogo(string $schoolSmis, array $logo): ?string
    {
        $bytes = $logo['bytes'] ?? null;
        if (! is_string($bytes) || $bytes === '') {
            return null;
        }

        $extension = match ($logo['mime'] ?? '') {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $path = 'school-logos/asset/'.$schoolSmis.'.'.$extension;
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    private function summarizeSchools(array $schools, array $localSchools): array
    {
        $matched = 0;
        $warnings = [];
        foreach ($schools as $school) {
            if (isset($localSchools[$school['school_smis']])) {
                $matched++;
            } else {
                $warnings[] = 'ไม่พบโรงเรียนในฐานข้อมูล local: '.$school['school_name'].' ['.$school['school_smis'].']';
            }
        }

        return [
            'school_rows_count' => count($schools),
            'school_logos_count' => count(array_filter($schools, fn (array $school) => ($school['logo_bytes'] ?? 0) > 0)),
            'matched_schools_count' => $matched,
            'unmatched_schools_count' => count($schools) - $matched,
            'warnings' => $warnings,
        ];
    }

    private function detailWarnings(array $details): array
    {
        return array_values(array_filter(array_map(fn (array $detail) => $detail['warning'] ?? null, $details)));
    }

    private function localSchoolMap(): array
    {
        $map = [];

        DB::table('system_school')
            ->select('id', 'smis', 'ministry', 'schoolname')
            ->get()
            ->each(function ($school) use (&$map) {
                $keys = array_filter(array_unique([
                    preg_replace('/\D+/', '', (string) $school->ministry),
                    preg_replace('/\D+/', '', (string) $school->smis),
                ]));
                $value = ['id' => (int) $school->id, 'schoolname' => (string) $school->schoolname];

                foreach ($keys as $key) {
                    $map[$key] = $value;
                }
            });

        return $map;
    }

    private function decodeBase64Image(string $src): array
    {
        if (! preg_match('/^data:(image\/[a-z0-9.+-]+);base64,\s*(.+)$/is', trim($src), $matches)) {
            return [];
        }

        $bytes = base64_decode(preg_replace('/\s+/', '', $matches[2]), true);

        return is_string($bytes)
            ? ['mime' => strtolower($matches[1]), 'bytes' => $bytes]
            : [];
    }

    private function fetchHtml(string $url): string
    {
        $response = $this->http
            ->timeout($this->timeout())
            ->get($url)
            ->throw();

        return (string) $response->body();
    }

    private function matchFirst(string $pattern, string $subject, int $group = 0): string
    {
        return preg_match($pattern, $subject, $matches) ? (string) ($matches[$group] ?? '') : '';
    }

    private function cleanText(string $html): string
    {
        $text = preg_replace('/<[^>]+>/u', ' ', $html) ?? $html;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function firstLine(array $lines, string $prefix): string
    {
        foreach ($lines as $line) {
            if (str_starts_with($line, $prefix)) {
                return $line;
            }
        }

        return '';
    }

    private function stripPrefix(string $line, string $prefix): string
    {
        return trim(preg_replace('/^'.preg_quote($prefix, '/').'\s*/u', '', $line) ?? $line);
    }

    private function lineInt(array $lines, string $prefix): ?int
    {
        $line = $this->firstLine($lines, $prefix);

        return preg_match('/(\d+)/u', $line, $matches) ? (int) $matches[1] : null;
    }

    private function lineMoney(array $lines, string $prefix): ?float
    {
        $line = $this->firstLine($lines, $prefix);

        return preg_match('/([\d,]+(?:\.\d+)?)/u', $line, $matches) ? (float) str_replace(',', '', $matches[1]) : null;
    }

    private function absoluteUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return self::BASE_URL.'/'.ltrim($path, '/');
    }

    private function schoolReportUrl(): string
    {
        return self::BASE_URL.'/Home/ReportBuildingSchool?AreaID='.AreaSettings::code();
    }

    private function timeout(): int
    {
        return max(10, (int) (config('services.obec_asset.timeout') ?? 45));
    }
}
