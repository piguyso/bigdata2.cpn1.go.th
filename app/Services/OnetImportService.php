<?php

namespace App\Services;

use App\Support\AreaSettings;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OnetImportService
{
    private const GRADE_OPTIONS = [
        'P6' => 'ป.6',
        'M3' => 'ม.3',
        'M6' => 'ม.6',
    ];

    private const LEVEL_RANKS = [
        'อ.1' => 1, 'อ.2' => 2, 'อ.3' => 3,
        'ป.1' => 4, 'ป.2' => 5, 'ป.3' => 6, 'ป.4' => 7, 'ป.5' => 8, 'ป.6' => 9,
        'ม.1' => 10, 'ม.2' => 11, 'ม.3' => 12, 'ม.4' => 13, 'ม.5' => 14, 'ม.6' => 15,
    ];

    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function preview(int $academicYear): array
    {
        $schools = $this->fetchAreaSchools();
        $localSchoolMap = $this->getLocalSchoolMap();

        $grades = collect(self::GRADE_OPTIONS)->map(function ($label, $gradeCode) use ($schools) {
            return [
                'grade_code' => $gradeCode,
                'grade_label' => $label,
                'schools_count' => $this->filterSchoolsByGrade($schools, $gradeCode)->count(),
            ];
        })->values()->all();

        $unmatched = $schools->filter(function (array $school) use ($localSchoolMap) {
            $smis = preg_replace('/\D+/', '', (string) ($school['smisCode'] ?? ''));
            return $smis === '' || ! isset($localSchoolMap[$smis]);
        })->values();

        return [
            'academic_year' => $academicYear,
            'area_code' => AreaSettings::code(),
            'area_name' => AreaSettings::name(),
            'schools_count' => $schools->count(),
            'matched_schools_count' => $schools->count() - $unmatched->count(),
            'unmatched_schools_count' => $unmatched->count(),
            'grades' => $grades,
            'sample_unmatched' => $unmatched->take(10)->map(fn (array $school) => [
                'smis' => (string) ($school['smisCode'] ?? ''),
                'school_name' => (string) ($school['schName'] ?? '-'),
                'district' => (string) ($school['district'] ?? '-'),
                'max_class_level' => (string) ($school['maxClassLevel'] ?? '-'),
            ])->all(),
        ];
    }

    public function import(int $academicYear, string $mode = 'replace', ?int $createdBy = null): array
    {
        $schools = $this->fetchAreaSchools();
        $localSchoolMap = $this->getLocalSchoolMap();
        $warnings = [];
        $rows = [];
        $matchedSchools = [];

        foreach (array_keys(self::GRADE_OPTIONS) as $gradeCode) {
            $gradeSchools = $this->filterSchoolsByGrade($schools, $gradeCode)->values();
            $responses = $this->fetchGradeResponses($academicYear, $gradeCode, $gradeSchools);

            foreach ($gradeSchools as $index => $school) {
                $smis = preg_replace('/\D+/', '', (string) ($school['smisCode'] ?? ''));
                if ($smis === '' || ! isset($localSchoolMap[$smis])) {
                    $warnings[] = 'ไม่พบโรงเรียนในฐานข้อมูล local: '.($school['schName'] ?? '-').' ['.$smis.']';
                    continue;
                }

                $response = $responses[$index] ?? null;
                if ($response === null || ! $response->successful()) {
                    $warnings[] = 'ดึงข้อมูล O-NET ไม่สำเร็จ: '.($school['schName'] ?? '-').' ระดับ '.$gradeCode;
                    continue;
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    $warnings[] = 'รูปแบบข้อมูล O-NET ไม่ถูกต้อง: '.($school['schName'] ?? '-').' ระดับ '.$gradeCode;
                    continue;
                }

                $matchedSchools[$smis] = true;

                foreach ($payload as $record) {
                    if (! is_array($record) || blank($record['SBJ_CODE'] ?? null)) {
                        continue;
                    }

                    $rows[] = [
                        'school_id' => $localSchoolMap[$smis]['id'],
                        'area_code' => AreaSettings::code(),
                        'area_name' => AreaSettings::name(),
                        'academic_year' => (int) ($record['YEAR_COURSE'] ?? $academicYear),
                        'grade_code' => (string) ($record['GRADE_CODE'] ?? $gradeCode),
                        'grade_abbr' => (string) ($record['GRADE_ABBR'] ?? (self::GRADE_OPTIONS[$gradeCode] ?? $gradeCode)),
                        'school_smis' => $smis,
                        'school_code' => (string) ($record['SCHOOL_CODE'] ?? $school['onetSchoolCode'] ?? ''),
                        'school_name' => (string) ($record['SCHOOL_NAME'] ?? $localSchoolMap[$smis]['schoolname']),
                        'subject_code' => (string) ($record['SBJ_CODE'] ?? ''),
                        'subject_name' => (string) ($record['SBJ_NAME'] ?? ''),
                        'student_count' => (int) ($record['STUDENT_CNT'] ?? 0),
                        'school_avg' => round((float) ($record['AVG_SCORE'] ?? 0), 4),
                        'province_avg' => round((float) data_get($record, 'onet_provinces.AVG_SCORE', 0), 4),
                        'regional_avg' => round((float) data_get($record, 'onet_regionals.AVG_SCORE', 0), 4),
                        'country_avg' => round((float) data_get($record, 'onet_countries.AVG_SCORE', 0), 4),
                        'payload' => json_encode($record, JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (count($rows) === 0) {
            throw new RuntimeException('ไม่พบข้อมูล O-NET ที่นำเข้าได้ในปีการศึกษาที่เลือก');
        }

        $importId = DB::transaction(function () use ($academicYear, $mode, $createdBy, $warnings, $rows, $schools, $matchedSchools) {
            if ($mode === 'replace') {
                DB::table('onet_records')
                    ->where('academic_year', $academicYear)
                    ->where('area_code', AreaSettings::code())
                    ->delete();
                DB::table('onet_imports')
                    ->where('academic_year', $academicYear)
                    ->where('area_code', AreaSettings::code())
                    ->delete();
            }

            $importId = DB::table('onet_imports')->insertGetId([
                'area_code' => AreaSettings::code(),
                'area_name' => AreaSettings::name(),
                'academic_year' => $academicYear,
                'mode' => $mode,
                'schools_count' => count($matchedSchools),
                'records_count' => count($rows),
                'subjects_count' => count($rows),
                'skipped_schools_count' => max($schools->count() - count($matchedSchools), 0),
                'warnings' => json_encode(array_values(array_unique($warnings)), JSON_UNESCAPED_UNICODE),
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $payload = collect($rows)->map(function (array $row) use ($importId) {
                $row['import_id'] = $importId;
                return $row;
            })->all();

            DB::table('onet_records')->upsert(
                $payload,
                ['academic_year', 'grade_code', 'school_code', 'subject_code'],
                ['import_id', 'school_id', 'area_code', 'area_name', 'grade_abbr', 'school_smis', 'school_name', 'subject_name', 'student_count', 'school_avg', 'province_avg', 'regional_avg', 'country_avg', 'payload', 'updated_at']
            );

            return $importId;
        });

        return [
            'import_id' => $importId,
            'academic_year' => $academicYear,
            'records_count' => count($rows),
            'schools_count' => count($matchedSchools),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    private function fetchAreaSchools(): Collection
    {
        $config = config('services.hrms_onet');
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $timeout = max(5, (int) ($config['timeout'] ?? 20));

        if ($baseUrl === '') {
            throw new RuntimeException('ยังไม่ได้ตั้งค่า HRMS O-NET base URL');
        }

        $response = $this->http
            ->withoutVerifying()
            ->acceptJson()
            ->timeout($timeout)
            ->get($baseUrl.'/standardcode/schoolArea/'.AreaSettings::code())
            ->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('รูปแบบข้อมูลรายชื่อโรงเรียน O-NET ไม่ถูกต้อง');
        }

        return collect($payload)
            ->filter(fn ($school) => is_array($school) && filled($school['onetSchoolCode'] ?? null))
            ->values();
    }

    private function fetchGradeResponses(int $academicYear, string $gradeCode, Collection $schools): array
    {
        $config = config('services.hrms_onet');
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $timeout = max(5, (int) ($config['timeout'] ?? 20));
        $responses = [];

        foreach ($schools->chunk(12) as $chunk) {
            $batch = $this->http->pool(function ($pool) use ($chunk, $baseUrl, $timeout, $academicYear, $gradeCode) {
                return $chunk->map(function (array $school) use ($pool, $baseUrl, $timeout, $academicYear, $gradeCode) {
                    $code = (string) ($school['onetSchoolCode'] ?? '');

                    return $pool->as($code)
                        ->withoutVerifying()
                        ->acceptJson()
                        ->timeout($timeout)
                        ->get($baseUrl.'/onet/school/'.$academicYear, [
                            'gradeCode' => $gradeCode,
                            'schoolCode' => $code,
                        ]);
                })->all();
            });

            foreach ($chunk as $school) {
                $responses[] = $batch[(string) ($school['onetSchoolCode'] ?? '')] ?? null;
            }
        }

        return $responses;
    }

    private function filterSchoolsByGrade(Collection $schools, string $gradeCode): Collection
    {
        $requiredRank = match ($gradeCode) {
            'M6' => self::LEVEL_RANKS['ม.6'],
            'M3' => self::LEVEL_RANKS['ม.3'],
            default => self::LEVEL_RANKS['ป.6'],
        };

        return $schools->filter(function (array $school) use ($requiredRank) {
            $maxLevel = trim((string) ($school['maxClassLevel'] ?? ''));
            $rank = self::LEVEL_RANKS[$maxLevel] ?? 0;
            return $rank >= $requiredRank;
        })->values();
    }

    private function getLocalSchoolMap(): array
    {
        return DB::table('system_school')
            ->select('id', 'smis', 'schoolname')
            ->get()
            ->mapWithKeys(function ($school) {
                $smis = preg_replace('/\D+/', '', (string) $school->smis);
                return $smis !== '' ? [$smis => ['id' => $school->id, 'schoolname' => $school->schoolname]] : [];
            })
            ->all();
    }
}
