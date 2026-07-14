<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LocalOnetDashboardService
{
    private const AREA_OPTION_CODE = '__area__';

    private const GRADE_OPTIONS = [
        'P6' => 'ป.6',
        'M3' => 'ม.3',
        'M6' => 'ม.6',
    ];

    public function getDashboardPayload(?string $gradeCode = null, ?int $academicYear = null, ?string $schoolCode = null): array
    {
        $gradeCode = array_key_exists($gradeCode ?? '', self::GRADE_OPTIONS) ? $gradeCode : 'P6';
        $availableYears = DB::table('onet_records')
            ->where('grade_code', $gradeCode)
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $schoolsQuery = DB::table('onet_records')->where('grade_code', $gradeCode);
        if ($selectedYear !== null) {
            $schoolsQuery->where('academic_year', $selectedYear);
        }

        $schools = $schoolsQuery
            ->select('school_code', 'school_smis', 'school_name')
            ->distinct()
            ->orderBy('school_smis')
            ->orderBy('school_name')
            ->get();

        $areaOption = (object) [
            'school_code' => self::AREA_OPTION_CODE,
            'school_smis' => null,
            'school_name' => 'ภาพรวมทั้งเขตฯ',
        ];

        $selectedSchool = $schoolCode === self::AREA_OPTION_CODE
            ? $areaOption
            : ($schoolCode ? $schools->firstWhere('school_code', $schoolCode) : $areaOption);

        $subjects = [];
        $trendSeries = [];
        $selectedSchoolInfo = null;

        if ($selectedSchool !== null && $selectedYear !== null) {
            $isAreaOverview = $selectedSchool->school_code === self::AREA_OPTION_CODE;

            $subjectQuery = DB::table('onet_records')
                ->where('grade_code', $gradeCode)
                ->where('academic_year', $selectedYear)
                ->orderBy('subject_code');

            $trendQuery = DB::table('onet_records')
                ->where('grade_code', $gradeCode)
                ->orderBy('academic_year')
                ->orderBy('subject_code');

            if (! $isAreaOverview) {
                $subjectQuery->where('school_code', $selectedSchool->school_code);
                $trendQuery->where('school_code', $selectedSchool->school_code);

                $selectedSchoolInfo = DB::table('system_school')
                    ->where('smis', $selectedSchool->school_smis)
                    ->first();
            }

            $subjects = $this->aggregateSubjectRows($subjectQuery->get(), $isAreaOverview);

            $trendSeries = $trendQuery->get()
                ->groupBy('subject_code')
                ->map(function ($rows, $subjectCode) use ($isAreaOverview) {
                    $groupedByYear = $rows->groupBy('academic_year');
                    $first = $rows->first();

                    return [
                        'subjectCode' => (string) $subjectCode,
                        'subjectName' => (string) ($first->subject_name ?? $subjectCode),
                        'points' => $groupedByYear->map(function ($yearRows, $year) use ($isAreaOverview, $subjectCode, $first) {
                            $aggregated = $this->aggregateRows($yearRows, $isAreaOverview);

                            return [
                                'year' => (int) $year,
                                'subjectCode' => (string) $subjectCode,
                                'subjectName' => (string) ($first->subject_name ?? $subjectCode),
                                'schoolAvg' => $aggregated['schoolAvg'],
                                'provinceAvg' => $aggregated['provinceAvg'],
                                'regionalAvg' => $aggregated['regionalAvg'],
                                'countryAvg' => $aggregated['countryAvg'],
                                'studentCount' => $aggregated['studentCount'],
                            ];
                        })->sortBy('year')->values()->all(),
                    ];
                })
                ->sortBy('subjectCode')
                ->values()
                ->all();
        }

        return [
            'area' => ['code' => '1086010000', 'name' => 'สพป.ชุมพร เขต 1'],
            'gradeOptions' => collect(self::GRADE_OPTIONS)->map(fn ($label, $code) => ['code' => $code, 'label' => $label])->values()->all(),
            'selectedGrade' => $gradeCode,
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'schools' => array_merge([[
                'schoolCode' => self::AREA_OPTION_CODE,
                'schoolName' => 'ภาพรวมทั้งเขตฯ',
                'schoolSmis' => '',
                'label' => 'ภาพรวมทั้งเขตฯ',
            ]], $schools->map(fn ($school) => [
                'schoolCode' => (string) $school->school_code,
                'schoolName' => (string) $school->school_name,
                'schoolSmis' => (string) ($school->school_smis ?? ''),
                'label' => trim(((string) ($school->school_smis ?? '')).' '.$school->school_name),
            ])->values()->all()),
            'selectedSchool' => $selectedSchool ? [
                'schoolCode' => (string) $selectedSchool->school_code,
                'schoolName' => (string) $selectedSchool->school_name,
                'smisCode' => (string) ($selectedSchool->school_smis ?? '-'),
                'district' => (string) ($selectedSchool->school_code === self::AREA_OPTION_CODE ? 'ชุมพร เขต 1' : ($selectedSchoolInfo->amper ?? '-')),
                'subdistrict' => (string) ($selectedSchool->school_code === self::AREA_OPTION_CODE ? '-' : ($selectedSchoolInfo->tambon ?? '-')),
                'schoolType' => (string) ($selectedSchool->school_code === self::AREA_OPTION_CODE ? 'ภาพรวมทั้งเขตฯ' : ($selectedSchoolInfo->statusDetail ?? '-')),
                'maxClassLevel' => self::GRADE_OPTIONS[$gradeCode] ?? $gradeCode,
            ] : null,
            'overview' => [
                [
                    'label' => 'โรงเรียนที่มีข้อมูลในปีนี้',
                    'value' => $schools->count(),
                    'suffix' => 'โรงเรียน',
                    'icon' => 'fa-solid fa-school',
                    'iconBg' => 'bg-orange-50 text-orange-500',
                    'note' => $selectedYear ? 'ปี '.$selectedYear : 'ยังไม่มีข้อมูล',
                ],
                [
                    'label' => 'จำนวนวิชาที่แสดง',
                    'value' => count($subjects),
                    'suffix' => 'วิชา',
                    'icon' => 'fa-solid fa-layer-group',
                    'iconBg' => 'bg-sky-50 text-sky-500',
                    'note' => 'ระดับ '.(self::GRADE_OPTIONS[$gradeCode] ?? $gradeCode),
                ],
                [
                    'label' => 'จำนวนนักเรียนเข้าสอบ',
                    'value' => count($subjects) > 0 ? max(array_column($subjects, 'studentCount')) : 0,
                    'suffix' => 'คน',
                    'icon' => 'fa-solid fa-user-graduate',
                    'iconBg' => 'bg-emerald-50 text-emerald-500',
                    'note' => $selectedSchool?->school_name ?? '-',
                ],
                [
                    'label' => 'คะแนนเฉลี่ยโรงเรียน',
                    'value' => count($subjects) > 0 ? round(collect($subjects)->avg('schoolAvg'), 2) : 0,
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

    private function aggregateSubjectRows($rows, bool $isAreaOverview): array
    {
        return collect($rows)
            ->groupBy('subject_code')
            ->map(function ($subjectRows, $subjectCode) use ($isAreaOverview) {
                $first = $subjectRows->first();
                $aggregated = $this->aggregateRows($subjectRows, $isAreaOverview);

                return [
                    'subjectCode' => (string) $subjectCode,
                    'subjectName' => (string) ($first->subject_name ?? $subjectCode),
                    'studentCount' => $aggregated['studentCount'],
                    'schoolAvg' => $aggregated['schoolAvg'],
                    'provinceAvg' => $aggregated['provinceAvg'],
                    'regionalAvg' => $aggregated['regionalAvg'],
                    'countryAvg' => $aggregated['countryAvg'],
                    'diffFromProvince' => round($aggregated['schoolAvg'] - $aggregated['provinceAvg'], 2),
                ];
            })
            ->sortBy('subjectCode')
            ->values()
            ->all();
    }

    private function aggregateRows($rows, bool $isAreaOverview): array
    {
        $collection = collect($rows);
        $studentTotal = (int) $collection->sum('student_count');
        $weightSum = max($studentTotal, 1);

        $weightedAverage = function (string $field) use ($collection, $weightSum, $isAreaOverview) {
            if (! $isAreaOverview) {
                return round((float) ($collection->first()->{$field} ?? 0), 2);
            }

            $weighted = $collection->sum(function ($row) use ($field) {
                return ((float) ($row->{$field} ?? 0)) * ((int) ($row->student_count ?? 0));
            });

            return round($weighted / $weightSum, 2);
        };

        return [
            'studentCount' => $studentTotal,
            'schoolAvg' => $weightedAverage('school_avg'),
            'provinceAvg' => $weightedAverage('province_avg'),
            'regionalAvg' => $weightedAverage('regional_avg'),
            'countryAvg' => $weightedAverage('country_avg'),
        ];
    }
}
