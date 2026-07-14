<?php

namespace App\Http\Controllers;

use App\Services\OnetImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnetImportController extends Controller
{
    public function __construct(private readonly OnetImportService $importService)
    {
    }

    public function index()
    {
        return view('admin.onet');
    }

    public function getData(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'years' => DB::table('academic_years')->orderByDesc('sort_order')->orderByDesc('year')->get(),
                'active_year' => DB::table('academic_years')->where('is_active', true)->value('year'),
                'imports' => DB::table('onet_imports as imports')
                    ->leftJoin('users', 'imports.created_by', '=', 'users.id')
                    ->select('imports.*', 'users.name as created_by_name')
                    ->orderByDesc('imports.id')
                    ->limit(20)
                    ->get(),
                'data_sets' => DB::table('onet_records')
                    ->select(
                        'academic_year',
                        DB::raw('COUNT(*) as records_count'),
                        DB::raw('COUNT(DISTINCT school_code) as schools_count'),
                        DB::raw('COUNT(DISTINCT grade_code) as grades_count'),
                        DB::raw('MAX(updated_at) as latest_updated_at')
                    )
                    ->groupBy('academic_year')
                    ->orderByDesc('academic_year')
                    ->get(),
                'record_count' => DB::table('onet_records')->count(),
                'latest_imported_year' => DB::table('onet_records')->orderByDesc('academic_year')->value('academic_year'),
            ]);
        } catch (\Exception $e) {
            Log::error('OnetImportController@getData: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถโหลดข้อมูล ONET ได้'], 500);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate(['academic_year' => ['required', 'digits:4']]);

        try {
            return response()->json([
                'status' => 'success',
                'message' => 'ตรวจสอบข้อมูล ONET ที่พร้อมนำเข้าเรียบร้อยแล้ว',
                'preview' => $this->importService->preview((int) $validated['academic_year']),
            ]);
        } catch (\Exception $e) {
            Log::error('OnetImportController@preview: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถตรวจสอบข้อมูล ONET ได้'], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => ['required', 'digits:4'],
            'mode' => ['nullable', 'in:replace'],
        ]);

        try {
            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูล ONET เรียบร้อยแล้ว',
                'result' => $this->importService->import((int) $validated['academic_year'], $validated['mode'] ?? 'replace', auth()->id()),
            ]);
        } catch (\Exception $e) {
            Log::error('OnetImportController@import: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล ONET'], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate(['academic_year' => ['required', 'digits:4']]);

        try {
            $recordsQuery = DB::table('onet_records')->where('academic_year', $validated['academic_year']);
            $importsQuery = DB::table('onet_imports')->where('academic_year', $validated['academic_year']);
            $recordsCount = (clone $recordsQuery)->count();
            $importsCount = (clone $importsQuery)->count();

            if ($recordsCount === 0 && $importsCount === 0) {
                return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูล ONET ของปีการศึกษาที่เลือก'], 404);
            }

            DB::transaction(function () use ($recordsQuery, $importsQuery) {
                $recordsQuery->delete();
                $importsQuery->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูล ONET ของปีการศึกษา '.$validated['academic_year'].' เรียบร้อยแล้ว',
                'deleted_records' => $recordsCount,
                'deleted_imports' => $importsCount,
            ]);
        } catch (\Exception $e) {
            Log::error('OnetImportController@destroy: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล ONET'], 500);
        }
    }
}
