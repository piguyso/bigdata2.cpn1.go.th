<?php

namespace App\Http\Controllers;

use App\Support\StudentDataTypes;
use App\Support\SchoolLogo;
use App\Support\SimpleXlsxExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentDataDashboardController extends Controller
{
    public function index()
    {
        $academicYears = $this->academicYears();
        $activeAcademicYear = $this->activeAcademicYear();

        return view('student-data-dashboard', [
            'dataTypes' => StudentDataTypes::all(),
            'defaultDataType' => StudentDataTypes::defaultKey(),
            'academicYears' => $academicYears,
            'activeAcademicYear' => $activeAcademicYear
                ?? ($academicYears->first()->year ?? null)
                ?? DB::table('student_data_records')->max('academic_year')
                ?? '2569',
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => ['nullable', 'digits:4'],
            'term' => ['nullable', 'integer', 'min:1', 'max:3'],
            'data_type' => ['nullable', 'string', Rule::in(StudentDataTypes::keys())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:48'],
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $context = $this->dashboardContext($validated);
        $academicYears = $context['academicYears'];
        $academicYear = $context['academicYear'];
        $term = $context['term'];
        $dataType = $context['dataType'];
        $definition = $context['definition'];
        $category = $context['category'];
        $baseQuery = $context['baseQuery'];
        $dataQuery = $context['dataQuery'];

        $summary = (clone $dataQuery)
            ->selectRaw('COUNT(*) as records_count')
            ->selectRaw('COUNT(DISTINCT records.school_smis) as schools_count')
            ->selectRaw('COALESCE(SUM(records.total_male), 0) as male_total')
            ->selectRaw('COALESCE(SUM(records.total_female), 0) as female_total')
            ->selectRaw('COALESCE(SUM(records.total), 0) as total_count')
            ->selectRaw('COALESCE(SUM(records.rooms_total), 0) as rooms_count')
            ->first();

        $categories = (clone $baseQuery)
            ->select(
                DB::raw("COALESCE(NULLIF(records.category, ''), '-') as label"),
                DB::raw('COUNT(*) as records'),
                DB::raw('COALESCE(SUM(records.total_male), 0) as male'),
                DB::raw('COALESCE(SUM(records.total_female), 0) as female'),
                DB::raw('COALESCE(SUM(records.total), 0) as total')
            )
            ->groupBy(DB::raw("COALESCE(NULLIF(records.category, ''), '-')"))
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn ($category) => [
                'label' => $category->label,
                'records' => (int) $category->records,
                'male' => (int) $category->male,
                'female' => (int) $category->female,
                'total' => (int) $category->total,
            ]);

        $rowsQuery = (clone $dataQuery);

        $perPage = (int) ($validated['per_page'] ?? 12);
        $page = (int) ($validated['page'] ?? 1);
        $paginator = $rowsQuery
            ->select(
                'records.*',
                'schools.schoolname',
                'schools.amper',
                'schools.tambon',
                'schools.schoolgroup',
                'schools.logo_path',
                'groups.name as school_group_name'
            )
            ->orderBy('records.school_smis')
            ->paginate($perPage, ['*'], 'page', $page);

        $schools = collect($paginator->items())->map(function ($row) {
            $metrics = json_decode($row->metrics ?? '[]', true) ?: [];

            return [
                'school_id' => $row->school_id,
                'school_smis' => $row->school_smis,
                'schoolname' => $row->schoolname ?: 'SMIS ' . $row->school_smis,
                'logo_url' => SchoolLogo::url($row->logo_path ?? null),
                'district' => $row->amper,
                'subdistrict' => $row->tambon,
                'school_group' => $row->school_group_name ?: $row->schoolgroup,
                'category' => $row->category,
                'male' => (int) $row->total_male,
                'female' => (int) $row->total_female,
                'total' => (int) $row->total,
                'rooms' => (int) $row->rooms_total,
                'top_metrics' => $this->topMetrics($metrics),
                'metrics' => $metrics,
            ];
        })->values();

        $available = DB::table('student_data_records')
            ->select('academic_year', 'term', 'data_type')
            ->whereIn('data_type', array_keys(StudentDataTypes::all()))
            ->groupBy('academic_year', 'term', 'data_type')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        return response()->json([
            'academic_year' => $academicYear,
            'term' => $term,
            'data_type' => $dataType,
            'category' => $category,
            'label' => $definition['label'],
            'data_types' => StudentDataTypes::all(),
            'academic_years' => $academicYears,
            'available_sets' => $available,
            'summary' => [
                'schools' => (int) ($summary->schools_count ?? 0),
                'records' => (int) ($summary->records_count ?? 0),
                'male' => (int) ($summary->male_total ?? 0),
                'female' => (int) ($summary->female_total ?? 0),
                'total' => (int) ($summary->total_count ?? 0),
                'rooms' => (int) ($summary->rooms_count ?? 0),
            ],
            'categories' => $categories,
            'schools' => $schools,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function exportXlsx(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'academic_year' => ['nullable', 'digits:4'],
            'term' => ['nullable', 'integer', 'min:1', 'max:3'],
            'data_type' => ['nullable', 'string', Rule::in(StudentDataTypes::keys())],
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $context = $this->dashboardContext($validated);
        $rows = (clone $context['dataQuery'])
            ->select(
                'records.academic_year',
                'records.term',
                'records.data_type',
                'records.category',
                'records.school_smis',
                'records.total_male',
                'records.total_female',
                'records.total',
                'records.rooms_total',
                'schools.schoolname',
                'schools.amper',
                'schools.tambon',
                'groups.name as school_group_name'
            )
            ->orderBy('records.school_smis')
            ->get()
            ->map(fn ($row) => [
                $row->academic_year,
                (string) $row->term,
                $context['definition']['label'],
                $row->category ?: '-',
                $row->school_smis,
                $row->schoolname ?: 'SMIS ' . $row->school_smis,
                $row->school_group_name ?: '',
                $row->amper ?: '',
                $row->tambon ?: '',
                (string) ((int) $row->total_male),
                (string) ((int) $row->total_female),
                (string) ((int) $row->total),
                (string) ((int) $row->rooms_total),
            ])
            ->all();

        return SimpleXlsxExporter::download(
            $this->exportFilename($context['academicYear'], $context['term'], $context['dataType'], $context['category']),
            $this->exportHeaders(),
            $rows
        );
    }

    private function topMetrics(array $metrics): array
    {
        return collect($metrics)
            ->map(fn ($metric, $key) => [
                'key' => $key,
                'label' => $metric['label'] ?? $key,
                'total' => (int) ($metric['total'] ?? $metric['value'] ?? 0),
            ])
            ->filter(fn ($metric) => $metric['total'] > 0)
            ->sortByDesc('total')
            ->take(4)
            ->values()
            ->all();
    }

    private function dashboardContext(array $validated): array
    {
        $dataType = $validated['data_type'] ?? StudentDataTypes::defaultKey();
        $definition = StudentDataTypes::get($dataType);
        if (! $definition) {
            $dataType = StudentDataTypes::defaultKey();
            $definition = StudentDataTypes::get($dataType);
        }

        $academicYears = $this->academicYears();
        $academicYear = $validated['academic_year']
            ?? $this->activeAcademicYear()
            ?? ($academicYears->first()->year ?? null)
            ?? DB::table('student_data_records')->max('academic_year')
            ?? '2569';
        $term = (string) ($validated['term'] ?? DB::table('student_data_records')->where('academic_year', $academicYear)->max('term') ?? '1');
        $search = trim((string) ($validated['search'] ?? ''));
        $category = trim((string) ($validated['category'] ?? ''));

        $groupNames = DB::table('system_group')
            ->select('code', DB::raw('MIN(name) as name'))
            ->groupBy('code');

        $baseQuery = DB::table('student_data_records as records')
            ->leftJoin('system_school as schools', 'records.school_id', '=', 'schools.id')
            ->leftJoinSub($groupNames, 'groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->where('records.data_type', $dataType)
            ->where('records.total', '>', 0);

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('records.school_smis', 'like', "%{$search}%")
                    ->orWhere('records.category', 'like', "%{$search}%")
                    ->orWhere('schools.schoolname', 'like', "%{$search}%")
                    ->orWhere('schools.amper', 'like', "%{$search}%")
                    ->orWhere('schools.tambon', 'like', "%{$search}%")
                    ->orWhere('groups.name', 'like', "%{$search}%");
            });
        }

        $dataQuery = clone $baseQuery;

        if ($category !== '') {
            $dataQuery->where(function ($query) use ($category) {
                if ($category === '-') {
                    $query->whereNull('records.category')
                        ->orWhere('records.category', '');

                    return;
                }

                $query->where('records.category', $category);
            });
        }

        return compact('academicYears', 'academicYear', 'term', 'dataType', 'definition', 'category', 'baseQuery', 'dataQuery');
    }

    private function exportHeaders(): array
    {
        return [
            'ปีการศึกษา',
            'รอบ',
            'ชนิดข้อมูล',
            'หมวด',
            'SMIS',
            'ชื่อโรงเรียน',
            'เครือข่าย',
            'อำเภอ',
            'ตำบล',
            'ชาย',
            'หญิง',
            'รวม',
            'ห้อง',
        ];
    }

    private function exportFilename(string $academicYear, string $term, string $dataType, string $category): string
    {
        $parts = ['student-data', $academicYear, 'term'.$term, $dataType];

        if ($category !== '') {
            $parts[] = preg_replace('/[^A-Za-z0-9ก-๙_-]+/u', '-', $category) ?: 'category';
        }

        return implode('_', $parts).'.xlsx';
    }

    private function academicYears()
    {
        $years = DB::table('academic_years')
            ->select('year', 'name', 'is_active')
            ->orderByDesc('sort_order')
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get();

        if ($years->isNotEmpty()) {
            return $years;
        }

        return DB::table('student_data_records')
            ->select('academic_year')
            ->groupBy('academic_year')
            ->orderByDesc('academic_year')
            ->get()
            ->map(fn ($row) => (object) [
                'year' => $row->academic_year,
                'name' => 'ปีการศึกษา ' . $row->academic_year,
                'is_active' => false,
            ]);
    }

    private function activeAcademicYear(): ?string
    {
        return DB::table('academic_years')
            ->where('is_active', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->value('year');
    }
}
