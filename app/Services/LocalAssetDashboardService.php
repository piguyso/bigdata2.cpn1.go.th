<?php

namespace App\Services;

use App\Support\SchoolLogo;
use App\Support\AreaSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocalAssetDashboardService
{
    public function getDashboardPayload(
        ?string $schoolSmis = null,
        ?string $buildingType = null,
        ?string $condition = null,
        int $schoolPage = 1,
        int $typePage = 1,
        int $buildingPage = 1,
        int $perPage = 15
    ): array
    {
        if (! Schema::hasTable('obec_asset_imports') || ! Schema::hasTable('obec_asset_buildings')) {
            return $this->emptyPayload('ยังไม่มีตารางข้อมูล OBEC Asset ในฐานข้อมูล local');
        }

        $latestImport = DB::table('obec_asset_imports')
            ->where('area_code', AreaSettings::code())
            ->orderByDesc('id')
            ->first();
        if (! $latestImport) {
            return $this->emptyPayload('ยังไม่มีข้อมูล OBEC Asset ที่นำเข้า');
        }

        $schools = $this->availableSchools((int) $latestImport->id);
        $types = $this->availableTypes((int) $latestImport->id);
        $conditions = $this->availableConditions((int) $latestImport->id);

        $schoolSmis = $this->normalizeSelected($schoolSmis, $schools, 'school_smis');
        $buildingType = $this->normalizeSelected($buildingType, $types, 'value');
        $condition = $this->normalizeSelected($condition, $conditions, 'value');

        $query = DB::table('obec_asset_buildings')
            ->where('import_id', $latestImport->id);

        if ($schoolSmis) {
            $query->where('school_smis', $schoolSmis);
        }
        if ($buildingType) {
            $query->where('building_type', $buildingType);
        }
        if ($condition) {
            $query->where('condition', $condition);
        }

        $baseQuery = clone $query;
        $totalBuildings = (clone $baseQuery)->count();
        $totalBudget = (float) ((clone $baseQuery)->sum('budget') ?? 0);
        $totalSchools = (clone $baseQuery)->distinct('school_smis')->count('school_smis');
        $oldestYear = (clone $baseQuery)->whereNotNull('construction_year')->min('construction_year');
        $averageAge = (float) ((clone $baseQuery)->whereNotNull('age_years')->avg('age_years') ?? 0);

        return [
            'message' => null,
            'latestImport' => [
                'id' => (int) $latestImport->id,
                'areaCode' => (string) $latestImport->area_code,
                'areaName' => (string) $latestImport->area_name,
                'importedAt' => (string) $latestImport->created_at,
                'schoolRowsCount' => (int) $latestImport->school_rows_count,
                'schoolLogosCount' => (int) $latestImport->school_logos_count,
                'buildingRecordsCount' => (int) $latestImport->building_records_count,
            ],
            'selectedSchoolSmis' => $schoolSmis,
            'selectedBuildingType' => $buildingType,
            'selectedCondition' => $condition,
            'availableSchools' => $schools,
            'availableTypes' => $types,
            'availableConditions' => $conditions,
            'overview' => [
                ['label' => 'โรงเรียน', 'value' => $totalSchools, 'suffix' => 'แห่ง', 'icon' => 'fa-solid fa-school', 'iconBg' => 'bg-orange-50 text-orange-500', 'note' => 'ตามตัวกรองปัจจุบัน'],
                ['label' => 'สิ่งก่อสร้าง', 'value' => $totalBuildings, 'suffix' => 'รายการ', 'icon' => 'fa-solid fa-building', 'iconBg' => 'bg-sky-50 text-sky-500', 'note' => 'จาก OBEC Asset'],
                ['label' => 'งบประมาณรวม', 'value' => round($totalBudget, 2), 'suffix' => 'บาท', 'icon' => 'fa-solid fa-baht-sign', 'iconBg' => 'bg-emerald-50 text-emerald-500', 'note' => 'เฉพาะรายการที่มีงบประมาณ'],
                ['label' => 'อายุเฉลี่ย', 'value' => round($averageAge, 1), 'suffix' => 'ปี', 'icon' => 'fa-solid fa-clock-rotate-left', 'iconBg' => 'bg-violet-50 text-violet-500', 'note' => $oldestYear ? 'เก่าสุด พ.ศ. '.$oldestYear : 'ยังไม่มีปีที่ก่อสร้าง'],
            ],
            'typeSummary' => $this->typeSummary(clone $query, $typePage, $perPage),
            'conditionSummary' => $this->conditionSummary(clone $query, $totalBuildings),
            'statusSummary' => $this->statusSummary(clone $query, $totalBuildings),
            'schoolRows' => $this->schoolRows(clone $query, (int) $latestImport->id, $schoolPage, $perPage),
            'buildingRows' => $this->buildingRows(clone $query, $buildingPage, $perPage),
        ];
    }

    private function availableSchools(int $importId): array
    {
        return DB::table('obec_asset_schools')
            ->where('import_id', $importId)
            ->orderByRaw('CAST(school_smis AS UNSIGNED)')
            ->orderBy('school_smis')
            ->get()
            ->map(fn ($school) => [
                'school_smis' => (string) $school->school_smis,
                'school_name' => (string) $school->school_name,
                'district' => (string) $school->district,
                'logo_url' => SchoolLogo::url($school->logo_path),
            ])
            ->all();
    }

    private function availableTypes(int $importId): array
    {
        return DB::table('obec_asset_buildings')
            ->where('import_id', $importId)
            ->where('building_type', '!=', '')
            ->select('building_type')
            ->distinct()
            ->orderBy('building_type')
            ->get()
            ->map(fn ($row) => ['value' => (string) $row->building_type, 'label' => (string) $row->building_type])
            ->all();
    }

    private function availableConditions(int $importId): array
    {
        return DB::table('obec_asset_buildings')
            ->where('import_id', $importId)
            ->whereNotNull('condition')
            ->where('condition', '!=', '')
            ->select('condition')
            ->distinct()
            ->orderBy('condition')
            ->get()
            ->map(fn ($row) => ['value' => (string) $row->condition, 'label' => (string) $row->condition])
            ->all();
    }

    private function typeSummary($query, int $page, int $perPage): array
    {
        $grouped = $query
            ->select('building_type', DB::raw('COUNT(*) as buildings_count'), DB::raw('SUM(COALESCE(budget, 0)) as total_budget'))
            ->groupBy('building_type');
        $total = DB::query()->fromSub($grouped, 'type_summary')->count();
        $page = $this->normalizePage($page);
        $perPage = $this->normalizePerPage($perPage);
        $page = $this->clampPage($page, $total, $perPage);

        $rows = $grouped
            ->orderByDesc('buildings_count')
            ->orderBy('building_type')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($row) => [
                'label' => (string) ($row->building_type ?: 'ไม่ระบุประเภท'),
                'count' => (int) $row->buildings_count,
                'budget' => (float) $row->total_budget,
            ])
            ->all();

        return [
            'data' => $rows,
            'meta' => $this->paginationMeta($total, $page, $perPage),
        ];
    }

    private function conditionSummary($query, int $totalBuildings): array
    {
        return $query
            ->select('condition', DB::raw('COUNT(*) as buildings_count'))
            ->groupBy('condition')
            ->orderByDesc('buildings_count')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) ($row->condition ?: 'ไม่ระบุ'),
                'count' => (int) $row->buildings_count,
                'percent' => $totalBuildings > 0 ? round(((int) $row->buildings_count / $totalBuildings) * 100, 1) : 0,
            ])
            ->all();
    }

    private function statusSummary($query, int $totalBuildings): array
    {
        return $query
            ->select('usage_status', DB::raw('COUNT(*) as buildings_count'))
            ->groupBy('usage_status')
            ->orderByDesc('buildings_count')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) ($row->usage_status ?: 'ไม่ระบุ'),
                'count' => (int) $row->buildings_count,
                'percent' => $totalBuildings > 0 ? round(((int) $row->buildings_count / $totalBuildings) * 100, 1) : 0,
            ])
            ->all();
    }

    private function schoolRows($query, int $importId, int $page, int $perPage): array
    {
        $grouped = $query
            ->select(
                'school_smis',
                DB::raw('MAX(school_name) as school_name'),
                DB::raw('COUNT(*) as buildings_count'),
                DB::raw('SUM(COALESCE(budget, 0)) as total_budget'),
                DB::raw('AVG(age_years) as average_age'),
                DB::raw('MIN(construction_year) as oldest_year')
            )
            ->groupBy('school_smis');
        $total = DB::query()->fromSub($grouped, 'school_summary')->count();
        $page = $this->normalizePage($page);
        $perPage = $this->normalizePerPage($perPage);
        $page = $this->clampPage($page, $total, $perPage);

        $rows = $grouped
            ->orderByRaw('CAST(school_smis AS UNSIGNED)')
            ->orderBy('school_smis')
            ->forPage($page, $perPage)
            ->get();

        $logos = DB::table('obec_asset_schools')
            ->where('import_id', $importId)
            ->whereIn('school_smis', $rows->pluck('school_smis')->all())
            ->pluck('logo_path', 'school_smis');

        return [
            'data' => $rows->map(fn ($row) => [
            'school_smis' => (string) $row->school_smis,
            'school_name' => (string) $row->school_name,
            'logo_url' => SchoolLogo::url($logos[$row->school_smis] ?? null),
            'buildings_count' => (int) $row->buildings_count,
            'total_budget' => (float) $row->total_budget,
            'average_age' => round((float) $row->average_age, 1),
            'oldest_year' => $row->oldest_year ? (int) $row->oldest_year : null,
            ])->all(),
            'meta' => $this->paginationMeta($total, $page, $perPage),
        ];
    }

    private function buildingRows($query, int $page, int $perPage): array
    {
        $total = (clone $query)->count();
        $page = $this->normalizePage($page);
        $perPage = $this->normalizePerPage($perPage);
        $page = $this->clampPage($page, $total, $perPage);

        $rows = $query
            ->select(
                'id',
                'school_smis',
                'school_name',
                'building_type',
                'building_model',
                'main_image_url',
                'rooms_design',
                'rooms_actual',
                'rooms_special',
                'extension_classroom',
                'extension_special',
                'construction_year',
                'age_years',
                'budget',
                'budget_source',
                'condition',
                'usage_status',
                'extra_images',
                'payload'
            )
            ->orderByRaw('CAST(school_smis AS UNSIGNED)')
            ->orderBy('school_smis')
            ->orderBy('building_type')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($row) => [
                'school_smis' => (string) $row->school_smis,
                'school_name' => (string) $row->school_name,
                'id' => (int) $row->id,
                'building_type' => (string) $row->building_type,
                'building_model' => (string) $row->building_model,
                'main_image_url' => $row->main_image_url,
                'extra_images' => $this->decodeJsonArray($row->extra_images),
                'images' => $this->buildingImages($row),
                'rooms_design' => $row->rooms_design !== null ? (int) $row->rooms_design : null,
                'rooms_actual' => $row->rooms_actual !== null ? (int) $row->rooms_actual : null,
                'rooms_special' => $row->rooms_special !== null ? (int) $row->rooms_special : null,
                'extension_classroom' => $row->extension_classroom !== null ? (int) $row->extension_classroom : null,
                'extension_special' => $row->extension_special !== null ? (int) $row->extension_special : null,
                'construction_year' => $row->construction_year ? (int) $row->construction_year : null,
                'age_years' => $row->age_years ? (int) $row->age_years : null,
                'budget' => $row->budget !== null ? (float) $row->budget : null,
                'budget_source' => (string) ($row->budget_source ?? ''),
                'condition' => (string) ($row->condition ?? ''),
                'usage_status' => (string) ($row->usage_status ?? ''),
                'detail_lines' => $this->detailLines($row->payload),
            ])
            ->all();

        return [
            'data' => $rows,
            'meta' => $this->paginationMeta($total, $page, $perPage),
        ];
    }

    private function buildingImages(object $row): array
    {
        return collect([$row->main_image_url, ...$this->decodeJsonArray($row->extra_images)])
            ->map(fn ($url) => trim((string) $url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function detailLines(?string $payload): array
    {
        $decoded = $this->decodeJsonArray($payload);

        return collect($decoded['lines'] ?? [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    private function decodeJsonArray(?string $json): array
    {
        $decoded = json_decode((string) $json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizePage(int $page): int
    {
        return max(1, $page);
    }

    private function normalizePerPage(int $perPage): int
    {
        return min(50, max(10, $perPage));
    }

    private function clampPage(int $page, int $total, int $perPage): int
    {
        return min($page, max(1, (int) ceil($total / $perPage)));
    }

    private function paginationMeta(int $total, int $page, int $perPage): array
    {
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($total, $page * $perPage),
        ];
    }

    private function normalizeSelected(?string $value, array $options, string $key): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach ($options as $option) {
            if ((string) ($option[$key] ?? '') === $value) {
                return $value;
            }
        }

        return null;
    }

    private function emptyPayload(string $message): array
    {
        return [
            'message' => $message,
            'latestImport' => null,
            'selectedSchoolSmis' => null,
            'selectedBuildingType' => null,
            'selectedCondition' => null,
            'availableSchools' => [],
            'availableTypes' => [],
            'availableConditions' => [],
            'overview' => [],
            'typeSummary' => ['data' => [], 'meta' => $this->paginationMeta(0, 1, 15)],
            'conditionSummary' => [],
            'statusSummary' => [],
            'schoolRows' => ['data' => [], 'meta' => $this->paginationMeta(0, 1, 15)],
            'buildingRows' => ['data' => [], 'meta' => $this->paginationMeta(0, 1, 15)],
        ];
    }
}
