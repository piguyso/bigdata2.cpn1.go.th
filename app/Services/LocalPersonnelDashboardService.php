<?php

namespace App\Services;

use App\Support\SchoolLogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocalPersonnelDashboardService
{
    public function getDashboardPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null): array
    {
        if (Schema::hasTable('personnel_import_batches') && DB::table('personnel_import_batches')->exists()) {
            return $this->getFullDashboardPayload($academicYear, $term, $schoolSmis);
        }

        return $this->getLegacyDashboardPayload($academicYear, $term);
    }

    public function getSchoolDashboardPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null): array
    {
        if (Schema::hasTable('personnel_import_batches') && DB::table('personnel_import_batches')->exists()) {
            return $this->getFullDashboardPayload($academicYear, $term, $schoolSmis, true);
        }

        return $this->getLegacyDashboardPayload($academicYear, $term);
    }

    public function getAreaPersonnelPayload(?int $academicYear = null, ?string $term = null): array
    {
        if (! Schema::hasTable('personnel_report_records')) {
            return $this->emptyAreaPersonnelPayload();
        }

        $snapshots = DB::table('personnel_report_records')
            ->where('report_key', 'report02')
            ->where('level', 'area')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        if ($snapshots->isEmpty()) {
            return $this->emptyAreaPersonnelPayload();
        }

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $record = DB::table('personnel_report_records')
            ->where('report_key', 'report02')
            ->where('level', 'area')
            ->where('academic_year', $selectedYear)
            ->where('term', $selectedTerm)
            ->orderByDesc('id')
            ->first();

        $metrics = $this->decodeJson($record->metrics ?? null);
        $rows = $this->buildAreaPersonnelRows($metrics);
        $total = $this->sumAreaPersonnelRows($rows);
        $extendedReports = $this->buildAreaExtendedReports($selectedYear, $selectedTerm);

        return [
            'selectedArea' => $record->area_name ?? 'สพป.ชุมพร เขต 1',
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'fetchedAt' => $record?->updated_at,
            'rows' => $rows,
            'total' => $total,
            'overview' => [
                ['label' => 'รวมทั้งหมด', 'value' => $total['all'], 'icon' => 'fa-solid fa-users', 'iconBg' => 'bg-orange-50 text-orange-500'],
                ['label' => 'คนครอง', 'value' => $total['position'], 'icon' => 'fa-solid fa-user-check', 'iconBg' => 'bg-emerald-50 text-emerald-500'],
                ['label' => 'ว่างรวม', 'value' => $total['emptyAll'], 'icon' => 'fa-solid fa-user-clock', 'iconBg' => 'bg-amber-50 text-amber-500'],
                ['label' => 'ติดเงื่อนไข', 'value' => $total['condition'], 'icon' => 'fa-solid fa-circle-exclamation', 'iconBg' => 'bg-rose-50 text-rose-500'],
            ],
            'areaReports' => $extendedReports,
        ];
    }

    public function getPositionReportPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null): array
    {
        if (! Schema::hasTable('personnel_report_records')) {
            return $this->emptyPositionReportPayload();
        }

        $snapshots = DB::table('personnel_report_records')
            ->where('report_key', 'report03')
            ->where('level', 'school')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        if ($snapshots->isEmpty()) {
            return $this->emptyPositionReportPayload();
        }

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $availableSchools = $selectedYear !== null && $selectedTerm !== null
            ? $this->buildPositionAvailableSchools($selectedYear, $selectedTerm)
            : [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ];
        $selectedSchoolSmis = preg_replace('/\D+/', '', (string) $schoolSmis);

        if ($selectedSchoolSmis !== '' && ! collect($availableSchools)->contains(fn ($school) => (string) $school['schoolSmis'] === $selectedSchoolSmis)) {
            $selectedSchoolSmis = '';
        }

        $records = DB::table('personnel_report_records as records')
            ->leftJoin('personnel_workload_schools as workload', function ($join) {
                $join->on('records.academic_year', '=', 'workload.academic_year')
                    ->on('records.term', '=', 'workload.term')
                    ->on('records.school_smis', '=', 'workload.school_smis');
            })
            ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
            ->where('records.report_key', 'report03')
            ->where('records.level', 'school')
            ->where('records.academic_year', $selectedYear)
            ->where('records.term', $selectedTerm)
            ->when($selectedSchoolSmis !== '', fn ($query) => $query->where('records.school_smis', $selectedSchoolSmis))
            ->select([
                'records.*',
                'workload.district',
                'workload.subdistrict',
                'workload.school_size',
                'workload.students_total',
                'workload.rooms_total',
                'schools.logo_path',
            ])
            ->orderBy('records.school_smis')
            ->get();

        $areaRecord = DB::table('personnel_report_records')
            ->where('report_key', 'report02')
            ->where('level', 'area')
            ->where('academic_year', $selectedYear)
            ->where('term', $selectedTerm)
            ->orderByDesc('id')
            ->first();

        $rows = $this->buildPositionReportRows($records);
        $total = $this->sumPositionReportRows($rows);
        $areaRows = $this->buildAreaPositionReportRows($areaRecord);
        $areaTotal = $this->sumPositionGroupRows($areaRows, $this->emptyAreaPositionMetrics());
        $combinedPersonAll = ($areaTotal['person']['all'] ?? 0) + ($total['person']['all'] ?? 0);
        $combinedPersonPosition = ($areaTotal['person']['position'] ?? 0) + ($total['person']['position'] ?? 0);
        $combinedPersonEmptyAll = ($areaTotal['person']['emptyAll'] ?? 0) + ($total['person']['emptyAll'] ?? 0);

        return [
            'selectedArea' => $selectedSchoolSmis !== ''
                ? ($records->first()->school_name ?? 'โรงเรียน')
                : ($records->first()->area_name ?? $areaRecord?->area_name ?? 'สพป.ชุมพร เขต 1'),
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'availableSchools' => $availableSchools,
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'selectedSchoolSmis' => $selectedSchoolSmis,
            'selectedScope' => $selectedSchoolSmis !== '' ? 'school' : 'area',
            'fetchedAt' => $records->max('updated_at'),
            'rows' => $rows,
            'total' => $total,
            'areaRows' => $areaRows,
            'areaTotal' => $areaTotal,
            'overview' => [
                ['label' => 'อัตรารวมทั้งหมด', 'value' => $combinedPersonAll, 'icon' => 'fa-solid fa-users', 'iconBg' => 'bg-orange-50 text-orange-500'],
                ['label' => 'สำนักงานเขตพื้นที่', 'value' => $areaTotal['person']['all'] ?? 0, 'icon' => 'fa-solid fa-building-user', 'iconBg' => 'bg-indigo-50 text-indigo-500'],
                ['label' => 'ตำแหน่งในโรงเรียน', 'value' => $total['person']['all'], 'icon' => 'fa-solid fa-school', 'iconBg' => 'bg-sky-50 text-sky-500'],
                ['label' => 'ว่างรวม', 'value' => $combinedPersonEmptyAll, 'icon' => 'fa-solid fa-user-clock', 'iconBg' => 'bg-amber-50 text-amber-500'],
            ],
            'areaPositionGroups' => [
                ['key' => 'areaDirector', 'label' => 'ผอ.เขต'],
                ['key' => 'areaViceDirector', 'label' => 'รอง ผอ.เขต'],
                ['key' => 'eduDirector', 'label' => 'เจ้าหน้าที่บริหารฯ'],
                ['key' => 'eduSupervisor', 'label' => 'ศึกษานิเทศก์'],
                ['key' => 'person38k', 'label' => '38 ค.(2)'],
            ],
            'positionGroups' => [
                ['key' => 'director', 'label' => 'ผอ.ร.ร.'],
                ['key' => 'viceDirector', 'label' => 'รอง ผอ.ร.ร.'],
                ['key' => 'teacher', 'label' => 'ครู'],
            ],
        ];
    }

    public function getGenderReportPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null): array
    {
        if (! Schema::hasTable('personnel_report_records')) {
            return $this->emptyGenderReportPayload();
        }

        $snapshots = DB::table('personnel_report_records')
            ->where('report_key', 'report04')
            ->where('level', 'school')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        if ($snapshots->isEmpty()) {
            return $this->emptyGenderReportPayload();
        }

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $availableSchools = $selectedYear !== null && $selectedTerm !== null
            ? $this->buildGenderAvailableSchools($selectedYear, $selectedTerm)
            : [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ];
        $selectedSchoolSmis = preg_replace('/\D+/', '', (string) $schoolSmis);

        if ($selectedSchoolSmis !== '' && ! collect($availableSchools)->contains(fn ($school) => (string) $school['schoolSmis'] === $selectedSchoolSmis)) {
            $selectedSchoolSmis = '';
        }

        $records = DB::table('personnel_report_records as records')
            ->leftJoin('personnel_workload_schools as workload', function ($join) {
                $join->on('records.academic_year', '=', 'workload.academic_year')
                    ->on('records.term', '=', 'workload.term')
                    ->on('records.school_smis', '=', 'workload.school_smis');
            })
            ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
            ->where('records.report_key', 'report04')
            ->where('records.level', 'school')
            ->where('records.academic_year', $selectedYear)
            ->where('records.term', $selectedTerm)
            ->when($selectedSchoolSmis !== '', fn ($query) => $query->where('records.school_smis', $selectedSchoolSmis))
            ->select([
                'records.*',
                'workload.district',
                'workload.subdistrict',
                'workload.school_size',
                'workload.students_total',
                'workload.rooms_total',
                'schools.logo_path',
            ])
            ->orderBy('records.school_smis')
            ->get();

        $rows = $this->buildGenderReportRows($records);
        $total = $this->sumGenderReportRows($rows);

        return [
            'selectedArea' => $selectedSchoolSmis !== ''
                ? ($records->first()->school_name ?? 'โรงเรียน')
                : ($records->first()->area_name ?? 'สพป.ชุมพร เขต 1'),
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'availableSchools' => $availableSchools,
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'selectedSchoolSmis' => $selectedSchoolSmis,
            'selectedScope' => $selectedSchoolSmis !== '' ? 'school' : 'area',
            'fetchedAt' => $records->max('updated_at'),
            'rows' => $rows,
            'total' => $total,
            'overview' => [
                ['label' => 'โรงเรียนในรายงาน', 'value' => count($rows), 'icon' => 'fa-solid fa-school', 'iconBg' => 'bg-sky-50 text-sky-500'],
                ['label' => 'บุคลากรรวม', 'value' => $total['all'], 'icon' => 'fa-solid fa-users', 'iconBg' => 'bg-orange-50 text-orange-500'],
                ['label' => 'ชาย', 'value' => $total['male']['person'], 'icon' => 'fa-solid fa-person', 'iconBg' => 'bg-indigo-50 text-indigo-500'],
                ['label' => 'หญิง', 'value' => $total['female']['person'], 'icon' => 'fa-solid fa-person-dress', 'iconBg' => 'bg-rose-50 text-rose-500'],
            ],
            'genderGroups' => [
                ['key' => 'male', 'label' => 'ชาย'],
                ['key' => 'female', 'label' => 'หญิง'],
            ],
        ];
    }

    public function getEducationReportPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null): array
    {
        if (! Schema::hasTable('personnel_report_records')) {
            return $this->emptyEducationReportPayload();
        }

        $snapshots = DB::table('personnel_report_records')
            ->where('report_key', 'report05')
            ->where('level', 'school')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        if ($snapshots->isEmpty()) {
            return $this->emptyEducationReportPayload();
        }

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $availableSchools = $selectedYear !== null && $selectedTerm !== null
            ? $this->buildEducationAvailableSchools($selectedYear, $selectedTerm)
            : [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ];
        $selectedSchoolSmis = preg_replace('/\D+/', '', (string) $schoolSmis);

        if ($selectedSchoolSmis !== '' && ! collect($availableSchools)->contains(fn ($school) => (string) $school['schoolSmis'] === $selectedSchoolSmis)) {
            $selectedSchoolSmis = '';
        }

        $records = DB::table('personnel_report_records as records')
            ->leftJoin('personnel_workload_schools as workload', function ($join) {
                $join->on('records.academic_year', '=', 'workload.academic_year')
                    ->on('records.term', '=', 'workload.term')
                    ->on('records.school_smis', '=', 'workload.school_smis');
            })
            ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
            ->where('records.report_key', 'report05')
            ->where('records.level', 'school')
            ->where('records.academic_year', $selectedYear)
            ->where('records.term', $selectedTerm)
            ->when($selectedSchoolSmis !== '', fn ($query) => $query->where('records.school_smis', $selectedSchoolSmis))
            ->select([
                'records.*',
                'workload.district',
                'workload.subdistrict',
                'workload.school_size',
                'workload.students_total',
                'workload.rooms_total',
                'schools.logo_path',
            ])
            ->orderBy('records.school_smis')
            ->get();

        $rows = $this->buildEducationReportRows($records);
        $total = $this->sumEducationReportRows($rows);

        return [
            'selectedArea' => $selectedSchoolSmis !== ''
                ? ($records->first()->school_name ?? 'โรงเรียน')
                : ($records->first()->area_name ?? 'สพป.ชุมพร เขต 1'),
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'availableSchools' => $availableSchools,
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'selectedSchoolSmis' => $selectedSchoolSmis,
            'selectedScope' => $selectedSchoolSmis !== '' ? 'school' : 'area',
            'fetchedAt' => $records->max('updated_at'),
            'rows' => $rows,
            'total' => $total,
            'overview' => [
                ['label' => 'โรงเรียนในรายงาน', 'value' => count($rows), 'icon' => 'fa-solid fa-school', 'iconBg' => 'bg-sky-50 text-sky-500'],
                ['label' => 'บุคลากรรวม', 'value' => $total['person']['total'], 'icon' => 'fa-solid fa-users', 'iconBg' => 'bg-orange-50 text-orange-500'],
                ['label' => 'ปริญญาตรี', 'value' => $total['person']['edu03'], 'icon' => 'fa-solid fa-user-graduate', 'iconBg' => 'bg-emerald-50 text-emerald-500'],
                ['label' => 'ปริญญาตรีขึ้นไป', 'value' => $total['person']['high'], 'icon' => 'fa-solid fa-graduation-cap', 'iconBg' => 'bg-violet-50 text-violet-500'],
            ],
            'roleGroups' => [
                ['key' => 'person', 'label' => 'บุคลากรรวม'],
                ['key' => 'director', 'label' => 'ผอ.'],
                ['key' => 'viceDirector', 'label' => 'รองฯ'],
                ['key' => 'teacher', 'label' => 'ครู'],
            ],
        ];
    }

    public function getAcademicStandingReportPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null): array
    {
        if (! Schema::hasTable('personnel_report_records')) {
            return $this->emptyAcademicStandingReportPayload();
        }

        $snapshots = DB::table('personnel_report_records')
            ->where('report_key', 'report10')
            ->where('level', 'area')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        if ($snapshots->isEmpty()) {
            return $this->emptyAcademicStandingReportPayload();
        }

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $availableSchools = $selectedYear !== null && $selectedTerm !== null
            ? $this->buildAcademicStandingAvailableSchools($selectedYear, $selectedTerm)
            : [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมโรงเรียนทั้งหมด', 'label' => 'ภาพรวมโรงเรียนทั้งหมด'],
            ];
        $selectedSchoolSmis = preg_replace('/\D+/', '', (string) $schoolSmis);

        if ($selectedSchoolSmis !== '' && ! collect($availableSchools)->contains(fn ($school) => (string) $school['schoolSmis'] === $selectedSchoolSmis)) {
            $selectedSchoolSmis = '';
        }

        $record = DB::table('personnel_report_records')
            ->where('report_key', 'report10')
            ->where('level', 'area')
            ->where('academic_year', $selectedYear)
            ->where('term', $selectedTerm)
            ->orderByDesc('id')
            ->first();

        $metrics = $this->decodeJson($record->metrics ?? null);
        $total = $this->academicStandingMetrics($metrics, 'countPerson');
        $schoolRecords = DB::table('personnel_report_records as records')
            ->leftJoin('personnel_workload_schools as workload', function ($join) {
                $join->on('records.academic_year', '=', 'workload.academic_year')
                    ->on('records.term', '=', 'workload.term')
                    ->on('records.school_code', '=', 'workload.school_code');
            })
            ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
            ->where('records.report_key', 'report09')
            ->where('records.level', 'school')
            ->where('records.academic_year', $selectedYear)
            ->where('records.term', $selectedTerm)
            ->when($selectedSchoolSmis !== '', function ($query) use ($selectedSchoolSmis) {
                $query->where(function ($nested) use ($selectedSchoolSmis) {
                    $nested->where('workload.school_smis', $selectedSchoolSmis)
                        ->orWhere('records.school_code', $selectedSchoolSmis);
                });
            })
            ->select([
                'records.*',
                'workload.school_smis as workload_school_smis',
                'workload.district',
                'workload.subdistrict',
                'workload.school_size',
                'schools.logo_path',
            ])
            ->orderByRaw('COALESCE(workload.school_smis, records.school_code)')
            ->get();
        $schoolRows = $this->buildAcademicStandingSchoolRows($schoolRecords);
        $schoolTotal = $this->sumAcademicStandingSchoolRows($schoolRows);
        $isSchoolScope = $selectedSchoolSmis !== '';
        $summaryTotal = $isSchoolScope ? $schoolTotal['person'] : $total;
        $summaryLevels = $isSchoolScope ? $this->academicStandingSchoolLevels() : $this->academicStandingLevels();
        $summaryOverview = $isSchoolScope
            ? [
                ['label' => 'รวมโรงเรียน', 'value' => $summaryTotal['total'], 'icon' => 'fa-solid fa-school', 'iconBg' => 'bg-orange-50 text-orange-500'],
                ['label' => 'ไม่มีวิทยฐานะ', 'value' => $summaryTotal['level00'], 'icon' => 'fa-solid fa-user', 'iconBg' => 'bg-slate-50 text-slate-500'],
                ['label' => 'ชำนาญการ', 'value' => $summaryTotal['level12'], 'icon' => 'fa-solid fa-user-check', 'iconBg' => 'bg-sky-50 text-sky-500'],
                ['label' => 'ชำนาญการพิเศษขึ้นไป', 'value' => $summaryTotal['level13'] + $summaryTotal['level14'] + $summaryTotal['level15'], 'icon' => 'fa-solid fa-award', 'iconBg' => 'bg-emerald-50 text-emerald-500'],
            ]
            : [
                ['label' => 'รวมทั้งหมด', 'value' => $total['total'], 'icon' => 'fa-solid fa-users', 'iconBg' => 'bg-orange-50 text-orange-500'],
                ['label' => 'ไม่มีวิทยฐานะ', 'value' => $total['level01'], 'icon' => 'fa-solid fa-user', 'iconBg' => 'bg-slate-50 text-slate-500'],
                ['label' => 'ชำนาญการ', 'value' => $total['level02'], 'icon' => 'fa-solid fa-user-check', 'iconBg' => 'bg-sky-50 text-sky-500'],
                ['label' => 'ชำนาญการพิเศษขึ้นไป', 'value' => $total['level03'] + $total['level04'] + $total['level05'], 'icon' => 'fa-solid fa-award', 'iconBg' => 'bg-emerald-50 text-emerald-500'],
            ];
        $selectedSchoolLabel = $isSchoolScope
            ? (collect($availableSchools)->firstWhere('schoolSmis', $selectedSchoolSmis)['schoolName'] ?? 'โรงเรียน')
            : null;

        return [
            'selectedArea' => $selectedSchoolLabel ?: ($record->area_name ?? 'สพป.ชุมพร เขต 1'),
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'availableSchools' => $availableSchools,
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'selectedSchoolSmis' => $selectedSchoolSmis,
            'selectedScope' => $isSchoolScope ? 'school' : 'area',
            'fetchedAt' => $record?->updated_at,
            'levels' => $this->academicStandingLevels(),
            'rows' => $this->buildAcademicStandingRows($metrics),
            'total' => $total,
            'summaryLevels' => $summaryLevels,
            'summaryTotal' => $summaryTotal,
            'schoolLevels' => $this->academicStandingSchoolLevels(),
            'schoolRows' => $schoolRows,
            'schoolTotal' => $schoolTotal,
            'overview' => $summaryOverview,
        ];
    }

    private function getFullDashboardPayload(?int $academicYear = null, ?string $term = null, ?string $schoolSmis = null, bool $schoolOnly = false): array
    {
        $snapshots = DB::table('personnel_import_batches')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $batch = null;
        $areaReport = null;
        $areaProfile = null;
        $workloadSummary = collect();
        $selectedSchoolSmis = preg_replace('/\D+/', '', (string) $schoolSmis);
        $availableSchools = [];
        $selectedSchool = null;
        $workloadRows = collect();

        if ($selectedYear !== null && $selectedTerm !== null) {
            $batch = DB::table('personnel_import_batches')
                ->where('academic_year', $selectedYear)
                ->where('term', $selectedTerm)
                ->orderByDesc('id')
                ->first();

            $areaReport = DB::table('personnel_report_records')
                ->where('academic_year', $selectedYear)
                ->where('term', $selectedTerm)
                ->where('report_key', 'report02')
                ->where('level', 'area')
                ->orderByDesc('id')
                ->first();

            if (! $areaReport) {
                $areaReport = DB::table('personnel_report_records')
                    ->where('academic_year', $selectedYear)
                    ->where('term', $selectedTerm)
                    ->where('report_key', 'report01')
                    ->where('level', 'area')
                    ->orderByDesc('id')
                    ->first();
            }

            $areaProfile = DB::table('personnel_area_profiles')
                ->where('academic_year', $selectedYear)
                ->where('term', $selectedTerm)
                ->orderByDesc('id')
                ->first();

            $workloadSummary = DB::table('personnel_workload_schools')
                ->where('academic_year', $selectedYear)
                ->where('term', $selectedTerm)
                ->whereNotNull('school_id')
                ->selectRaw('COUNT(*) as schools_count')
                ->selectRaw('SUM(students_total) as students_total')
                ->selectRaw('SUM(rooms_total) as rooms_total')
                ->selectRaw('SUM(teacher_total) as teacher_total')
                ->selectRaw('SUM(director_total) as director_total')
                ->selectRaw('SUM(vice_director_total) as vice_director_total')
                ->selectRaw('SUM(personnel_total) as personnel_total')
                ->selectRaw('SUM(teacher_shortage_total) as teacher_shortage_total')
                ->first();

            $availableSchools = $this->buildAvailableSchools(
                $selectedYear,
                $selectedTerm,
                $schoolOnly ? 'ภาพรวมโรงเรียนทั้งหมด' : 'ภาพรวมทั้งเขต'
            );

            if ($selectedSchoolSmis !== '') {
                $selectedSchool = DB::table('personnel_workload_schools as workload')
                    ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
                    ->where('workload.academic_year', $selectedYear)
                    ->where('workload.term', $selectedTerm)
                    ->where('workload.school_smis', $selectedSchoolSmis)
                    ->whereNotNull('workload.school_id')
                    ->select('workload.*', 'schools.logo_path')
                    ->orderBy('workload.school_smis')
                    ->first();

                if (! $selectedSchool) {
                    $selectedSchoolSmis = '';
                }
            }

            $workloadRows = DB::table('personnel_workload_schools as workload')
                ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
                ->where('workload.academic_year', $selectedYear)
                ->where('workload.term', $selectedTerm)
                ->whereNotNull('workload.school_id')
                ->when($selectedSchoolSmis !== '', fn ($query) => $query->where('workload.school_smis', $selectedSchoolSmis))
                ->select('workload.*', 'schools.logo_path')
                ->orderBy('workload.school_smis')
                ->get();
        }

        $isSchoolScope = $selectedSchool !== null;
        $payload = $isSchoolScope
            ? $this->decodeJson($selectedSchool->payload ?? null)
            : ($schoolOnly ? [] : $this->decodeJson($areaReport->payload ?? null));
        $metrics = $isSchoolScope
            ? $this->extractNumericMetrics($payload)
            : ($schoolOnly ? $this->aggregateSchoolMetrics($workloadRows) : $this->decodeJson($areaReport->metrics ?? null));
        $profileMetrics = $this->decodeJson($areaProfile->metrics ?? null);
        $currentSummary = $isSchoolScope ? $selectedSchool : $workloadSummary;

        return [
            'selectedArea' => $isSchoolScope
                ? ($selectedSchool->school_name ?? 'โรงเรียน')
                : ($schoolOnly ? 'ภาพรวมบุคลากรในโรงเรียน' : ($batch->area_name ?? 'สพป.ชุมพร เขต 1')),
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'availableSchools' => $availableSchools,
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'selectedSchoolSmis' => $selectedSchoolSmis,
            'selectedScope' => $isSchoolScope ? 'school' : ($schoolOnly ? 'schools' : 'area'),
            'fetchedAt' => $batch?->updated_at,
            'overview' => $isSchoolScope
                ? $this->buildSchoolOverview($metrics, $selectedSchool)
                : ($schoolOnly
                    ? $this->buildSchoolsOverview($metrics, $workloadSummary, $batch)
                    : $this->buildFullOverview($metrics, $workloadSummary, $batch)),
            'employmentSummary' => $this->buildFullEmploymentSummary($metrics),
            'positionSummary' => $this->buildFullPositionSummary($metrics, $currentSummary),
            'personnelStatusSummary' => $this->buildStatusSummary($payload, 'countPerson10', 'ข้าราชการ'),
            'teacherStatusSummary' => $this->buildStatusSummary($payload, 'countTeacher', 'ครู'),
            'rawCodeSummary' => [
                'personnel' => $this->buildRawCodeSummary($payload, 'countPerson'),
                'position' => $this->buildRawCodeSummary($payload, 'count'),
            ],
            'workloadSummary' => [
                'schoolsCount' => $isSchoolScope ? 1 : (int) ($workloadSummary->schools_count ?? 0),
                'studentsTotal' => (int) ($currentSummary->students_total ?? 0),
                'roomsTotal' => (int) ($currentSummary->rooms_total ?? 0),
                'personnelTotal' => (int) ($currentSummary->personnel_total ?? 0),
                'teacherShortageTotal' => (int) ($currentSummary->teacher_shortage_total ?? 0),
                'schoolName' => $selectedSchool->school_name ?? null,
                'schoolSmis' => $selectedSchool->school_smis ?? null,
                'logoUrl' => SchoolLogo::url($selectedSchool->logo_path ?? null),
                'district' => $selectedSchool->district ?? null,
                'subdistrict' => $selectedSchool->subdistrict ?? null,
                'schoolSize' => $selectedSchool->school_size ?? null,
            ],
            'areaProfile' => [
                'director' => (int) ($profileMetrics['director'] ?? 0),
                'deputyDirector' => (int) ($profileMetrics['deputyDirector'] ?? 0),
                'supervisor' => (int) ($profileMetrics['supervisor'] ?? 0),
                'person38k' => (int) ($profileMetrics['person38k'] ?? 0),
                'sumPerson' => (int) ($profileMetrics['sumPerson'] ?? 0),
            ],
            'workloadTable' => [
                'rows' => $this->buildWorkloadRows($workloadRows),
                'studentLevels' => $this->buildStudentLevelSummary($workloadRows),
            ],
        ];
    }

    private function emptyAreaPersonnelPayload(): array
    {
        return [
            'selectedArea' => 'สพป.ชุมพร เขต 1',
            'availableYears' => [],
            'availableTerms' => [],
            'availableSchools' => [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ],
            'selectedYear' => null,
            'selectedTerm' => null,
            'selectedSchoolSmis' => '',
            'selectedScope' => 'area',
            'fetchedAt' => null,
            'rows' => [],
            'total' => [
                'all' => 0,
                'position' => 0,
                'empty' => 0,
                's04' => 0,
                'condition' => 0,
                'noMoney' => 0,
                'emptyAll' => 0,
            ],
            'overview' => [],
            'areaReports' => $this->emptyAreaExtendedReports(),
        ];
    }

    private function emptyAreaExtendedReports(): array
    {
        return [
            'gender' => ['rows' => [], 'total' => 0],
            'education' => ['rows' => [], 'total' => 0],
            'educationGender' => [
                'rows' => [],
                'genderColumns' => [],
                'message' => 'ยังไม่มีข้อมูลแยกวุฒิการศึกษาและเพศเฉพาะบุคลากรในเขตพื้นที่ใน localdb',
            ],
            'educationPosition' => ['rows' => [], 'educationColumns' => []],
        ];
    }

    private function buildAreaExtendedReports(?int $academicYear, ?string $term): array
    {
        if ($academicYear === null || $term === null) {
            return $this->emptyAreaExtendedReports();
        }

        $report08 = DB::table('personnel_report_records')
            ->where('report_key', 'report08')
            ->where('level', 'area')
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->orderByDesc('id')
            ->first();

        $report07 = DB::table('personnel_report_records')
            ->where('report_key', 'report07')
            ->where('level', 'area')
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->orderByDesc('id')
            ->first();

        $metrics08 = $this->decodeJson($report08->metrics ?? null);
        $metrics07 = $this->decodeJson($report07->metrics ?? null);
        $educationPosition = $this->buildAreaEducationPositionReport($metrics07);

        return [
            'gender' => $this->buildAreaOfficeGenderReport($metrics08),
            'education' => $this->buildAreaEducationSummaryFromPositionRows($educationPosition['rows']),
            'educationGender' => [
                'rows' => [],
                'genderColumns' => [],
                'message' => 'ยังไม่มีข้อมูลแยกวุฒิการศึกษาและเพศเฉพาะบุคลากรในเขตพื้นที่ใน localdb',
            ],
            'educationPosition' => $educationPosition,
        ];
    }

    private function buildAreaOfficeGenderReport(array $metrics): array
    {
        $male = $this->sumMetrics($metrics, [
            'countDirectorAreaMaleSum',
            'countViceDirectorAreaMaleSum',
            'countEduDirectorMaleSum',
            'countEduSupervisorMaleSum',
            'countPerson38kGeneralMaleSum',
            'countPerson38kAcademicMaleSum',
        ]);
        $female = $this->sumMetrics($metrics, [
            'countDirectorAreaFemaleSum',
            'countViceDirectorAreaFemaleSum',
            'countEduDirectorFemaleSum',
            'countEduSupervisorFemaleSum',
            'countPerson38kGeneralFemaleSum',
            'countPerson38kAcademicFemaleSum',
        ]);
        $total = $male + $female;

        return [
            'total' => $total,
            'rows' => [
                ['label' => 'ชาย', 'value' => $male, 'percent' => $this->percent($male, $total), 'className' => 'bg-indigo-500'],
                ['label' => 'หญิง', 'value' => $female, 'percent' => $this->percent($female, $total), 'className' => 'bg-rose-500'],
            ],
        ];
    }

    private function buildAreaGenderReport(array $metrics): array
    {
        $total = $this->firstMetric($metrics, ['countPerson10']);
        $male = $this->firstMetric($metrics, ['countPerson10Male']);
        $female = $this->firstMetric($metrics, ['countPerson10Female']);

        if ($total > 0 && ($male + $female) !== $total) {
            $female = max($total - $male, 0);
        }

        return [
            'total' => $total,
            'rows' => [
                ['label' => 'ชาย', 'value' => $male, 'percent' => $this->percent($male, $total), 'className' => 'bg-indigo-500'],
                ['label' => 'หญิง', 'value' => $female, 'percent' => $this->percent($female, $total), 'className' => 'bg-rose-500'],
            ],
        ];
    }

    private function buildAreaEducationReport(array $metrics, string $prefix): array
    {
        $total = $this->firstMetric($metrics, [$prefix]);
        $rows = collect($this->educationColumns())
            ->map(fn ($column) => [
                'key' => $column['key'],
                'label' => $column['label'],
                'value' => $this->educationMetricValue($metrics, $prefix, $column['key']),
            ])
            ->filter(fn ($row) => $row['value'] > 0 || in_array($row['key'], ['low', 'edu03', 'edu04', 'edu05'], true))
            ->values()
            ->all();

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }

    private function buildAreaEducationGenderReport(array $metrics): array
    {
        $maleTotal = $this->firstMetric($metrics, ['countPerson10Male']);
        $femaleTotal = $this->firstMetric($metrics, ['countPerson10Female']);
        $overallTotal = $this->firstMetric($metrics, ['countPerson10']);

        if ($overallTotal > 0 && ($maleTotal + $femaleTotal) !== $overallTotal) {
            $femaleTotal = max($overallTotal - $maleTotal, 0);
        }

        $genderColumns = [
            ['key' => 'male', 'label' => 'ชาย', 'total' => $maleTotal],
            ['key' => 'female', 'label' => 'หญิง', 'total' => $femaleTotal],
        ];

        $rows = collect($this->educationColumns())
            ->map(fn ($column) => [
                'key' => $column['key'],
                'label' => $column['label'],
                'male' => $this->educationMetricValue($metrics, 'countPerson10Male', $column['key']),
                'female' => $this->educationMetricValue($metrics, 'countPerson10Female', $column['key']),
            ])
            ->filter(fn ($row) => ($row['male'] + $row['female']) > 0 || in_array($row['key'], ['low', 'edu03', 'edu04', 'edu05'], true))
            ->values()
            ->all();

        return [
            'genderColumns' => $genderColumns,
            'rows' => $rows,
        ];
    }

    private function buildAreaEducationSummaryFromPositionRows(array $positionRows): array
    {
        $rows = collect($this->educationColumns())
            ->map(function ($column) use ($positionRows) {
                return [
                    'key' => $column['key'],
                    'label' => $column['label'],
                    'value' => collect($positionRows)->sum(fn ($row) => $row[$column['key']] ?? 0),
                ];
            })
            ->filter(fn ($row) => $row['value'] > 0 || in_array($row['key'], ['low', 'edu03', 'edu04', 'edu05'], true))
            ->values()
            ->all();

        return [
            'total' => collect($positionRows)->sum('total'),
            'rows' => $rows,
        ];
    }

    private function buildAreaEducationPositionReport(array $metrics): array
    {
        $positions = [
            ['key' => 'directorArea', 'label' => 'ผอ.สพท.', 'prefix' => 'countDirectorArea'],
            ['key' => 'viceDirectorArea', 'label' => 'รอง ผอ.สพท.', 'prefix' => 'countViceDirectorArea'],
            ['key' => 'eduDirector', 'label' => 'เจ้าหน้าที่บริหารการศึกษาขั้นพื้นฐาน', 'prefix' => 'countEduDirector'],
            ['key' => 'eduSupervisor', 'label' => 'ศึกษานิเทศก์', 'prefix' => 'countEduSupervisor'],
            ['key' => 'person38k', 'label' => 'บุคลากรอื่นตามมาตรา 38 ค.(2)', 'prefix' => 'countPerson38k'],
        ];

        return [
            'educationColumns' => $this->educationColumns(),
            'rows' => collect($positions)
                ->map(function ($position) use ($metrics) {
                    $row = [
                        'key' => $position['key'],
                        'label' => $position['label'],
                        'total' => $this->firstMetric($metrics, [$position['prefix']]),
                    ];

                    foreach ($this->educationColumns() as $column) {
                        $row[$column['key']] = $this->educationMetricValue($metrics, $position['prefix'], $column['key']);
                    }

                    return $row;
                })
                ->filter(fn ($row) => $row['total'] > 0 || collect($this->educationColumns())->sum(fn ($column) => $row[$column['key']] ?? 0) > 0)
                ->values()
                ->all(),
        ];
    }

    private function educationColumns(): array
    {
        return [
            ['key' => 'low', 'label' => 'ต่ำกว่า ป.ตรี'],
            ['key' => 'edu01', 'label' => 'วุฒิ 01'],
            ['key' => 'edu02', 'label' => 'วุฒิ 02'],
            ['key' => 'edu03', 'label' => 'ป.ตรี'],
            ['key' => 'edu035', 'label' => 'ป.บัณฑิต'],
            ['key' => 'edu04', 'label' => 'ป.โท'],
            ['key' => 'edu05', 'label' => 'ป.เอก'],
        ];
    }

    private function educationMetricValue(array $metrics, string $prefix, string $key): int
    {
        $suffix = match ($key) {
            'low' => 'EduLow',
            'edu01' => 'Edu01',
            'edu02' => 'Edu02',
            'edu03' => 'Edu03',
            'edu035' => 'Edu035',
            'edu04' => 'Edu04',
            'edu05' => 'Edu05',
            default => '',
        };

        return $suffix !== '' ? $this->firstMetric($metrics, [$prefix.$suffix]) : 0;
    }

    private function percent(int $value, int $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(($value / $total) * 100, 1);
    }

    private function emptyPositionReportPayload(): array
    {
        return [
            'selectedArea' => 'สพป.ชุมพร เขต 1',
            'availableYears' => [],
            'availableTerms' => [],
            'selectedYear' => null,
            'selectedTerm' => null,
            'fetchedAt' => null,
            'rows' => [],
            'total' => $this->emptyPositionMetrics(),
            'areaRows' => [],
            'areaTotal' => $this->emptyAreaPositionMetrics(),
            'overview' => [],
            'areaPositionGroups' => [],
            'positionGroups' => [],
        ];
    }

    private function emptyGenderReportPayload(): array
    {
        return [
            'selectedArea' => 'สพป.ชุมพร เขต 1',
            'availableYears' => [],
            'availableTerms' => [],
            'availableSchools' => [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ],
            'selectedYear' => null,
            'selectedTerm' => null,
            'selectedSchoolSmis' => '',
            'selectedScope' => 'area',
            'fetchedAt' => null,
            'rows' => [],
            'total' => $this->emptyGenderMetrics(),
            'overview' => [],
            'genderGroups' => [],
        ];
    }

    private function emptyEducationReportPayload(): array
    {
        return [
            'selectedArea' => 'สพป.ชุมพร เขต 1',
            'availableYears' => [],
            'availableTerms' => [],
            'availableSchools' => [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ],
            'selectedYear' => null,
            'selectedTerm' => null,
            'selectedSchoolSmis' => '',
            'selectedScope' => 'area',
            'fetchedAt' => null,
            'rows' => [],
            'total' => $this->emptyEducationMetrics(),
            'overview' => [],
            'roleGroups' => [],
        ];
    }

    private function emptyAcademicStandingReportPayload(): array
    {
        return [
            'selectedArea' => 'สพป.ชุมพร เขต 1',
            'availableYears' => [],
            'availableTerms' => [],
            'selectedYear' => null,
            'selectedTerm' => null,
            'fetchedAt' => null,
            'availableSchools' => [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมโรงเรียนทั้งหมด', 'label' => 'ภาพรวมโรงเรียนทั้งหมด'],
            ],
            'selectedSchoolSmis' => '',
            'selectedScope' => 'area',
            'levels' => $this->academicStandingLevels(),
            'rows' => [],
            'total' => $this->emptyAcademicStandingMetrics(),
            'summaryLevels' => $this->academicStandingLevels(),
            'summaryTotal' => $this->emptyAcademicStandingMetrics(),
            'schoolLevels' => $this->academicStandingSchoolLevels(),
            'schoolRows' => [],
            'schoolTotal' => $this->emptyAcademicStandingSchoolReportMetrics(),
            'overview' => [],
        ];
    }

    private function buildPositionReportRows($records): array
    {
        return collect($records)
            ->map(function ($record, $index) {
                $metrics = $this->decodeJson($record->metrics ?? null);

                return [
                    'index' => $index + 1,
                    'schoolSmis' => (string) ($record->school_smis ?? ''),
                    'schoolCode' => (string) ($record->school_code ?? ''),
                    'schoolName' => (string) ($record->school_name ?? ''),
                    'logoUrl' => SchoolLogo::url($record->logo_path ?? null),
                    'district' => (string) ($record->district ?? ''),
                    'subdistrict' => (string) ($record->subdistrict ?? ''),
                    'schoolSize' => (string) ($record->school_size ?? ''),
                    'studentsTotal' => (int) ($record->students_total ?? 0),
                    'roomsTotal' => (int) ($record->rooms_total ?? 0),
                    'person' => $this->positionMetrics($metrics, 'countPerson'),
                    'director' => $this->positionMetrics($metrics, 'countDirectorSchool'),
                    'viceDirector' => $this->positionMetrics($metrics, 'countViceDirectorSchool'),
                    'teacher' => $this->positionMetrics($metrics, 'countTeacher'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildAreaPositionReportRows(?object $record): array
    {
        if (! $record) {
            return [];
        }

        $metrics = $this->decodeJson($record->metrics ?? null);
        $row = [
            'index' => 1,
            'label' => $record->area_name ?? 'สำนักงานเขตพื้นที่',
            'person' => $this->sumPositionGroups([
                $this->positionMetrics($metrics, 'countDirectorArea'),
                $this->positionMetrics($metrics, 'countViceDirectorArea'),
                $this->positionMetrics($metrics, 'countEduDirector'),
                $this->positionMetrics($metrics, 'countEduSupervisor'),
                $this->positionMetrics($metrics, 'countPerson38k'),
            ]),
            'areaDirector' => $this->positionMetrics($metrics, 'countDirectorArea'),
            'areaViceDirector' => $this->positionMetrics($metrics, 'countViceDirectorArea'),
            'eduDirector' => $this->positionMetrics($metrics, 'countEduDirector'),
            'eduSupervisor' => $this->positionMetrics($metrics, 'countEduSupervisor'),
            'person38k' => $this->positionMetrics($metrics, 'countPerson38k'),
        ];

        return [$row];
    }

    private function buildGenderReportRows($records): array
    {
        return collect($records)
            ->map(function ($record, $index) {
                $metrics = $this->decodeJson($record->metrics ?? null);

                return [
                    'index' => $index + 1,
                    'schoolSmis' => (string) ($record->school_smis ?? ''),
                    'schoolCode' => (string) ($record->school_code ?? ''),
                    'schoolName' => (string) ($record->school_name ?? ''),
                    'logoUrl' => SchoolLogo::url($record->logo_path ?? null),
                    'district' => (string) ($record->district ?? ''),
                    'subdistrict' => (string) ($record->subdistrict ?? ''),
                    'schoolSize' => (string) ($record->school_size ?? ''),
                    'studentsTotal' => (int) ($record->students_total ?? 0),
                    'roomsTotal' => (int) ($record->rooms_total ?? 0),
                    'all' => $this->firstMetric($metrics, ['countAllPerson']),
                    'male' => $this->genderMetrics($metrics, 'Male'),
                    'female' => $this->genderMetrics($metrics, 'Female'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildEducationReportRows($records): array
    {
        return collect($records)
            ->map(function ($record, $index) {
                $metrics = $this->decodeJson($record->metrics ?? null);

                return [
                    'index' => $index + 1,
                    'schoolSmis' => (string) ($record->school_smis ?? ''),
                    'schoolCode' => (string) ($record->school_code ?? ''),
                    'schoolName' => (string) ($record->school_name ?? ''),
                    'logoUrl' => SchoolLogo::url($record->logo_path ?? null),
                    'district' => (string) ($record->district ?? ''),
                    'subdistrict' => (string) ($record->subdistrict ?? ''),
                    'schoolSize' => (string) ($record->school_size ?? ''),
                    'studentsTotal' => (int) ($record->students_total ?? 0),
                    'roomsTotal' => (int) ($record->rooms_total ?? 0),
                    'person' => $this->educationMetrics($metrics, 'countPerson'),
                    'director' => $this->educationMetrics($metrics, 'countDirector'),
                    'viceDirector' => $this->educationMetrics($metrics, 'countViceDirector'),
                    'teacher' => $this->educationMetrics($metrics, 'countTeacher'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildAcademicStandingRows(array $metrics): array
    {
        $positions = [
            ['key' => 'directorArea', 'label' => 'ผอ.เขต', 'prefix' => 'countDirectorArea', 'scope' => 'สำนักงานเขตพื้นที่'],
            ['key' => 'viceDirectorArea', 'label' => 'รอง ผอ.เขต', 'prefix' => 'countViceDirectorArea', 'scope' => 'สำนักงานเขตพื้นที่'],
            ['key' => 'eduDirector', 'label' => 'เจ้าหน้าที่บริหารฯ', 'prefix' => 'countEduDirector', 'scope' => 'สำนักงานเขตพื้นที่'],
            ['key' => 'eduSupervisor', 'label' => 'ศึกษานิเทศก์', 'prefix' => 'countEduSupervisor', 'scope' => 'สำนักงานเขตพื้นที่'],
            ['key' => 'person38k', 'label' => '38 ค.(2)', 'prefix' => 'countPerson38k', 'scope' => 'สำนักงานเขตพื้นที่/โรงเรียน'],
            ['key' => 'directorSchool', 'label' => 'ผอ.ร.ร.', 'prefix' => 'countDirectorSchool', 'scope' => 'โรงเรียน'],
            ['key' => 'viceDirectorSchool', 'label' => 'รอง ผอ.ร.ร.', 'prefix' => 'countViceDirectorSchool', 'scope' => 'โรงเรียน'],
            ['key' => 'teacher', 'label' => 'ครู', 'prefix' => 'countTeacher', 'scope' => 'โรงเรียน'],
        ];

        return collect($positions)
            ->map(function ($position) use ($metrics) {
                return [
                    'key' => $position['key'],
                    'label' => $position['label'],
                    'scope' => $position['scope'],
                    'metrics' => $this->academicStandingMetrics($metrics, $position['prefix']),
                ];
            })
            ->filter(fn ($row) => ($row['metrics']['total'] ?? 0) > 0)
            ->values()
            ->all();
    }

    private function buildAcademicStandingSchoolRows($records): array
    {
        return collect($records)
            ->map(function ($record, $index) {
                $metrics = $this->decodeJson($record->metrics ?? null);
                $director = $this->academicStandingSchoolMetrics($metrics, 'countDirectorSchool');
                $viceDirector = $this->academicStandingSchoolMetrics($metrics, 'countViceDirectorSchool');
                $teacher = $this->academicStandingSchoolMetrics($metrics, 'countTeacher');
                $person = $this->sumAcademicStandingSchoolMetrics([$director, $viceDirector, $teacher]);
                $person['total'] = $this->firstMetric($metrics, ['countPersonSchoolSum', 'countPersonAllSum'], $person['total']);

                return [
                    'index' => $index + 1,
                    'schoolSmis' => (string) ($record->workload_school_smis ?? $record->school_smis ?? ''),
                    'schoolCode' => (string) ($record->school_code ?? ''),
                    'schoolName' => (string) ($record->school_name ?? ''),
                    'logoUrl' => SchoolLogo::url($record->logo_path ?? null),
                    'district' => (string) ($record->district ?? ''),
                    'subdistrict' => (string) ($record->subdistrict ?? ''),
                    'schoolSize' => (string) ($record->school_size ?? ''),
                    'person' => $person,
                    'director' => $director,
                    'viceDirector' => $viceDirector,
                    'teacher' => $teacher,
                ];
            })
            ->values()
            ->all();
    }

    private function sumAcademicStandingSchoolRows(array $rows): array
    {
        $total = $this->emptyAcademicStandingSchoolReportMetrics();

        foreach ($rows as $row) {
            foreach (array_keys($total) as $group) {
                $total[$group] = $this->sumAcademicStandingSchoolMetrics([
                    $total[$group],
                    $row[$group] ?? $this->emptyAcademicStandingSchoolMetrics(),
                ]);
            }
        }

        return $total;
    }

    private function sumPositionReportRows(array $rows): array
    {
        return $this->sumPositionGroupRows($rows, $this->emptyPositionMetrics());
    }

    private function sumPositionGroupRows(array $rows, array $emptyMetrics): array
    {
        $total = $emptyMetrics;

        foreach ($rows as $row) {
            foreach (array_keys($total) as $group) {
                foreach (array_keys($total[$group]) as $metric) {
                    $total[$group][$metric] += (int) ($row[$group][$metric] ?? 0);
                }
            }
        }

        return $total;
    }

    private function sumPositionGroups(array $groups): array
    {
        $total = [
            'all' => 0,
            'j18' => 0,
            'position' => 0,
            'empty' => 0,
            'noMoney' => 0,
            'condition' => 0,
            'emptyAll' => 0,
        ];

        foreach ($groups as $group) {
            foreach (array_keys($total) as $key) {
                $total[$key] += (int) ($group[$key] ?? 0);
            }
        }

        return $total;
    }

    private function sumGenderReportRows(array $rows): array
    {
        $total = $this->emptyGenderMetrics();

        foreach ($rows as $row) {
            $total['all'] += (int) ($row['all'] ?? 0);

            foreach (['male', 'female'] as $group) {
                foreach (array_keys($total[$group]) as $metric) {
                    $total[$group][$metric] += (int) ($row[$group][$metric] ?? 0);
                }
            }
        }

        return $total;
    }

    private function sumEducationReportRows(array $rows): array
    {
        $total = $this->emptyEducationMetrics();

        foreach ($rows as $row) {
            foreach (array_keys($total) as $group) {
                foreach (array_keys($total[$group]) as $metric) {
                    $total[$group][$metric] += (int) ($row[$group][$metric] ?? 0);
                }
            }
        }

        return $total;
    }

    private function emptyPositionMetrics(): array
    {
        $metrics = [
            'all' => 0,
            'j18' => 0,
            'position' => 0,
            'empty' => 0,
            'noMoney' => 0,
            'condition' => 0,
            'emptyAll' => 0,
        ];

        return [
            'person' => $metrics,
            'director' => $metrics,
            'viceDirector' => $metrics,
            'teacher' => $metrics,
        ];
    }

    private function emptyAreaPositionMetrics(): array
    {
        $metrics = [
            'all' => 0,
            'j18' => 0,
            'position' => 0,
            'empty' => 0,
            'noMoney' => 0,
            'condition' => 0,
            'emptyAll' => 0,
        ];

        return [
            'person' => $metrics,
            'areaDirector' => $metrics,
            'areaViceDirector' => $metrics,
            'eduDirector' => $metrics,
            'eduSupervisor' => $metrics,
            'person38k' => $metrics,
        ];
    }

    private function emptyGenderMetrics(): array
    {
        $metrics = [
            'person' => 0,
            'director' => 0,
            'viceDirector' => 0,
            'teacher' => 0,
        ];

        return [
            'all' => 0,
            'male' => $metrics,
            'female' => $metrics,
        ];
    }

    private function emptyEducationMetrics(): array
    {
        $metrics = [
            'total' => 0,
            'edu01' => 0,
            'edu02' => 0,
            'edu03' => 0,
            'edu035' => 0,
            'edu04' => 0,
            'edu05' => 0,
            'low' => 0,
            'high' => 0,
        ];

        return [
            'person' => $metrics,
            'director' => $metrics,
            'viceDirector' => $metrics,
            'teacher' => $metrics,
        ];
    }

    private function academicStandingLevels(): array
    {
        return [
            ['key' => 'level01', 'suffix' => 'Level01', 'label' => 'ไม่มีวิทยฐานะ'],
            ['key' => 'level02', 'suffix' => 'Level02', 'label' => 'ชำนาญการ'],
            ['key' => 'level03', 'suffix' => 'Level03', 'label' => 'ชำนาญการพิเศษ'],
            ['key' => 'level04', 'suffix' => 'Level04', 'label' => 'เชี่ยวชาญ'],
            ['key' => 'level05', 'suffix' => 'Level05', 'label' => 'เชี่ยวชาญพิเศษ'],
        ];
    }

    private function academicStandingSchoolLevels(): array
    {
        return [
            ['key' => 'level00', 'suffix' => 'Level00', 'label' => 'ไม่มีวิทยฐานะ'],
            ['key' => 'level11', 'suffix' => 'Level11', 'label' => 'ครูผู้ช่วย/คศ.1'],
            ['key' => 'level12', 'suffix' => 'Level12', 'label' => 'ชำนาญการ'],
            ['key' => 'level13', 'suffix' => 'Level13', 'label' => 'ชำนาญการพิเศษ'],
            ['key' => 'level14', 'suffix' => 'Level14', 'label' => 'เชี่ยวชาญ'],
            ['key' => 'level15', 'suffix' => 'Level15', 'label' => 'เชี่ยวชาญพิเศษ'],
        ];
    }

    private function emptyAcademicStandingMetrics(): array
    {
        return [
            'total' => 0,
            'level01' => 0,
            'level02' => 0,
            'level03' => 0,
            'level04' => 0,
            'level05' => 0,
        ];
    }

    private function emptyAcademicStandingSchoolMetrics(): array
    {
        return [
            'total' => 0,
            'level00' => 0,
            'level11' => 0,
            'level12' => 0,
            'level13' => 0,
            'level14' => 0,
            'level15' => 0,
        ];
    }

    private function emptyAcademicStandingSchoolReportMetrics(): array
    {
        $metrics = $this->emptyAcademicStandingSchoolMetrics();

        return [
            'person' => $metrics,
            'director' => $metrics,
            'viceDirector' => $metrics,
            'teacher' => $metrics,
        ];
    }

    private function academicStandingMetrics(array $metrics, string $prefix): array
    {
        $row = [
            'total' => $this->firstMetric($metrics, [$prefix]),
        ];

        foreach ($this->academicStandingLevels() as $level) {
            $row[$level['key']] = $this->firstMetric($metrics, [$prefix.$level['suffix']]);
        }

        return $row;
    }

    private function academicStandingSchoolMetrics(array $metrics, string $prefix): array
    {
        $row = [
            'total' => $this->firstMetric($metrics, [$prefix.'Sum', $prefix]),
        ];

        foreach ($this->academicStandingSchoolLevels() as $level) {
            $row[$level['key']] = $this->firstMetric($metrics, [$prefix.$level['suffix']]);
        }

        return $row;
    }

    private function sumAcademicStandingSchoolMetrics(array $rows): array
    {
        $total = $this->emptyAcademicStandingSchoolMetrics();

        foreach ($rows as $row) {
            foreach (array_keys($total) as $key) {
                $total[$key] += (int) ($row[$key] ?? 0);
            }
        }

        return $total;
    }

    private function positionMetrics(array $metrics, string $prefix): array
    {
        $empty = $this->firstMetric($metrics, [$prefix.'Empty']);
        $noMoney = $this->firstMetric($metrics, [$prefix.'NoMoney']);
        $condition = $this->firstMetric($metrics, [$prefix.'Condition']);

        return [
            'all' => $this->firstMetric($metrics, [$prefix.'All']),
            'j18' => $this->firstMetric($metrics, [$prefix.'J18']),
            'position' => $this->firstMetric($metrics, [$prefix.'Pos']),
            'empty' => $empty,
            'noMoney' => $noMoney,
            'condition' => $condition,
            'emptyAll' => $this->firstMetric($metrics, [$prefix.'EmptyAll'], $empty + $noMoney + $condition),
        ];
    }

    private function genderMetrics(array $metrics, string $suffix): array
    {
        $director = $this->firstMetric($metrics, ['countDirector'.$suffix]);
        $viceDirector = $this->firstMetric($metrics, ['countViceDirector'.$suffix]);
        $teacher = $this->firstMetric($metrics, ['countTeacher'.$suffix]);
        $person = $director + $viceDirector + $teacher;

        return [
            'person' => $person ?: $this->firstMetric($metrics, ['countPerson'.$suffix]),
            'director' => $director,
            'viceDirector' => $viceDirector,
            'teacher' => $teacher,
        ];
    }

    private function educationMetrics(array $metrics, string $prefix): array
    {
        return [
            'total' => $this->firstMetric($metrics, [$prefix]),
            'edu01' => $this->firstMetric($metrics, [$prefix.'Edu01']),
            'edu02' => $this->firstMetric($metrics, [$prefix.'Edu02']),
            'edu03' => $this->firstMetric($metrics, [$prefix.'Edu03']),
            'edu035' => $this->firstMetric($metrics, [$prefix.'Edu035']),
            'edu04' => $this->firstMetric($metrics, [$prefix.'Edu04']),
            'edu05' => $this->firstMetric($metrics, [$prefix.'Edu05']),
            'low' => $this->firstMetric($metrics, [$prefix.'EduLow']),
            'high' => $this->firstMetric($metrics, [$prefix.'EduHigh']),
        ];
    }

    private function buildPositionAvailableSchools(int $academicYear, string $term): array
    {
        $schools = DB::table('personnel_report_records as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis')
            ->where('records.report_key', 'report03')
            ->where('records.level', 'school')
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->whereNotNull('records.school_smis')
            ->select('records.school_smis', 'records.school_name', 'schools.logo_path')
            ->orderBy('records.school_smis')
            ->get()
            ->map(fn ($school) => [
                'schoolSmis' => (string) $school->school_smis,
                'schoolName' => (string) $school->school_name,
                'logoUrl' => SchoolLogo::url($school->logo_path ?? null),
                'label' => trim(((string) $school->school_smis).' '.((string) $school->school_name)),
            ])
            ->values()
            ->all();

        array_unshift($schools, [
            'schoolSmis' => '',
            'schoolName' => 'ภาพรวมทั้งเขต',
            'label' => 'ภาพรวมทั้งเขต',
        ]);

        return $schools;
    }

    private function buildGenderAvailableSchools(int $academicYear, string $term): array
    {
        $schools = DB::table('personnel_report_records as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis')
            ->where('records.report_key', 'report04')
            ->where('records.level', 'school')
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->whereNotNull('records.school_smis')
            ->select('records.school_smis', 'records.school_name', 'schools.logo_path')
            ->orderBy('records.school_smis')
            ->get()
            ->map(fn ($school) => [
                'schoolSmis' => (string) $school->school_smis,
                'schoolName' => (string) $school->school_name,
                'logoUrl' => SchoolLogo::url($school->logo_path ?? null),
                'label' => trim(((string) $school->school_smis).' '.((string) $school->school_name)),
            ])
            ->values()
            ->all();

        array_unshift($schools, [
            'schoolSmis' => '',
            'schoolName' => 'ภาพรวมทั้งเขต',
            'label' => 'ภาพรวมทั้งเขต',
        ]);

        return $schools;
    }

    private function buildEducationAvailableSchools(int $academicYear, string $term): array
    {
        $schools = DB::table('personnel_report_records as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis')
            ->where('records.report_key', 'report05')
            ->where('records.level', 'school')
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->whereNotNull('records.school_smis')
            ->select('records.school_smis', 'records.school_name', 'schools.logo_path')
            ->orderBy('records.school_smis')
            ->get()
            ->map(fn ($school) => [
                'schoolSmis' => (string) $school->school_smis,
                'schoolName' => (string) $school->school_name,
                'logoUrl' => SchoolLogo::url($school->logo_path ?? null),
                'label' => trim(((string) $school->school_smis).' '.((string) $school->school_name)),
            ])
            ->values()
            ->all();

        array_unshift($schools, [
            'schoolSmis' => '',
            'schoolName' => 'ภาพรวมทั้งเขต',
            'label' => 'ภาพรวมทั้งเขต',
        ]);

        return $schools;
    }

    private function buildAcademicStandingAvailableSchools(int $academicYear, string $term): array
    {
        $schools = DB::table('personnel_report_records as records')
            ->leftJoin('personnel_workload_schools as workload', function ($join) {
                $join->on('records.academic_year', '=', 'workload.academic_year')
                    ->on('records.term', '=', 'workload.term')
                    ->on('records.school_code', '=', 'workload.school_code');
            })
            ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
            ->where('records.report_key', 'report09')
            ->where('records.level', 'school')
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->select([
                'records.school_code',
                'records.school_name',
                'workload.school_smis',
                'schools.logo_path',
            ])
            ->orderByRaw('COALESCE(workload.school_smis, records.school_code)')
            ->get()
            ->map(function ($school) {
                $schoolSmis = (string) ($school->school_smis ?: $school->school_code);

                return [
                    'schoolSmis' => $schoolSmis,
                    'schoolName' => (string) $school->school_name,
                    'logoUrl' => SchoolLogo::url($school->logo_path ?? null),
                    'label' => trim($schoolSmis.' '.((string) $school->school_name)),
                ];
            })
            ->values()
            ->all();

        array_unshift($schools, [
            'schoolSmis' => '',
            'schoolName' => 'ภาพรวมโรงเรียนทั้งหมด',
            'label' => 'ภาพรวมโรงเรียนทั้งหมด',
        ]);

        return $schools;
    }

    private function buildAreaPersonnelRows(array $metrics): array
    {
        $positions = [
            ['label' => 'ผอ.สพท.', 'prefix' => 'countDirectorArea'],
            ['label' => 'รอง ผอ.สพท.', 'prefix' => 'countViceDirectorArea'],
            ['label' => 'เจ้าหน้าที่บริหารการศึกษาขั้นพื้นฐาน', 'prefix' => 'countEduDirector'],
            ['label' => 'ศึกษานิเทศก์', 'prefix' => 'countEduSupervisor'],
            ['label' => 'บุคลากรอื่นตามมาตรา 38 ค.(2)', 'prefix' => 'countPerson38k'],
        ];

        return collect($positions)
            ->map(function ($position, $index) use ($metrics) {
                $prefix = $position['prefix'];

                return [
                    'index' => $index + 1,
                    'label' => $position['label'],
                    'all' => $this->firstMetric($metrics, [$prefix.'All']),
                    'position' => $this->firstMetric($metrics, [$prefix.'Pos']),
                    'empty' => $this->firstMetric($metrics, [$prefix.'Empty']),
                    's04' => $this->firstMetric($metrics, [$prefix.'S04']),
                    'condition' => $this->firstMetric($metrics, [$prefix.'Condition']),
                    'noMoney' => $this->firstMetric($metrics, [$prefix.'NoMoney']),
                    'emptyAll' => $this->firstMetric($metrics, [$prefix.'EmptyAll']),
                ];
            })
            ->values()
            ->all();
    }

    private function sumAreaPersonnelRows(array $rows): array
    {
        return [
            'all' => collect($rows)->sum('all'),
            'position' => collect($rows)->sum('position'),
            'empty' => collect($rows)->sum('empty'),
            's04' => collect($rows)->sum('s04'),
            'condition' => collect($rows)->sum('condition'),
            'noMoney' => collect($rows)->sum('noMoney'),
            'emptyAll' => collect($rows)->sum('emptyAll'),
        ];
    }

    private function getLegacyDashboardPayload(?int $academicYear = null, ?string $term = null): array
    {
        $snapshots = DB::table('personnel_overview_records')
            ->select('academic_year', 'term', 'updated_at', 'area_name')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        $availableYears = $snapshots
            ->pluck('academic_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->all();

        $selectedYear = $academicYear && in_array($academicYear, $availableYears, true)
            ? $academicYear
            : ($availableYears[0] ?? null);

        $availableTerms = $snapshots
            ->where('academic_year', $selectedYear)
            ->pluck('term')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        $selectedTerm = $term && in_array((string) $term, $availableTerms, true)
            ? (string) $term
            : ($availableTerms[0] ?? null);

        $record = null;
        $payload = [];

        if ($selectedYear !== null && $selectedTerm !== null) {
            $record = DB::table('personnel_overview_records')
                ->where('academic_year', $selectedYear)
                ->where('term', $selectedTerm)
                ->orderByDesc('id')
                ->first();

            $payload = json_decode($record->payload ?? '[]', true);
            if (! is_array($payload)) {
                $payload = [];
            }
        }

        return [
            'selectedArea' => $record->area_name ?? 'สพป.ชุมพร เขต 1',
            'availableYears' => $availableYears,
            'availableTerms' => $availableTerms,
            'availableSchools' => [
                ['schoolSmis' => '', 'schoolName' => 'ภาพรวมทั้งเขต', 'label' => 'ภาพรวมทั้งเขต'],
            ],
            'selectedYear' => $selectedYear,
            'selectedTerm' => $selectedTerm,
            'selectedSchoolSmis' => '',
            'selectedScope' => 'area',
            'fetchedAt' => $record?->updated_at,
            'overview' => $this->buildOverview($record),
            'employmentSummary' => $this->buildEmploymentSummary($record),
            'positionSummary' => $this->buildPositionSummary($record),
            'personnelStatusSummary' => $this->buildStatusSummary($payload, 'countPerson10', 'ข้าราชการ'),
            'teacherStatusSummary' => $this->buildStatusSummary($payload, 'countTeacher', 'ครู'),
            'rawCodeSummary' => [
                'personnel' => $this->buildRawCodeSummary($payload, 'countPerson'),
                'position' => $this->buildRawCodeSummary($payload, 'count'),
            ],
            'workloadSummary' => [
                'schoolsCount' => 0,
                'studentsTotal' => 0,
                'roomsTotal' => 0,
                'teacherShortageTotal' => 0,
            ],
            'areaProfile' => [
                'director' => 0,
                'deputyDirector' => 0,
                'supervisor' => 0,
                'person38k' => 0,
                'sumPerson' => 0,
            ],
            'workloadTable' => [
                'rows' => [],
                'studentLevels' => [],
            ],
        ];
    }

    private function buildFullOverview(array $metrics, ?object $workloadSummary, ?object $batch): array
    {
        return [
            [
                'label' => 'บุคลากรรวมทั้งหมด',
                'value' => $this->employmentTotal($metrics),
                'icon' => 'fa-solid fa-users',
                'iconBg' => 'bg-orange-50 text-orange-500',
                'note' => 'ข้อมูลจาก report02 ใน local DB',
            ],
            [
                'label' => 'ครู',
                'value' => $this->firstMetric($metrics, ['countTeacherAll', 'countTeacherSum'], (int) ($workloadSummary->teacher_total ?? 0)),
                'icon' => 'fa-solid fa-chalkboard-user',
                'iconBg' => 'bg-sky-50 text-sky-500',
                'note' => 'ตำแหน่งครูทั้งหมด',
            ],
            [
                'label' => 'โรงเรียนที่จับคู่ได้',
                'value' => (int) ($batch->matched_schools_count ?? $workloadSummary->schools_count ?? 0),
                'icon' => 'fa-solid fa-school',
                'iconBg' => 'bg-emerald-50 text-emerald-500',
                'note' => 'นับเฉพาะที่ตรงกับฐานโรงเรียน local',
            ],
            [
                'label' => 'นักเรียนจาก workload',
                'value' => (int) ($workloadSummary->students_total ?? 0),
                'icon' => 'fa-solid fa-graduation-cap',
                'iconBg' => 'bg-violet-50 text-violet-500',
                'note' => 'รวมเฉพาะโรงเรียนที่จับคู่ได้',
            ],
        ];
    }

    private function buildSchoolOverview(array $metrics, object $school): array
    {
        return [
            [
                'label' => 'บุคลากรรวมทั้งหมด',
                'value' => $this->firstMetric($metrics, ['countPersonAll', 'countPersonSchoolSum'], $this->employmentTotal($metrics) ?: (int) ($school->personnel_total ?? 0)),
                'icon' => 'fa-solid fa-users',
                'iconBg' => 'bg-orange-50 text-orange-500',
                'note' => 'ข้อมูลรายโรงเรียนจาก workload',
            ],
            [
                'label' => 'ครู',
                'value' => $this->firstMetric($metrics, ['countTeacherAll', 'countTeacherSum'], (int) ($school->teacher_total ?? 0)),
                'icon' => 'fa-solid fa-chalkboard-user',
                'iconBg' => 'bg-sky-50 text-sky-500',
                'note' => 'ตำแหน่งครูในโรงเรียน',
            ],
            [
                'label' => 'นักเรียน',
                'value' => (int) ($school->students_total ?? 0),
                'icon' => 'fa-solid fa-graduation-cap',
                'iconBg' => 'bg-emerald-50 text-emerald-500',
                'note' => 'จำนวนนักเรียนจาก workload',
            ],
            [
                'label' => 'ห้องเรียน',
                'value' => (int) ($school->rooms_total ?? 0),
                'icon' => 'fa-solid fa-door-open',
                'iconBg' => 'bg-violet-50 text-violet-500',
                'note' => trim((string) ($school->school_size ?? '')) !== '' ? (string) $school->school_size : 'จำนวนห้องเรียน',
            ],
        ];
    }

    private function buildSchoolsOverview(array $metrics, ?object $workloadSummary, ?object $batch): array
    {
        return [
            [
                'label' => 'บุคลากรในโรงเรียนทั้งหมด',
                'value' => $this->firstMetric($metrics, ['countPersonAll', 'countPersonSchoolSum'], $this->employmentTotal($metrics) ?: (int) ($workloadSummary->personnel_total ?? 0)),
                'icon' => 'fa-solid fa-users',
                'iconBg' => 'bg-orange-50 text-orange-500',
                'note' => 'รวมเฉพาะข้อมูลรายโรงเรียน',
            ],
            [
                'label' => 'ครู',
                'value' => $this->firstMetric($metrics, ['countTeacherAll', 'countTeacherSum'], (int) ($workloadSummary->teacher_total ?? 0)),
                'icon' => 'fa-solid fa-chalkboard-user',
                'iconBg' => 'bg-sky-50 text-sky-500',
                'note' => 'ตำแหน่งครูในโรงเรียน',
            ],
            [
                'label' => 'โรงเรียนที่จับคู่ได้',
                'value' => (int) ($batch->matched_schools_count ?? $workloadSummary->schools_count ?? 0),
                'icon' => 'fa-solid fa-school',
                'iconBg' => 'bg-emerald-50 text-emerald-500',
                'note' => 'นับเฉพาะโรงเรียนใน local DB',
            ],
            [
                'label' => 'นักเรียนจาก workload',
                'value' => (int) ($workloadSummary->students_total ?? 0),
                'icon' => 'fa-solid fa-graduation-cap',
                'iconBg' => 'bg-violet-50 text-violet-500',
                'note' => 'รวมเฉพาะโรงเรียน',
            ],
        ];
    }

    private function buildFullEmploymentSummary(array $metrics): array
    {
        return [
            ['key' => 'government_officer_total', 'label' => 'ข้าราชการ', 'value' => $this->firstMetric($metrics, ['countPerson10All', 'countPerson10Sum'])],
            ['key' => 'civil_service_staff_total', 'label' => 'บุคลากร คศ.38 ค.', 'value' => $this->firstMetric($metrics, ['countPerson38All', 'countPerson38kAll', 'countPerson38Sum'])],
            ['key' => 'government_employee_total', 'label' => 'พนักงานราชการ', 'value' => $this->firstMetric($metrics, ['countPerson15All', 'countPerson15Sum'])],
            ['key' => 'temporary_employee_total', 'label' => 'ลูกจ้างชั่วคราว/จ้างเหมา', 'value' => $this->firstMetric($metrics, ['countPerson17All', 'countPerson17Sum'])],
            ['key' => 'permanent_employee_total', 'label' => 'ลูกจ้างประจำ', 'value' => $this->firstMetric($metrics, ['countPerson23All', 'countPerson23Sum'])],
        ];
    }

    private function buildFullPositionSummary(array $metrics, ?object $workloadSummary): array
    {
        $teacher = $this->firstMetric($metrics, ['countTeacherAll', 'countTeacherSum'], (int) ($workloadSummary->teacher_total ?? 0));
        $director = $this->firstMetric($metrics, ['countDirectorSchoolAll', 'countDirectorAll', 'countDirectorSchoolSum'], (int) ($workloadSummary->director_total ?? 0));
        $viceDirector = $this->firstMetric($metrics, ['countViceDirectorSchoolAll', 'countViceDirectorAll', 'countViceDirectorSchoolSum'], (int) ($workloadSummary->vice_director_total ?? 0));
        $areaDirector = $this->firstMetric($metrics, ['countDirectorAreaAll', 'countDirectorAreaSum', 'director']);
        $areaViceDirector = $this->firstMetric($metrics, ['countViceDirectorAreaAll', 'countViceDirectorAreaSum', 'deputyDirector']);
        $supervisor = $this->firstMetric($metrics, ['countEduSupervisorAll', 'countEduSupervisorSum', 'supervisor']);
        $person38k = $this->firstMetric($metrics, ['countPerson38kAll', 'countPerson38All', 'countPerson38kSum', 'countPerson38Sum', 'person38k']);
        $total = $this->employmentTotal($metrics) ?: (int) ($workloadSummary->personnel_total ?? 0);
        $other = max($total - $teacher - $director - $viceDirector - $areaDirector - $areaViceDirector - $supervisor - $person38k, 0);

        return [
            ['key' => 'area_director_total', 'label' => 'ผอ.เขต', 'value' => $areaDirector],
            ['key' => 'area_vice_director_total', 'label' => 'รอง ผอ.เขต', 'value' => $areaViceDirector],
            ['key' => 'supervisor_total', 'label' => 'ศึกษานิเทศก์', 'value' => $supervisor],
            ['key' => 'person38k_total', 'label' => '38 ค.', 'value' => $person38k],
            ['key' => 'teacher_total', 'label' => 'ครู', 'value' => $teacher],
            ['key' => 'director_total', 'label' => 'ผู้อำนวยการ', 'value' => $director],
            ['key' => 'vice_director_total', 'label' => 'รองผู้อำนวยการ', 'value' => $viceDirector],
            ['key' => 'other_total', 'label' => 'ตำแหน่งอื่น ๆ', 'value' => $other],
        ];
    }

    private function buildOverview(?object $record): array
    {
        return [
            [
                'label' => 'บุคลากรรวมทั้งหมด',
                'value' => (int) ($record->total_personnel ?? 0),
                'icon' => 'fa-solid fa-users',
                'iconBg' => 'bg-orange-50 text-orange-500',
                'note' => 'ข้อมูล snapshot จาก local DB',
            ],
            [
                'label' => 'ครู',
                'value' => (int) ($record->teacher_total ?? 0),
                'icon' => 'fa-solid fa-chalkboard-user',
                'iconBg' => 'bg-sky-50 text-sky-500',
                'note' => 'ตำแหน่งครูทั้งหมด',
            ],
            [
                'label' => 'ผู้อำนวยการ',
                'value' => (int) ($record->director_total ?? 0),
                'icon' => 'fa-solid fa-user-tie',
                'iconBg' => 'bg-emerald-50 text-emerald-500',
                'note' => 'รวมอัตราผู้อำนวยการ',
            ],
            [
                'label' => 'รองผู้อำนวยการ',
                'value' => (int) ($record->vice_director_total ?? 0),
                'icon' => 'fa-solid fa-user-group',
                'iconBg' => 'bg-violet-50 text-violet-500',
                'note' => 'รวมอัตรารองผู้อำนวยการ',
            ],
        ];
    }

    private function buildEmploymentSummary(?object $record): array
    {
        return [
            ['key' => 'government_officer_total', 'label' => 'ข้าราชการ', 'value' => (int) ($record->government_officer_total ?? 0)],
            ['key' => 'civil_service_staff_total', 'label' => 'บุคลากร คศ.38 ค.', 'value' => (int) ($record->civil_service_staff_total ?? 0)],
            ['key' => 'government_employee_total', 'label' => 'พนักงานราชการ', 'value' => (int) ($record->government_employee_total ?? 0)],
            ['key' => 'temporary_employee_total', 'label' => 'ลูกจ้างชั่วคราว/จ้างเหมา', 'value' => (int) ($record->temporary_employee_total ?? 0)],
            ['key' => 'permanent_employee_total', 'label' => 'ลูกจ้างประจำ', 'value' => (int) ($record->permanent_employee_total ?? 0)],
        ];
    }

    private function buildPositionSummary(?object $record): array
    {
        $teacher = (int) ($record->teacher_total ?? 0);
        $director = (int) ($record->director_total ?? 0);
        $viceDirector = (int) ($record->vice_director_total ?? 0);
        $other = max((int) ($record->total_personnel ?? 0) - $teacher - $director - $viceDirector, 0);

        return [
            ['key' => 'teacher_total', 'label' => 'ครู', 'value' => $teacher],
            ['key' => 'director_total', 'label' => 'ผู้อำนวยการ', 'value' => $director],
            ['key' => 'vice_director_total', 'label' => 'รองผู้อำนวยการ', 'value' => $viceDirector],
            ['key' => 'other_total', 'label' => 'ตำแหน่งอื่น ๆ', 'value' => $other],
        ];
    }

    private function buildStatusSummary(array $payload, string $prefix, string $labelPrefix): array
    {
        return [
            ['key' => $prefix.'Pos', 'label' => $labelPrefix.' คนครอง', 'value' => $this->toInt($payload[$prefix.'Pos'] ?? 0)],
            ['key' => $prefix.'Empty', 'label' => $labelPrefix.' อัตราว่าง', 'value' => $this->toInt($payload[$prefix.'Empty'] ?? 0)],
            ['key' => $prefix.'Condition', 'label' => $labelPrefix.' ติดเงื่อนไข', 'value' => $this->toInt($payload[$prefix.'Condition'] ?? 0)],
            ['key' => $prefix.'EmptyAll', 'label' => $labelPrefix.' ว่างรวม', 'value' => $this->toInt($payload[$prefix.'EmptyAll'] ?? 0)],
            ['key' => $prefix.'All', 'label' => $labelPrefix.' รวมทั้งหมด', 'value' => $this->toInt($payload[$prefix.'All'] ?? 0)],
        ];
    }

    private function buildRawCodeSummary(array $payload, string $keyPrefix): array
    {
        return collect($payload)
            ->filter(function ($value, $key) use ($keyPrefix) {
                return str_starts_with((string) $key, $keyPrefix) && is_numeric($value) && (int) $value > 0;
            })
            ->map(fn ($value, $key) => ['key' => (string) $key, 'value' => (int) $value])
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function decodeJson(?string $json): array
    {
        $payload = json_decode((string) $json, true);

        return is_array($payload) ? $payload : [];
    }

    private function buildAvailableSchools(int $academicYear, string $term, string $overviewLabel = 'ภาพรวมทั้งเขต'): array
    {
        $schools = DB::table('personnel_workload_schools as workload')
            ->leftJoin('system_school as schools', 'workload.school_id', '=', 'schools.id')
            ->where('workload.academic_year', $academicYear)
            ->where('workload.term', $term)
            ->whereNotNull('workload.school_id')
            ->whereNotNull('workload.school_smis')
            ->select('workload.school_smis', 'workload.school_name', 'schools.logo_path')
            ->orderBy('workload.school_smis')
            ->get()
            ->map(fn ($school) => [
                'schoolSmis' => (string) $school->school_smis,
                'schoolName' => (string) $school->school_name,
                'logoUrl' => SchoolLogo::url($school->logo_path ?? null),
                'label' => trim(((string) $school->school_smis).' '.((string) $school->school_name)),
            ])
            ->values()
            ->all();

        array_unshift($schools, [
            'schoolSmis' => '',
            'schoolName' => $overviewLabel,
            'label' => $overviewLabel,
        ]);

        return $schools;
    }

    private function aggregateSchoolMetrics($rows): array
    {
        $metrics = [];

        foreach ($rows as $row) {
            foreach ($this->extractNumericMetrics($this->decodeJson($row->payload ?? null)) as $key => $value) {
                $metrics[$key] = ($metrics[$key] ?? 0) + $value;
            }
        }

        return $metrics;
    }

    private function extractNumericMetrics(array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    private function buildWorkloadRows($rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                $students = $this->decodeJson($row->students ?? null);

                return [
                    'schoolSmis' => (string) ($row->school_smis ?? ''),
                    'schoolCode' => (string) ($row->school_code ?? ''),
                    'schoolName' => (string) ($row->school_name ?? ''),
                    'logoUrl' => SchoolLogo::url($row->logo_path ?? null),
                    'district' => (string) ($row->district ?? ''),
                    'subdistrict' => (string) ($row->subdistrict ?? ''),
                    'schoolType' => (string) ($row->school_type ?? ''),
                    'schoolSize' => (string) ($row->school_size ?? ''),
                    'studentsTotal' => (int) ($row->students_total ?? 0),
                    'roomsTotal' => (int) ($row->rooms_total ?? 0),
                    'personnelTotal' => (int) ($row->personnel_total ?? 0),
                    'directorTotal' => (int) ($row->director_total ?? 0),
                    'viceDirectorTotal' => (int) ($row->vice_director_total ?? 0),
                    'teacherTotal' => (int) ($row->teacher_total ?? 0),
                    'teacherShortageTotal' => (int) ($row->teacher_shortage_total ?? 0),
                    'studentLevels' => $this->studentLevelsFromPayload($students),
                ];
            })
            ->values()
            ->all();
    }

    private function buildStudentLevelSummary($rows): array
    {
        $summary = [
            'early' => ['label' => 'ก่อนประถม', 'students' => 0, 'rooms' => 0],
            'primary' => ['label' => 'ประถมศึกษา', 'students' => 0, 'rooms' => 0],
            'lowerSecondary' => ['label' => 'มัธยมต้น', 'students' => 0, 'rooms' => 0],
            'upperSecondary' => ['label' => 'มัธยมปลาย', 'students' => 0, 'rooms' => 0],
        ];

        foreach ($rows as $row) {
            $levels = $this->studentLevelsFromPayload($this->decodeJson($row->students ?? null));

            foreach ($summary as $key => $item) {
                $summary[$key]['students'] += $levels[$key]['students'] ?? 0;
                $summary[$key]['rooms'] += $levels[$key]['rooms'] ?? 0;
            }
        }

        return array_values($summary);
    }

    private function studentLevelsFromPayload(array $students): array
    {
        return [
            'early' => [
                'label' => 'ก่อนประถม',
                'students' => $this->toInt($students['aSumStudents'] ?? 0),
                'rooms' => $this->toInt($students['aSumRoom'] ?? 0),
            ],
            'primary' => [
                'label' => 'ประถมศึกษา',
                'students' => $this->toInt($students['pSumStudents'] ?? 0),
                'rooms' => $this->toInt($students['pSumRoom'] ?? 0),
            ],
            'lowerSecondary' => [
                'label' => 'มัธยมต้น',
                'students' => $this->toInt($students['m123Sum'] ?? 0),
                'rooms' => $this->toInt($students['m123SumRoom'] ?? 0),
            ],
            'upperSecondary' => [
                'label' => 'มัธยมปลาย',
                'students' => $this->toInt($students['m456pvcSumStudents'] ?? 0),
                'rooms' => $this->toInt($students['m456pvcSumRoom'] ?? 0),
            ],
        ];
    }

    private function firstMetric(array $metrics, array $keys, int $fallback = 0): int
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $metrics)) {
                return $this->toInt($metrics[$key]);
            }
        }

        return $fallback;
    }

    private function sumMetrics(array $metrics, array $keys): int
    {
        return collect($keys)->sum(fn ($key) => $this->firstMetric($metrics, [$key]));
    }

    private function employmentTotal(array $metrics): int
    {
        return $this->firstMetric($metrics, ['countPerson10All', 'countPerson10Sum'])
            + $this->firstMetric($metrics, ['countPerson38All', 'countPerson38kAll', 'countPerson38Sum'])
            + $this->firstMetric($metrics, ['countPerson15All', 'countPerson15Sum'])
            + $this->firstMetric($metrics, ['countPerson17All', 'countPerson17Sum'])
            + $this->firstMetric($metrics, ['countPerson23All', 'countPerson23Sum']);
    }
}
