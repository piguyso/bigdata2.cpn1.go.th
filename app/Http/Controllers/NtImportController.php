<?php

namespace App\Http\Controllers;

use App\Services\NtImportService;
use App\Support\SimpleXlsxExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NtImportController extends Controller
{
    public function __construct(private readonly NtImportService $importService)
    {
    }

    public function index(): View
    {
        return view('admin.nt');
    }

    public function downloadTemplate()
    {
        return SimpleXlsxExporter::downloadCsv(
            'nt-import-template.csv',
            [
                'nt_school_code',
                'school_name',
                'district',
                'school_size',
                'math_score',
                'math_percent',
                'thai_score',
                'thai_percent',
                'total_score',
                'total_percent',
                'math_quality',
                'thai_quality',
                'total_quality',
            ],
            [[
                '1000000001',
                'โรงเรียนตัวอย่าง',
                'เมืองชุมพร',
                'กลาง',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                'พอใช้',
                'พอใช้',
                'พอใช้',
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

            $imports = DB::table('nt_imports as imports')
                ->leftJoin('users', 'imports.created_by', '=', 'users.id')
                ->select('imports.*', 'users.name as created_by_name')
                ->orderByDesc('imports.id')
                ->limit(20)
                ->get();

            $dataSets = DB::table('nt_records')
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
                'record_count' => DB::table('nt_records')->count(),
                'latest_imported_year' => DB::table('nt_records')->orderByDesc('id')->value('academic_year'),
            ]);
        } catch (\Throwable $e) {
            Log::error('NtImportController@getData: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูล NT ได้',
            ], 500);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'csv' => ['required', 'file', 'mimes:csv,txt'],
            ]);

            $file = $request->file('csv');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');
            $token = Str::uuid()->toString().'.'.$extension;
            $storedPath = $file->storeAs('nt-imports/tmp', $token);
            $preview = $this->importService->preview(Storage::path($storedPath));

            if (($preview['valid_rows'] ?? 0) === 0) {
                $detail = '';
                if (!empty($preview['invalid_samples'])) {
                    $firstErr = $preview['invalid_samples'][0];
                    $detail = ' (แถวที่ ' . $firstErr['row_number'] . ': ' . $firstErr['reason'] . ')';
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่พบแถวข้อมูล NT ที่นำเข้าได้' . $detail,
                    'preview' => $preview,
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'ตรวจสอบไฟล์ NT เรียบร้อยแล้ว',
                'upload_token' => $token,
                'source_filename' => $file->getClientOriginalName(),
                'preview' => $preview,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('NtImportController@preview: '.$e->getMessage() . "\n" . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่สามารถตรวจสอบไฟล์ NT ได้: ' . $e->getMessage(),
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

            $storedPath = 'nt-imports/tmp/'.basename($validated['upload_token']);
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
                    DB::table('nt_records')->where('academic_year', $validated['academic_year'])->delete();
                    DB::table('nt_imports')->where('academic_year', $validated['academic_year'])->delete();
                }

                $importId = DB::table('nt_imports')->insertGetId([
                    'academic_year_id' => $academicYear->id,
                    'academic_year' => $validated['academic_year'],
                    'source_filename' => $validated['source_filename'],
                    'stored_filename' => basename($validated['upload_token']),
                    'schema_version' => 'nt13',
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
                    'nt_school_code' => $row['nt_school_code'],
                    'school_smis' => $row['system_smis'],
                    'school_name' => $row['school_name'],
                    'district' => $row['district'],
                    'school_size' => $row['school_size'],
                    'math_score' => $row['math_score'],
                    'math_percent' => $row['math_percent'],
                    'thai_score' => $row['thai_score'],
                    'thai_percent' => $row['thai_percent'],
                    'total_score' => $row['total_score'],
                    'total_percent' => $row['total_percent'],
                    'math_quality' => $row['math_quality'],
                    'thai_quality' => $row['thai_quality'],
                    'total_quality' => $row['total_quality'],
                    'payload' => json_encode($row['payload'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $matchedRows);

                DB::table('nt_records')->upsert(
                    $payload,
                    ['academic_year', 'nt_school_code'],
                    ['import_id', 'academic_year_id', 'school_id', 'school_smis', 'school_name', 'district', 'school_size', 'math_score', 'math_percent', 'thai_score', 'thai_percent', 'total_score', 'total_percent', 'math_quality', 'thai_quality', 'total_quality', 'payload', 'updated_at']
                );

                return $importId;
            });

            Storage::delete($storedPath);

            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูล NT เรียบร้อยแล้ว ('.$importedRows.' โรงเรียน)',
                'import_id' => $importId,
                'imported_rows' => $importedRows,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('NtImportController@import: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล NT',
            ], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
            ]);

            $recordsQuery = DB::table('nt_records')->where('academic_year', $validated['academic_year']);
            $importsQuery = DB::table('nt_imports')->where('academic_year', $validated['academic_year']);
            $recordsCount = (clone $recordsQuery)->count();
            $importsCount = (clone $importsQuery)->count();

            if ($recordsCount === 0 && $importsCount === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูล NT ของปีที่เลือก',
                ], 404);
            }

            DB::transaction(function () use ($recordsQuery, $importsQuery) {
                $recordsQuery->delete();
                $importsQuery->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูล NT ปีการศึกษา '.$validated['academic_year'].' เรียบร้อยแล้ว',
                'deleted_records' => $recordsCount,
                'deleted_imports' => $importsCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('NtImportController@destroy: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล NT',
            ], 500);
        }
    }
}
