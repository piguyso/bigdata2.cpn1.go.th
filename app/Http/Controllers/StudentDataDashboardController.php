<?php

namespace App\Http\Controllers;

use App\Support\StudentDataTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDataDashboardController extends Controller
{
    public function index()
    {
        return view('student-data-dashboard', [
            'dataTypes' => StudentDataTypes::all(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => ['nullable', 'digits:4'],
            'term' => ['nullable', 'integer', 'min:1', 'max:3'],
            'data_type' => ['nullable', 'string'],
        ]);

        $dataType = $validated['data_type'] ?? 'class_gender';
        $definition = StudentDataTypes::get($dataType) ?? StudentDataTypes::get('class_gender');
        $academicYear = $validated['academic_year']
            ?? DB::table('student_data_records')->max('academic_year')
            ?? DB::table('academic_years')->where('is_active', true)->value('year')
            ?? '2569';
        $term = (string) ($validated['term'] ?? DB::table('student_data_records')->where('academic_year', $academicYear)->max('term') ?? '1');

        $rows = DB::table('student_data_records as records')
            ->leftJoin('system_school as schools', 'records.school_id', '=', 'schools.id')
            ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->where('records.data_type', $dataType)
            ->select(
                'records.*',
                'schools.schoolname',
                'schools.amper',
                'schools.tambon',
                'schools.schoolgroup',
                'groups.name as school_group_name'
            )
            ->orderByDesc('records.total')
            ->get();

        $schools = $rows->map(function ($row) {
            $metrics = json_decode($row->metrics ?? '[]', true) ?: [];

            return [
                'school_id' => $row->school_id,
                'school_smis' => $row->school_smis,
                'schoolname' => $row->schoolname ?: 'SMIS ' . $row->school_smis,
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

        $categories = $rows->groupBy('category')->map(fn ($items, $category) => [
            'label' => $category ?: '-',
            'records' => $items->count(),
            'male' => (int) $items->sum('total_male'),
            'female' => (int) $items->sum('total_female'),
            'total' => (int) $items->sum('total'),
        ])->values()->sortByDesc('total')->values();

        $available = DB::table('student_data_records')
            ->select('academic_year', 'term', 'data_type')
            ->groupBy('academic_year', 'term', 'data_type')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();

        return response()->json([
            'academic_year' => $academicYear,
            'term' => $term,
            'data_type' => $dataType,
            'label' => $definition['label'],
            'data_types' => StudentDataTypes::all(),
            'available_sets' => $available,
            'summary' => [
                'schools' => $rows->pluck('school_smis')->unique()->count(),
                'records' => $rows->count(),
                'male' => (int) $rows->sum('total_male'),
                'female' => (int) $rows->sum('total_female'),
                'total' => (int) $rows->sum('total'),
                'rooms' => (int) $rows->sum('rooms_total'),
            ],
            'categories' => $categories,
            'schools' => $schools,
        ]);
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
}
