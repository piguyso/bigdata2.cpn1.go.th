<?php

namespace App\Http\Controllers;

use App\Services\BoppSchoolImportService;
use App\Support\AreaSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SchoolImportController extends Controller
{
    public function __construct(private readonly BoppSchoolImportService $importService)
    {
    }

    public function index()
    {
        return view('admin.school-import');
    }

    public function getData(): JsonResponse
    {
        try {
            $schoolCount = DB::table('system_school')->count();
            $areaCount   = DB::table('system_school')
                ->where('ministry', 'like', substr(AreaSettings::code(), 0, 8) . '%')
                ->count();

            $dmcCount = $this->importService->getDmcCount();

            $logs = Schema::hasTable('school_import_logs')
                ? $this->importService->getLogs()
                : [];

            return response()->json([
                'status'          => 'success',
                'area_code'       => AreaSettings::code(),
                'area_name'       => AreaSettings::name(),
                'school_count'    => $schoolCount,
                'area_count'      => $areaCount,
                'dmc_total'       => $dmcCount['total'],
                'dmc_area_count'  => $dmcCount['area'],
                'logs'            => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolImportController@getData: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลได้',
            ], 500);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'mode' => ['nullable', 'in:replace,merge'],
        ]);

        try {
            $preview = $this->importService->preview();

            return response()->json([
                'status'  => 'success',
                'message' => 'ตรวจสอบข้อมูลรายชื่อโรงเรียนเรียบร้อยแล้ว',
                'preview' => $preview,
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolImportController@preview: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?: 'ไม่สามารถตรวจสอบข้อมูลได้',
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:replace,merge'],
        ]);

        try {
            $result = $this->importService->import($validated['mode'], auth()->id());

            return response()->json([
                'status'  => 'success',
                'message' => 'นำเข้าข้อมูลรายชื่อโรงเรียนเรียบร้อยแล้ว ' . number_format($result['rows_imported']) . ' รายการ',
                'result'  => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolImportController@import: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล',
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $code = trim(AreaSettings::code());
            $smisPrefix = (strlen($code) === 10 && str_starts_with($code, '10')) 
                ? substr($code, 2, 4) 
                : substr($code, 0, 4);

            $deleted = DB::table('system_school')
                ->where('smis', 'like', $smisPrefix . '%')
                ->count();

            DB::transaction(function () use ($smisPrefix) {
                DB::table('system_school')
                    ->where('smis', 'like', $smisPrefix . '%')
                    ->delete();

                if (Schema::hasTable('school_import_logs')) {
                    DB::table('school_import_logs')
                        ->where('area_code', AreaSettings::code())
                        ->delete();
                }
            });

            return response()->json([
                'status'          => 'success',
                'message'         => 'ลบข้อมูลโรงเรียนในเขตพื้นที่เรียบร้อยแล้ว ' . number_format($deleted) . ' รายการ',
                'deleted_records' => $deleted,
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolImportController@destroy: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล',
            ], 500);
        }
    }

    public function deleteLog(int $id): JsonResponse
    {
        try {
            $log = DB::table('school_import_logs')
                ->where('id', $id)
                ->where('area_code', AreaSettings::code())
                ->first();

            if (!$log) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'ไม่พบประวัติการนำเข้าที่ต้องการลบ',
                ], 404);
            }

            DB::transaction(function () use ($id) {
                // Delete schools imported in this log batch
                DB::table('system_school')
                    ->where('import_log_id', $id)
                    ->delete();

                // Delete the log record
                DB::table('school_import_logs')->where('id', $id)->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'ลบประวัติการนำเข้าและข้อมูลโรงเรียนเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolImportController@deleteLog: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบประวัติการนำเข้า',
            ], 500);
        }
    }
}
