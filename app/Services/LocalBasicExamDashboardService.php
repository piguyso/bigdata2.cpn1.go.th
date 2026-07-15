<?php

namespace App\Services;

use App\Support\SchoolLogo;
use App\Support\AreaSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LocalBasicExamDashboardService
{
    private const AREA_OPTION_CODE = '__area__';

    private const CONFIG = [
        'nt' => [
            'title' => 'NT',
            'table' => 'nt_records',
            'school_code' => 'nt_school_code',
            'student_field' => null,
            'subjects' => [
                ['code' => 'math', 'name' => 'คณิตศาสตร์', 'field' => 'math_percent'],
                ['code' => 'thai', 'name' => 'ภาษาไทย', 'field' => 'thai_percent'],
                ['code' => 'total', 'name' => 'รวม 2 ด้าน', 'field' => 'total_percent'],
            ],
        ],
        'rt' => [
            'title' => 'RT',
            'table' => 'rt_records',
            'school_code' => 'rt_school_code',
            'student_field' => 'students_count',
            'subjects' => [
                ['code' => 'reading_aloud', 'name' => 'การอ่านออกเสียง', 'field' => 'reading_aloud_percent'],
                ['code' => 'reading_comprehension', 'name' => 'การอ่านรู้เรื่อง', 'field' => 'reading_comprehension_percent'],
                ['code' => 'total', 'name' => 'รวม 2 ด้าน', 'field' => 'total_percent'],
            ],
        ],
    ];

    public function getDashboardPayload(string $examType, ?int $academicYear = null, ?string $schoolCode = null): array
    {
        $config = $this->config($examType);
        $table = $config['table'];
        $schoolCodeColumn = $config['school_code'];

        $availableYears = DB::table($table)
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $schoolsQuery = DB::table($table.' as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis');
        if ($selectedYear !== null) {
            $schoolsQuery->where('records.academic_year', $selectedYear);
        }

        $schools = $schoolsQuery
            ->select("records.$schoolCodeColumn as school_code", 'records.school_smis', 'records.school_name', 'schools.logo_path')
            ->distinct()
            ->orderBy('records.school_smis')
            ->orderBy('records.school_name')
            ->get();

        $areaOption = (object) [
            'school_code' => self::AREA_OPTION_CODE,
            'school_smis' => null,
            'school_name' => 'ภาพรวมทั้งเขตฯ',
            'logo_path' => null,
        ];

        $selectedSchool = $schoolCode === self::AREA_OPTION_CODE
            ? $areaOption
            : ($schoolCode ? $schools->firstWhere('school_code', $schoolCode) : $areaOption);

        if (! $selectedSchool) {
            $selectedSchool = $areaOption;
        }

        $isAreaOverview = $selectedSchool->school_code === self::AREA_OPTION_CODE;
        $selectedSchoolInfo = null;
        $subjects = [];
        $trendSeries = [];

        if ($selectedYear !== null) {
            $areaRowsForYear = DB::table($table)
                ->where('academic_year', $selectedYear)
                ->get();

            $subjectQuery = DB::table($table)
                ->where('academic_year', $selectedYear);

            $trendQuery = DB::table($table)
                ->orderBy('academic_year');

            if (! $isAreaOverview) {
                $subjectQuery->where($schoolCodeColumn, $selectedSchool->school_code);
                $trendQuery->where($schoolCodeColumn, $selectedSchool->school_code);

                $selectedSchoolInfo = DB::table('system_school')
                    ->where('smis', $selectedSchool->school_smis)
                    ->first();
            }

            $subjects = $this->buildSubjects($subjectQuery->get(), $areaRowsForYear, $config);
            $trendRows = $trendQuery->get()->groupBy('academic_year');
            $trendSeries = collect($config['subjects'])
                ->map(fn ($subject) => [
                    'subjectCode' => $subject['code'],
                    'subjectName' => $subject['name'],
                    'points' => $trendRows->map(function ($yearRows, $year) use ($config, $subject) {
                        return [
                            'year' => (int) $year,
                            'subjectCode' => $subject['code'],
                            'subjectName' => $subject['name'],
                            'schoolAvg' => $this->weightedAverage($yearRows, $subject['field'], $config['student_field']),
                            'studentCount' => $this->studentCount($yearRows, $config['student_field']),
                        ];
                    })->sortBy('year')->values()->all(),
                ])
                ->values()
                ->all();
        }

        $overallAvg = count($subjects) > 0 ? round(collect($subjects)->avg('schoolAvg'), 2) : 0;

        return [
            'area' => ['code' => AreaSettings::code(), 'name' => AreaSettings::name()],
            'examType' => $examType,
            'examTitle' => $config['title'],
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'schools' => array_merge([[
                'schoolCode' => self::AREA_OPTION_CODE,
                'schoolName' => 'ภาพรวมทั้งเขตฯ',
                'schoolSmis' => '',
                'logoUrl' => null,
                'label' => 'ภาพรวมทั้งเขตฯ',
            ]], $schools->map(fn ($school) => [
                'schoolCode' => (string) $school->school_code,
                'schoolName' => (string) $school->school_name,
                'schoolSmis' => (string) ($school->school_smis ?? ''),
                'logoUrl' => SchoolLogo::url($school->logo_path ?? null),
                'label' => trim(((string) ($school->school_smis ?? '')).' '.$school->school_name),
            ])->values()->all()),
            'selectedSchool' => $selectedSchool ? [
                'schoolCode' => (string) $selectedSchool->school_code,
                'schoolName' => (string) $selectedSchool->school_name,
                'smisCode' => (string) ($selectedSchool->school_smis ?? '-'),
                'district' => (string) ($isAreaOverview ? 'ชุมพร เขต 1' : ($selectedSchoolInfo->amper ?? '-')),
                'subdistrict' => (string) ($isAreaOverview ? '-' : ($selectedSchoolInfo->tambon ?? '-')),
                'schoolType' => (string) ($isAreaOverview ? 'ภาพรวมทั้งเขตฯ' : ($selectedSchoolInfo->statusDetail ?? '-')),
                'logoUrl' => SchoolLogo::url($selectedSchoolInfo->logo_path ?? ($selectedSchool->logo_path ?? null)),
                'maxClassLevel' => $config['title'] === 'RT' ? 'ป.1' : 'ป.3',
            ] : null,
            'overview' => [
                [
                    'label' => 'โรงเรียนที่มีข้อมูล',
                    'value' => $schools->count(),
                    'suffix' => 'โรงเรียน',
                    'icon' => 'fa-solid fa-school',
                    'iconBg' => 'bg-orange-50 text-orange-500',
                    'note' => $selectedYear ? 'ปี '.$selectedYear : 'ยังไม่มีข้อมูล',
                ],
                [
                    'label' => 'ด้านที่แสดง',
                    'value' => count($subjects),
                    'suffix' => 'ด้าน',
                    'icon' => 'fa-solid fa-layer-group',
                    'iconBg' => 'bg-sky-50 text-sky-500',
                    'note' => $config['title'],
                ],
                [
                    'label' => 'จำนวนนักเรียนเข้าสอบ',
                    'value' => count($subjects) > 0 ? max(array_column($subjects, 'studentCount')) : 0,
                    'suffix' => $config['student_field'] ? 'คน' : 'รายการ',
                    'icon' => 'fa-solid fa-user-graduate',
                    'iconBg' => 'bg-emerald-50 text-emerald-500',
                    'note' => $selectedSchool?->school_name ?? '-',
                ],
                [
                    'label' => 'คะแนนเฉลี่ย',
                    'value' => $overallAvg,
                    'suffix' => 'คะแนน',
                    'icon' => 'fa-solid fa-chart-line',
                    'iconBg' => 'bg-violet-50 text-violet-500',
                    'note' => 'ข้อมูลจากฐานข้อมูล local',
                ],
            ],
            'subjects' => $subjects,
            'trend' => [
                'years' => $availableYears,
                'series' => $trendSeries,
                'points' => [],
            ],
            'fetchedAt' => now()->toIso8601String(),
        ];
    }

    private function config(string $examType): array
    {
        if (! isset(self::CONFIG[$examType])) {
            throw new InvalidArgumentException('Unknown exam type: '.$examType);
        }

        return self::CONFIG[$examType];
    }

    private function buildSubjects(Collection $rows, Collection $areaRows, array $config): array
    {
        return collect($config['subjects'])
            ->map(function ($subject) use ($rows, $areaRows, $config) {
                $schoolAvg = $this->weightedAverage($rows, $subject['field'], $config['student_field']);
                $areaAvg = $this->weightedAverage($areaRows, $subject['field'], $config['student_field']);

                return [
                    'subjectCode' => $subject['code'],
                    'subjectName' => $subject['name'],
                    'studentCount' => $this->studentCount($rows, $config['student_field']),
                    'schoolAvg' => $schoolAvg,
                    'areaAvg' => $areaAvg,
                    'diffFromArea' => round($schoolAvg - $areaAvg, 2),
                ];
            })
            ->values()
            ->all();
    }

    private function weightedAverage(Collection $rows, string $field, ?string $studentField): float
    {
        $rows = $rows->filter(fn ($row) => $row->{$field} !== null);
        if ($rows->isEmpty()) {
            return 0;
        }

        if (! $studentField) {
            return round((float) $rows->avg($field), 2);
        }

        $weightSum = (int) $rows->sum($studentField);
        if ($weightSum <= 0) {
            return round((float) $rows->avg($field), 2);
        }

        $weighted = $rows->sum(fn ($row) => ((float) $row->{$field}) * ((int) $row->{$studentField}));

        return round($weighted / $weightSum, 2);
    }

    private function studentCount(Collection $rows, ?string $studentField): int
    {
        if (! $studentField) {
            return $rows->count();
        }

        return (int) $rows->sum($studentField);
    }
}
