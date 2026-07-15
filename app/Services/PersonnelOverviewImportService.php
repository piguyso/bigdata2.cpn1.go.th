<?php

namespace App\Services;

use App\Support\AreaSettings;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PersonnelOverviewImportService
{
    private const SCHOOL_REPORTS = ['report03', 'report04', 'report05', 'report09'];

    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function getRemoteContext(?int $academicYear = null, ?string $term = null): array
    {
        return [
            'academic_year' => (string) ($academicYear ?: $this->fetchSettingValue('academicYear')),
            'term' => (string) ($term ?: $this->fetchSettingValue('semester')),
            'area_code' => AreaSettings::code(),
            'area_name' => AreaSettings::name(),
        ];
    }

    public function preview(?int $academicYear = null, ?string $term = null): array
    {
        $context = $this->getRemoteContext($academicYear, $term);
        $sources = $this->fetchAllSources($context);
        $normalized = $this->normalizeSources($context, $sources);

        return [
            ...$context,
            'summary' => $this->buildSummary($this->getReportPayload($sources, 'report02') ?: $this->findAreaRow($this->getReportPayload($sources, 'report01'))),
            'sources' => $this->summarizeSources($sources),
            'normalized' => $this->summarizeNormalized($normalized),
            'warnings' => $normalized['warnings'],
        ];
    }

    public function import(string $mode = 'replace', ?int $createdBy = null, ?int $academicYear = null, ?string $term = null): array
    {
        $context = $this->getRemoteContext($academicYear, $term);
        $sources = $this->fetchAllSources($context);
        $normalized = $this->normalizeSources($context, $sources);
        $summary = $this->buildSummary($this->getReportPayload($sources, 'report02') ?: $this->findAreaRow($this->getReportPayload($sources, 'report01')));

        $batchId = DB::transaction(function () use ($context, $sources, $normalized, $summary, $mode, $createdBy) {
            if ($mode === 'replace') {
                $this->deleteSnapshot((int) $context['academic_year'], (string) $context['term']);
            }

            $batchId = DB::table('personnel_import_batches')->insertGetId([
                'area_code' => AreaSettings::code(),
                'area_name' => AreaSettings::name(),
                'academic_year' => (int) $context['academic_year'],
                'term' => (string) $context['term'],
                'mode' => $mode,
                'sources_count' => count($sources),
                'normalized_records_count' => count($normalized['reports']) + count($normalized['workloads']) + (empty($normalized['area_profile']) ? 0 : 1),
                'matched_schools_count' => $normalized['matched_schools_count'],
                'unmatched_schools_count' => $normalized['unmatched_schools_count'],
                'warnings' => json_encode($normalized['warnings'], JSON_UNESCAPED_UNICODE),
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->storeSources($batchId, $context, $sources);
            $this->storeReportRecords($batchId, $context, $normalized['reports']);
            $this->storeWorkloadSchools($batchId, $context, $normalized['workloads']);
            $this->storeAreaProfile($batchId, $context, $normalized['area_profile']);
            $this->storeLegacyOverview($batchId, $context, $summary, $this->getReportPayload($sources, 'report02') ?: $this->findAreaRow($this->getReportPayload($sources, 'report01')), $mode, $createdBy);

            return $batchId;
        });

        return [
            'batch_id' => $batchId,
            ...$context,
            'summary' => $summary,
            'sources' => $this->summarizeSources($sources),
            'normalized' => $this->summarizeNormalized($normalized),
        ];
    }

    public function deleteSnapshot(int $academicYear, string $term): void
    {
        DB::table('personnel_overview_records')
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('area_code', AreaSettings::code())
            ->delete();

        DB::table('personnel_overview_imports')
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('area_code', AreaSettings::code())
            ->delete();

        DB::table('personnel_import_batches')
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('area_code', AreaSettings::code())
            ->delete();
    }

    private function fetchAllSources(array $context): array
    {
        $opendataBaseUrl = $this->opendataBaseUrl();
        $apiBaseUrl = $this->apiBaseUrl();
        $academicYear = (int) $context['academic_year'];
        $term = (string) $context['term'];
        $apiAcademicYear = $academicYear > 2400 ? $academicYear - 543 : $academicYear;

        $sources = [];

        for ($number = 1; $number <= 10; $number++) {
            $key = sprintf('report%02d', $number);
            $endpoint = $number === 1
                ? $opendataBaseUrl.'/reports/report01/'
                : $opendataBaseUrl.'/reports/'.$key.'/'.AreaSettings::code().'?academicYear='.$apiAcademicYear;

            $payload = $this->fetchJson($endpoint);
            $sources[$key] = $this->makeSource($key, $endpoint, $payload);
        }

        $workloadEndpoint = $opendataBaseUrl.'/reports/workload/area/'.AreaSettings::code()
            .'?academicYear='.$apiAcademicYear
            .'&semester='.rawurlencode($term)
            .'&studentYear=&studentSemester=';
        $sources['workload'] = $this->makeSource('workload', $workloadEndpoint, $this->fetchJson($workloadEndpoint));

        $areaProfileEndpoint = $apiBaseUrl.'/personAreas/'.AreaSettings::code();
        $sources['person_areas'] = $this->makeSource('person_areas', $areaProfileEndpoint, $this->fetchJson($areaProfileEndpoint));

        $semesterEndpoint = $apiBaseUrl.'/semester/';
        $sources['semester'] = $this->makeSource('semester', $semesterEndpoint, $this->fetchJson($semesterEndpoint));

        return $sources;
    }

    private function normalizeSources(array $context, array $sources): array
    {
        $localSchools = $this->localSchoolMap();
        $reports = [];
        $workloads = [];
        $warnings = [];
        $matchedSchools = [];
        $unmatchedSchools = [];

        foreach ($sources as $sourceKey => $source) {
            if (! str_starts_with($sourceKey, 'report')) {
                continue;
            }

            $payload = $source['payload'];
            $level = in_array($sourceKey, self::SCHOOL_REPORTS, true) ? 'school' : 'area';
            $items = $level === 'school' ? (is_array($payload) ? $payload : []) : [$sourceKey === 'report01' ? $this->findAreaRow($payload) : $payload];

            foreach ($items as $item) {
                if (! is_array($item) || $item === []) {
                    continue;
                }

                $schoolSmis = $this->extractSmis($item);
                $school = $schoolSmis !== '' ? ($localSchools[$schoolSmis] ?? null) : null;

                if ($level === 'school' && $schoolSmis !== '') {
                    if ($school) {
                        $matchedSchools[$schoolSmis] = true;
                    } else {
                        $unmatchedSchools[$schoolSmis] = $this->schoolWarningLabel($item, $schoolSmis);
                    }
                }

                $reports[] = [
                    'report_key' => $sourceKey,
                    'level' => $level,
                    'school_id' => $school['id'] ?? null,
                    'school_smis' => $schoolSmis !== '' ? $schoolSmis : null,
                    'school_code' => $this->extractSchoolCode($item),
                    'school_name' => $this->extractSchoolName($item, $school['schoolname'] ?? null),
                    'metrics' => $this->extractMetrics($item),
                    'payload' => $item,
                ];
            }
        }

        $workloadPayload = $this->getReportPayload($sources, 'workload');
        if (is_array($workloadPayload)) {
            foreach ($workloadPayload as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $schoolSmis = $this->extractSmis($item);
                $school = $schoolSmis !== '' ? ($localSchools[$schoolSmis] ?? null) : null;

                if ($schoolSmis !== '') {
                    if ($school) {
                        $matchedSchools[$schoolSmis] = true;
                    } else {
                        $unmatchedSchools[$schoolSmis] = $this->schoolWarningLabel($item, $schoolSmis);
                    }
                }

                $workloads[] = [
                    'school_id' => $school['id'] ?? null,
                    'school_smis' => $schoolSmis !== '' ? $schoolSmis : null,
                    'school_code' => $this->extractSchoolCode($item),
                    'school_name' => $this->extractSchoolName($item, $school['schoolname'] ?? null),
                    'district' => $item['district'] ?? null,
                    'subdistrict' => $item['subdistrict'] ?? null,
                    'province' => $item['province'] ?? null,
                    'school_type' => $item['schoolType'] ?? null,
                    'school_size' => $item['schoolSize'] ?? null,
                    'latitude' => $this->toDecimal($item['latitude'] ?? null),
                    'longitude' => $this->toDecimal($item['longitude'] ?? null),
                    'students_total' => $this->toInt(data_get($item, 'students.sumStudents', $item['sumStudents'] ?? 0)),
                    'rooms_total' => $this->toInt(data_get($item, 'students.sumRoom', $item['sumRoom'] ?? 0)),
                    'personnel_total' => $this->workloadPersonnelTotal($item),
                    'teacher_total' => $this->toInt($item['countTeacherAll'] ?? data_get($item, 'manpowers.countTeacherAll', 0)),
                    'director_total' => $this->toInt($item['countDirectorAll'] ?? data_get($item, 'manpowers.countDirectorAll', 0)),
                    'vice_director_total' => $this->toInt($item['countViceDirectorAll'] ?? data_get($item, 'manpowers.countViceDirectorAll', 0)),
                    'teacher_shortage_total' => $this->toInt($item['teacherW23'] ?? 0),
                    'students' => is_array($item['students'] ?? null) ? $item['students'] : null,
                    'manpowers' => is_array($item['manpowers'] ?? null) ? $item['manpowers'] : null,
                    'payload' => $item,
                ];
            }
        }

        foreach ($unmatchedSchools as $label) {
            $warnings[] = 'ไม่พบโรงเรียนในฐานข้อมูล local: '.$label;
        }

        return [
            'reports' => $reports,
            'workloads' => $workloads,
            'area_profile' => $this->normalizeAreaProfile($sources['person_areas']['payload'] ?? []),
            'matched_schools_count' => count($matchedSchools),
            'unmatched_schools_count' => count($unmatchedSchools),
            'warnings' => array_values($warnings),
        ];
    }

    private function storeSources(int $batchId, array $context, array $sources): void
    {
        foreach ($sources as $source) {
            $json = json_encode($source['payload'], JSON_UNESCAPED_UNICODE);

            DB::table('personnel_import_sources')->insert([
                'batch_id' => $batchId,
                'area_code' => AreaSettings::code(),
                'academic_year' => (int) $context['academic_year'],
                'term' => (string) $context['term'],
                'source_key' => $source['source_key'],
                'endpoint' => $source['endpoint'],
                'records_count' => $source['records_count'],
                'payload_hash' => hash('sha256', $json),
                'payload' => $json,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function storeReportRecords(int $batchId, array $context, array $reports): void
    {
        foreach (array_chunk($reports, 200) as $chunk) {
            DB::table('personnel_report_records')->insert(array_map(fn ($row) => [
                'batch_id' => $batchId,
                'area_code' => AreaSettings::code(),
                'area_name' => AreaSettings::name(),
                'academic_year' => (int) $context['academic_year'],
                'term' => (string) $context['term'],
                'report_key' => $row['report_key'],
                'level' => $row['level'],
                'school_id' => $row['school_id'],
                'school_smis' => $row['school_smis'],
                'school_code' => $row['school_code'],
                'school_name' => $row['school_name'],
                'metrics' => json_encode($row['metrics'], JSON_UNESCAPED_UNICODE),
                'payload' => json_encode($row['payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ], $chunk));
        }
    }

    private function storeWorkloadSchools(int $batchId, array $context, array $workloads): void
    {
        foreach (array_chunk($workloads, 100) as $chunk) {
            DB::table('personnel_workload_schools')->insert(array_map(fn ($row) => [
                'batch_id' => $batchId,
                'area_code' => AreaSettings::code(),
                'area_name' => AreaSettings::name(),
                'academic_year' => (int) $context['academic_year'],
                'term' => (string) $context['term'],
                'school_id' => $row['school_id'],
                'school_smis' => $row['school_smis'],
                'school_code' => $row['school_code'],
                'school_name' => $row['school_name'],
                'district' => $row['district'],
                'subdistrict' => $row['subdistrict'],
                'province' => $row['province'],
                'school_type' => $row['school_type'],
                'school_size' => $row['school_size'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
                'students_total' => $row['students_total'],
                'rooms_total' => $row['rooms_total'],
                'personnel_total' => $row['personnel_total'],
                'teacher_total' => $row['teacher_total'],
                'director_total' => $row['director_total'],
                'vice_director_total' => $row['vice_director_total'],
                'teacher_shortage_total' => $row['teacher_shortage_total'],
                'students' => json_encode($row['students'], JSON_UNESCAPED_UNICODE),
                'manpowers' => json_encode($row['manpowers'], JSON_UNESCAPED_UNICODE),
                'payload' => json_encode($row['payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ], $chunk));
        }
    }

    private function storeAreaProfile(int $batchId, array $context, array $areaProfile): void
    {
        if ($areaProfile === []) {
            return;
        }

        DB::table('personnel_area_profiles')->insert([
            'batch_id' => $batchId,
            'area_code' => AreaSettings::code(),
            'area_name' => AreaSettings::name(),
            'academic_year' => (int) $context['academic_year'],
            'term' => (string) $context['term'],
            'metrics' => json_encode($areaProfile['metrics'], JSON_UNESCAPED_UNICODE),
            'payload' => json_encode($areaProfile['payload'], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function storeLegacyOverview(int $batchId, array $context, array $summary, array $payload, string $mode, ?int $createdBy): void
    {
        $legacyImportId = DB::table('personnel_overview_imports')->insertGetId([
            'area_code' => AreaSettings::code(),
            'area_name' => AreaSettings::name(),
            'academic_year' => (int) $context['academic_year'],
            'term' => (string) $context['term'],
            'mode' => $mode,
            'records_count' => 1,
            'created_by' => $createdBy,
            'warnings' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('personnel_overview_records')->insert([
            'import_id' => $legacyImportId,
            'area_code' => AreaSettings::code(),
            'area_name' => AreaSettings::name(),
            'academic_year' => (int) $context['academic_year'],
            'term' => (string) $context['term'],
            'total_personnel' => $summary['total_personnel'],
            'government_officer_total' => $summary['government_officer_total'],
            'civil_service_staff_total' => $summary['civil_service_staff_total'],
            'government_employee_total' => $summary['government_employee_total'],
            'temporary_employee_total' => $summary['temporary_employee_total'],
            'permanent_employee_total' => $summary['permanent_employee_total'],
            'director_total' => $summary['director_total'],
            'vice_director_total' => $summary['vice_director_total'],
            'teacher_total' => $summary['teacher_total'],
            'payload' => json_encode(['batch_id' => $batchId, ...$payload], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function fetchSettingValue(string $settingKey): string
    {
        $response = $this->http
            ->acceptJson()
            ->timeout($this->timeout())
            ->get($this->apiBaseUrl().'/settings/'.$settingKey)
            ->throw();

        $payload = $response->json();
        $value = trim((string) ($payload['value'] ?? ''));

        if ($value === '') {
            throw new RuntimeException('ไม่พบค่า '.$settingKey.' จาก HRMS');
        }

        return $value;
    }

    private function fetchJson(string $endpoint): mixed
    {
        $response = $this->http
            ->acceptJson()
            ->timeout($this->timeout())
            ->get($endpoint)
            ->throw();

        return $response->json();
    }

    private function makeSource(string $sourceKey, string $endpoint, mixed $payload): array
    {
        return [
            'source_key' => $sourceKey,
            'endpoint' => $endpoint,
            'records_count' => $this->countPayloadRecords($payload),
            'payload' => $payload,
        ];
    }

    private function buildSummary(array $row): array
    {
        $governmentOfficerTotal = $this->firstInt($row, ['countPerson10All', 'countPerson10Sum', 'countPerson10']);
        $civilServiceStaffTotal = $this->firstInt($row, ['countPerson38All', 'countPerson38kAll', 'countPerson38Sum']);
        $governmentEmployeeTotal = $this->firstInt($row, ['countPerson15All', 'countPerson15Sum']);
        $temporaryEmployeeTotal = $this->firstInt($row, ['countPerson17All', 'countPerson17Sum']);
        $permanentEmployeeTotal = $this->firstInt($row, ['countPerson23All', 'countPerson23Sum']);

        return [
            'government_officer_total' => $governmentOfficerTotal,
            'civil_service_staff_total' => $civilServiceStaffTotal,
            'government_employee_total' => $governmentEmployeeTotal,
            'temporary_employee_total' => $temporaryEmployeeTotal,
            'permanent_employee_total' => $permanentEmployeeTotal,
            'director_total' => $this->firstInt($row, ['countDirectorAll', 'countDirectorSchoolAll', 'countDirectorSchoolSum']),
            'vice_director_total' => $this->firstInt($row, ['countViceDirectorAll', 'countViceDirectorSchoolAll', 'countViceDirectorSchoolSum']),
            'teacher_total' => $this->firstInt($row, ['countTeacherAll', 'countTeacherSum']),
            'total_personnel' => $governmentOfficerTotal + $civilServiceStaffTotal + $governmentEmployeeTotal + $temporaryEmployeeTotal + $permanentEmployeeTotal,
        ];
    }

    private function summarizeSources(array $sources): array
    {
        return collect($sources)
            ->map(fn ($source) => [
                'source_key' => $source['source_key'],
                'records_count' => $source['records_count'],
                'endpoint' => $source['endpoint'],
            ])
            ->values()
            ->all();
    }

    private function summarizeNormalized(array $normalized): array
    {
        return [
            'report_records_count' => count($normalized['reports']),
            'workload_schools_count' => count($normalized['workloads']),
            'matched_schools_count' => $normalized['matched_schools_count'],
            'unmatched_schools_count' => $normalized['unmatched_schools_count'],
            'has_area_profile' => $normalized['area_profile'] !== [],
        ];
    }

    private function normalizeAreaProfile(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        return [
            'metrics' => $this->extractMetrics($payload),
            'payload' => $payload,
        ];
    }

    private function getReportPayload(array $sources, string $sourceKey): mixed
    {
        return $sources[$sourceKey]['payload'] ?? [];
    }

    private function findAreaRow(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (($payload['areaCode'] ?? null) || ($payload['area_code'] ?? null)) {
            return $payload;
        }

        foreach ($payload as $item) {
            if (is_array($item) && (string) ($item['areaCode'] ?? '') === AreaSettings::code()) {
                return $item;
            }
        }

        return [];
    }

    private function localSchoolMap(): array
    {
        return DB::table('system_school')
            ->select('id', 'smis', 'schoolname')
            ->get()
            ->mapWithKeys(function ($school) {
                $smis = preg_replace('/\D+/', '', (string) $school->smis);

                return $smis !== ''
                    ? [$smis => ['id' => (int) $school->id, 'schoolname' => (string) $school->schoolname]]
                    : [];
            })
            ->all();
    }

    private function extractMetrics(array $row): array
    {
        return collect($row)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    private function extractSmis(array $row): string
    {
        foreach (['smisCode', 'smis', 'schoolSmis'] as $key) {
            $value = preg_replace('/\D+/', '', (string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractSchoolCode(array $row): ?string
    {
        foreach (['schoolCode', 'schId', 'school_code'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractSchoolName(array $row, ?string $fallback = null): ?string
    {
        foreach (['schName', 'schoolName', 'school_name'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    private function schoolWarningLabel(array $row, string $schoolSmis): string
    {
        return trim(($this->extractSchoolName($row) ?? '-').' ['.$schoolSmis.']');
    }

    private function countPayloadRecords(mixed $payload): int
    {
        if (! is_array($payload)) {
            return 0;
        }

        if (array_is_list($payload)) {
            return count($payload);
        }

        return 1;
    }

    private function firstInt(array $row, array $keys): int
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $this->toInt($row[$key]);
            }
        }

        return 0;
    }

    private function workloadPersonnelTotal(array $item): int
    {
        return $this->toInt($item['countPerson10All'] ?? data_get($item, 'manpowers.countPerson10All', 0))
            + $this->toInt($item['countPerson15All'] ?? data_get($item, 'manpowers.countPerson15All', 0))
            + $this->toInt($item['countPerson17All'] ?? data_get($item, 'manpowers.countPerson17All', 0))
            + $this->toInt($item['countPerson23All'] ?? data_get($item, 'manpowers.countPerson23All', 0))
            + $this->toInt($item['countPerson38All'] ?? data_get($item, 'manpowers.countPerson38All', 0));
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function toDecimal(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function timeout(): int
    {
        return max(5, (int) (config('services.hrms_opendata.timeout') ?? config('services.hrms_onet.timeout') ?? 20));
    }

    private function opendataBaseUrl(): string
    {
        $baseUrl = rtrim((string) (config('services.hrms_opendata.base_url') ?? ''), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ยังไม่ได้ตั้งค่า HRMS opendata base URL');
        }

        return $baseUrl;
    }

    private function apiBaseUrl(): string
    {
        $baseUrl = rtrim((string) (config('services.hrms_onet.base_url') ?? ''), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ยังไม่ได้ตั้งค่า HRMS base URL');
        }

        return $baseUrl;
    }
}
