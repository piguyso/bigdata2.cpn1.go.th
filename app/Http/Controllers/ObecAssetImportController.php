<?php

namespace App\Http\Controllers;

use App\Services\ObecAssetImportService;
use App\Support\AreaSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ObecAssetImportController extends Controller
{
    public function __construct(private readonly ObecAssetImportService $importService)
    {
    }

    public function index()
    {
        return view('admin.obec-asset');
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
        } catch (\Throwable $e) {
            Log::error('ObecAssetImportController@getData: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูล OBEC Asset ได้',
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
                'message' => 'ตรวจสอบข้อมูล OBEC Asset เรียบร้อยแล้ว',
                'preview' => $this->importService->preview((int) $validated['academic_year'], (string) $validated['term']),
            ]);
        } catch (\Throwable $e) {
            Log::error('ObecAssetImportController@preview: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'ไม่สามารถตรวจสอบข้อมูล OBEC Asset ได้',
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
                'message' => 'นำเข้าข้อมูล OBEC Asset เรียบร้อยแล้ว',
                'result' => $this->importService->import($validated['mode'] ?? 'replace', auth()->id(), (int) $validated['academic_year'], (string) $validated['term']),
            ]);
        } catch (\Throwable $e) {
            Log::error('ObecAssetImportController@import: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล OBEC Asset',
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'import_id' => ['required', 'integer'],
        ]);

        try {
            $result = $this->importService->deleteImport((int) $validated['import_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'ลบชุดข้อมูล OBEC Asset เรียบร้อยแล้ว',
                ...$result,
            ]);
        } catch (\Throwable $e) {
            Log::error('ObecAssetImportController@destroy: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการลบข้อมูล OBEC Asset',
            ], 500);
        }
    }

    private function getImports()
    {
        if (! Schema::hasTable('obec_asset_imports')) {
            return collect();
        }

        return DB::table('obec_asset_imports as imports')
            ->leftJoin('users', 'imports.created_by', '=', 'users.id')
            ->where('imports.area_code', AreaSettings::code())
            ->select('imports.*', 'users.name as created_by_name')
            ->orderByDesc('imports.id')
            ->limit(20)
            ->get();
    }

    private function getDataSets()
    {
        if (! Schema::hasTable('obec_asset_imports')) {
            return collect();
        }

        return DB::table('obec_asset_imports')
            ->where('area_code', AreaSettings::code())
            ->select('id', 'area_code', 'area_name', 'academic_year', 'term', 'school_rows_count', 'school_logos_count', 'building_records_count', 'matched_schools_count', 'unmatched_schools_count', 'created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
    }

    private function getLatestImported()
    {
        if (! Schema::hasTable('obec_asset_imports')) {
            return null;
        }

        return DB::table('obec_asset_imports')
            ->where('area_code', AreaSettings::code())
            ->select('id', 'area_code', 'area_name', 'academic_year', 'term', 'created_at')
            ->orderByDesc('id')
            ->first();
    }

    private function getRecordCount(): int
    {
        if (! Schema::hasTable('obec_asset_imports') || ! Schema::hasTable('obec_asset_buildings')) {
            return 0;
        }

        return DB::table('obec_asset_buildings as buildings')
            ->join('obec_asset_imports as imports', 'buildings.import_id', '=', 'imports.id')
            ->where('imports.area_code', AreaSettings::code())
            ->count();
    }
}
