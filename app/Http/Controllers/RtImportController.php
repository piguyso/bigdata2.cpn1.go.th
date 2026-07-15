<?php

namespace App\Http\Controllers;

use App\Services\RtImportService;
use App\Support\SimpleXlsxExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RtImportController extends Controller
{
    public function __construct(private readonly RtImportService $importService)
    {
    }

    public function index(): View
    {
        return view('admin.rt');
    }

    public function downloadTemplate()
    {
        return SimpleXlsxExporter::download(
            'rt-import-template.xlsx',
            [
                'no',
                'unused',
                'rt_school_code',
                'school_name',
                'district',
                'school_size',
                'students_count',
                'reading_aloud_percent',
                'reading_aloud_sd',
                'reading_aloud_max',
                'reading_aloud_min',
                'reading_aloud_mode',
                'reading_aloud_median',
                'reading_comprehension_percent',
                'reading_comprehension_sd',
                'reading_comprehension_max',
                'reading_comprehension_min',
                'reading_comprehension_mode',
                'reading_comprehension_median',
                'total_percent',
                'total_sd',
                'total_max',
                'total_min',
                'total_mode',
                'total_median',
            ],
            [[
                '1',
                '',
                '1000000001',
                'โรงเรียนตัวอย่าง',
                'เมืองชุมพร',
                'กลาง',
                '20',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
            ]]
        );
    }

    public function getData(): JsonResponse
    {
        try {
            $years = DB::table('academic_years')
                ->orderByDesc('sort_order')
                ->orderByDesc('year')
                ->get();

            $imports = DB::table('rt_imports as imports')
                ->leftJoin('users', 'imports.created_by', '=', 'users.id')
                ->select('imports.*', 'users.name as created_by_name')
                ->orderByDesc('imports.id')
                ->limit(20)
                ->get();

            $dataSets = DB::table('rt_records')
                ->select(
                    'academic_year',
                    DB::raw('COUNT(*) as records_count'),
                    DB::raw('AVG(total_percent) as avg_total_percent'),
                    DB::raw('MAX(updated_at) as latest_updated_at')
                )
                ->groupBy('academic_year')
                ->orderByDesc('academic_year')
                ->get();

            return response()->json([
                'status' => 'success',
                'years' => $years,
                'active_year' => DB::table('academic_years')->where('is_active', true)->value('year'),
                'imports' => $imports,
                'data_sets' => $dataSets,
                'record_count' => DB::table('rt_records')->count(),
                'latest_imported_year' => DB::table('rt_records')->orderByDesc('id')->value('academic_year'),
            ]);
        } catch (\Throwable $e) {
            Log::error('RtImportController@getData: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูล RT ได้',
            ], 500);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'xlsx' => ['required', 'file', 'mimes:csv,txt,xlsx'],
            ]);

            $file = $request->file('xlsx');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
            $token = Str::uuid()->toString().'.'.$extension;
            $storedPath = $file->storeAs('rt-imports/tmp', $token);
            $preview = $this->importService->preview(Storage::path($storedPath));

            if (($preview['valid_rows'] ?? 0) === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่พบแถวข้อมูล RT ที่นำเข้าได้',
                    'preview' => $preview,
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'ตรวจสอบไฟล์ RT เรียบร้อยแล้ว',
                'upload_token' => $token,
                'source_filename' => $file->getClientOriginalName(),
                'preview' => $preview,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('RtImportController@preview: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่สามารถตรวจสอบไฟล์ RT ได้',
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'upload_token' => ['required', 'string'],
                'source_filename' => ['required', 'string', 'max:255'],
                'mode' => ['nullable', 'in:replace'],
            ]);

            $academicYear = DB::table('academic_years')->where('year', $validated['academic_year'])->first();
            if (! $academicYear) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบปีการศึกษาที่เลือกในระบบ',
                ], 422);
            }

            $storedPath = 'rt-imports/tmp/'.basename($validated['upload_token']);
            if (! Storage::exists($storedPath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบไฟล์ชั่วคราว กรุณาอัปโหลดใหม่อีกครั้ง',
                ], 422);
            }

            $parsed = $this->importService->parseForImport(Storage::path($storedPath));
            $matchedRows = array_values(array_filter($parsed['rows'], fn ($row) => $row['matched_school']));
            if (count($matchedRows) === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียนที่จับคู่กับ system_school ได้ จึงยังไม่บันทึกข้อมูลลงฐานข้อมูล',
                ], 422);
            }

            $mode = $validated['mode'] ?? 'replace';
            $importedRows = count($matchedRows);

            $importId = DB::transaction(function () use ($academicYear, $validated, $parsed, $matchedRows, $mode, $importedRows) {
                if ($mode === 'replace') {
                    DB::table('rt_records')->where('academic_year', $validated['academic_year'])->delete();
                    DB::table('rt_imports')->where('academic_year', $validated['academic_year'])->delete();
                }

                $importId = DB::table('rt_imports')->insertGetId([
                    'academic_year_id' => $academicYear->id,
                    'academic_year' => $validated['academic_year'],
                    'source_filename' => $validated['source_filename'],
                    'stored_filename' => basename($validated['upload_token']),
                    'sheet_name' => $parsed['sheet_name'] ?? 'Local05',
                    'schema_version' => 'rt_auto_school_rows',
                    'total_rows' => $parsed['total_rows'],
                    'valid_rows' => $parsed['valid_rows'],
                    'imported_rows' => $importedRows,
                    'unmatched_rows' => $parsed['unmatched_rows'],
                    'invalid_rows' => $parsed['invalid_rows'],
                    'mode' => $mode,
                    'created_by' => auth()->id(),
                    'warnings' => json_encode($parsed['warnings'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $payload = array_map(fn ($row) => [
                    'import_id' => $importId,
                    'academic_year_id' => $academicYear->id,
                    'academic_year' => $validated['academic_year'],
                    'school_id' => $row['school_id'],
                    'rt_school_code' => $row['rt_school_code'],
                    'school_smis' => $row['system_smis'],
                    'school_name' => $row['school_name'],
                    'district' => $row['district'],
                    'school_size' => $row['school_size'],
                    'students_count' => $row['students_count'],
                    'reading_aloud_percent' => $row['reading_aloud_percent'],
                    'reading_aloud_sd' => $row['reading_aloud_sd'],
                    'reading_aloud_max' => $row['reading_aloud_max'],
                    'reading_aloud_min' => $row['reading_aloud_min'],
                    'reading_aloud_mode' => $row['reading_aloud_mode'],
                    'reading_aloud_median' => $row['reading_aloud_median'],
                    'reading_comprehension_percent' => $row['reading_comprehension_percent'],
                    'reading_comprehension_sd' => $row['reading_comprehension_sd'],
                    'reading_comprehension_max' => $row['reading_comprehension_max'],
                    'reading_comprehension_min' => $row['reading_comprehension_min'],
                    'reading_comprehension_mode' => $row['reading_comprehension_mode'],
                    'reading_comprehension_median' => $row['reading_comprehension_median'],
                    'total_percent' => $row['total_percent'],
                    'total_sd' => $row['total_sd'],
                    'total_max' => $row['total_max'],
                    'total_min' => $row['total_min'],
                    'total_mode' => $row['total_mode'],
                    'total_median' => $row['total_median'],
                    'payload' => json_encode($row['payload'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $matchedRows);

                DB::table('rt_records')->upsert(
                    $payload,
                    ['academic_year', 'rt_school_code'],
                    ['import_id', 'academic_year_id', 'school_id', 'school_smis', 'school_name', 'district', 'school_size', 'students_count', 'reading_aloud_percent', 'reading_aloud_sd', 'reading_aloud_max', 'reading_aloud_min', 'reading_aloud_mode', 'reading_aloud_median', 'reading_comprehension_percent', 'reading_comprehension_sd', 'reading_comprehension_max', 'reading_comprehension_min', 'reading_comprehension_mode', 'reading_comprehension_median', 'total_percent', 'total_sd', 'total_max', 'total_min', 'total_mode', 'total_median', 'payload', 'updated_at']
                );

                return $importId;
            });

            Storage::delete($storedPath);

            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูล RT เรียบร้อยแล้ว ('.$importedRows.' โรงเรียน)',
                'import_id' => $importId,
                'imported_rows' => $importedRows,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('RtImportController@import: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล RT',
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
            ]);

            $recordsQuery = DB::table('rt_records')->where('academic_year', $validated['academic_year']);
            $importsQuery = DB::table('rt_imports')->where('academic_year', $validated['academic_year']);
            $recordsCount = (clone $recordsQuery)->count();
            $importsCount = (clone $importsQuery)->count();

            if ($recordsCount === 0 && $importsCount === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูล RT ของปีที่เลือก',
                ], 404);
            }

            DB::transaction(function () use ($recordsQuery, $importsQuery) {
                $recordsQuery->delete();
                $importsQuery->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูล RT ปีการศึกษา '.$validated['academic_year'].' เรียบร้อยแล้ว',
                'deleted_records' => $recordsCount,
                'deleted_imports' => $importsCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('RtImportController@destroy: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล RT',
            ], 500);
        }
    }
}
