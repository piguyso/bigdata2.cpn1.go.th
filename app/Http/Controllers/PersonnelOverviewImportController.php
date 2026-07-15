<?php

namespace App\Http\Controllers;

use App\Services\PersonnelOverviewImportService;
use App\Support\AreaSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PersonnelOverviewImportController extends Controller
{
    public function __construct(private readonly PersonnelOverviewImportService $importService)
    {
    }

    public function index()
    {
        return view('admin.personnel-overview');
    }

    public function getData(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'current_remote' => $this->importService->getRemoteContext(),
                'years' => DB::table('academic_years')->orderByDesc('sort_order')->orderByDesc('year')->get(),
                'active_year' => DB::table('academic_years')->where('is_active', true)->value('year'),
                'imports' => $this->getImports(),
                'data_sets' => $this->getDataSets(),
                'record_count' => $this->getRecordCount(),
                'latest_imported' => $this->getLatestImported(),
            ]);
        } catch (\Exception $e) {
            Log::error('PersonnelOverviewImportController@getData: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลภาพรวมบุคลากรได้',
            ], 500);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => ['required', 'digits:4'],
            'term' => ['required', 'max:10'],
        ]);

        try {
            return response()->json([
                'status' => 'success',
                'message' => 'ตรวจสอบข้อมูลภาพรวมบุคลากรเรียบร้อยแล้ว',
                'preview' => $this->importService->preview((int) $validated['academic_year'], (string) $validated['term']),
            ]);
        } catch (\Exception $e) {
            Log::error('PersonnelOverviewImportController@preview: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'ไม่สามารถตรวจสอบข้อมูลภาพรวมบุคลากรได้',
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => ['required', 'digits:4'],
            'term' => ['required', 'max:10'],
            'mode' => ['nullable', 'in:replace'],
        ]);

        try {
            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูลภาพรวมบุคลากรเรียบร้อยแล้ว',
                'result' => $this->importService->import($validated['mode'] ?? 'replace', auth()->id(), (int) $validated['academic_year'], (string) $validated['term']),
            ]);
        } catch (\Exception $e) {
            Log::error('PersonnelOverviewImportController@import: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการนำเข้าข้อมูลภาพรวมบุคลากร',
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => ['required', 'digits:4'],
            'term' => ['required', 'max:10'],
        ]);

        try {
            $recordsQuery = DB::table('personnel_overview_records')
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term'])
                ->where('area_code', AreaSettings::code());

            $importsQuery = DB::table('personnel_overview_imports')
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term'])
                ->where('area_code', AreaSettings::code());

            $recordsCount = (clone $recordsQuery)->count();
            $importsCount = (clone $importsQuery)->count();
            $batchCount = Schema::hasTable('personnel_import_batches')
                ? DB::table('personnel_import_batches')
                    ->where('academic_year', $validated['academic_year'])
                    ->where('term', $validated['term'])
                    ->where('area_code', AreaSettings::code())
                    ->count()
                : 0;

            if ($recordsCount === 0 && $importsCount === 0 && $batchCount === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลภาพรวมบุคลากรของปีและรอบที่เลือก',
                ], 404);
            }

            DB::transaction(function () use ($recordsQuery, $importsQuery, $validated) {
                $this->importService->deleteSnapshot((int) $validated['academic_year'], (string) $validated['term']);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลภาพรวมบุคลากร ปี '.$validated['academic_year'].' รอบ '.$validated['term'].' เรียบร้อยแล้ว',
                'deleted_records' => $recordsCount,
                'deleted_imports' => $importsCount,
                'deleted_batches' => $batchCount,
            ]);
        } catch (\Exception $e) {
            Log::error('PersonnelOverviewImportController@destroy: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลภาพรวมบุคลากร',
            ], 500);
        }
    }

    private function getImports()
    {
        if (Schema::hasTable('personnel_import_batches')) {
            return DB::table('personnel_import_batches as imports')
                ->leftJoin('users', 'imports.created_by', '=', 'users.id')
                ->where('imports.area_code', AreaSettings::code())
                ->select('imports.*', 'users.name as created_by_name')
                ->orderByDesc('imports.id')
                ->limit(20)
                ->get();
        }

        return DB::table('personnel_overview_imports as imports')
            ->leftJoin('users', 'imports.created_by', '=', 'users.id')
            ->where('imports.area_code', AreaSettings::code())
            ->select('imports.*', 'users.name as created_by_name')
            ->orderByDesc('imports.id')
            ->limit(20)
            ->get();
    }

    private function getDataSets()
    {
        if (Schema::hasTable('personnel_import_batches')) {
            return DB::table('personnel_import_batches')
                ->where('area_code', AreaSettings::code())
                ->select(
                    'academic_year',
                    'term',
                    DB::raw('MAX(sources_count) as sources_count'),
                    DB::raw('MAX(normalized_records_count) as normalized_records_count'),
                    DB::raw('MAX(matched_schools_count) as matched_schools_count'),
                    DB::raw('MAX(unmatched_schools_count) as unmatched_schools_count'),
                    DB::raw('MAX(updated_at) as latest_updated_at')
                )
                ->groupBy('academic_year', 'term')
                ->orderByDesc('academic_year')
                ->orderByDesc('term')
                ->get();
        }

        return DB::table('personnel_overview_records')
            ->where('area_code', AreaSettings::code())
            ->select(
                'academic_year',
                'term',
                DB::raw('COUNT(*) as records_count'),
                DB::raw('MAX(total_personnel) as total_personnel'),
                DB::raw('MAX(updated_at) as latest_updated_at')
            )
            ->groupBy('academic_year', 'term')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get();
    }

    private function getRecordCount(): int
    {
        if (Schema::hasTable('personnel_import_sources')) {
            return DB::table('personnel_import_sources')->where('area_code', AreaSettings::code())->count();
        }

        return DB::table('personnel_overview_records')->where('area_code', AreaSettings::code())->count();
    }

    private function getLatestImported()
    {
        if (Schema::hasTable('personnel_import_batches')) {
            return DB::table('personnel_import_batches')
                ->where('area_code', AreaSettings::code())
                ->select('academic_year', 'term')
                ->orderByDesc('academic_year')
                ->orderByDesc('term')
                ->first();
        }

        return DB::table('personnel_overview_records')
            ->where('area_code', AreaSettings::code())
            ->select('academic_year', 'term')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->first();
    }
}
