<?php

namespace App\Http\Controllers;

use App\Services\SchoolmisImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SchoolmisController extends Controller
{
    public function __construct(private readonly SchoolmisImportService $importService)
    {
    }

    public function index()
    {
        return view('admin.schoolmis');
    }

    public function getData(): JsonResponse
    {
        try {
            $years = DB::table('academic_years')
                ->orderByDesc('sort_order')
                ->orderByDesc('year')
                ->get();

            $imports = DB::table('schoolmis_imports as imports')
                ->leftJoin('users', 'imports.created_by', '=', 'users.id')
                ->select('imports.*', 'users.name as created_by_name')
                ->orderByDesc('imports.id')
                ->limit(20)
                ->get();

            $dataSets = DB::table('schoolmis_records')
                ->select(
                    'academic_year',
                    'term',
                    DB::raw('COUNT(*) as records_count'),
                    DB::raw('SUM(student_total) as students_count'),
                    DB::raw('SUM(room_total) as rooms_count'),
                    DB::raw('MAX(updated_at) as latest_updated_at')
                )
                ->groupBy('academic_year', 'term')
                ->orderByDesc('academic_year')
                ->orderByDesc('term')
                ->get();

            return response()->json([
                'status' => 'success',
                'years' => $years,
                'active_year' => DB::table('academic_years')->where('is_active', true)->value('year'),
                'imports' => $imports,
                'data_sets' => $dataSets,
                'record_count' => DB::table('schoolmis_records')->count(),
                'latest_imported_year' => DB::table('schoolmis_records')->orderByDesc('id')->value('academic_year'),
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolmisController@getData: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูล SchoolMIS ได้',
            ], 500);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'term' => ['required', 'integer', 'min:1', 'max:3'],
                'csv' => ['required', 'file', 'mimes:csv,txt'],
            ]);

            $file = $request->file('csv');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');
            $token = Str::uuid()->toString() . '.' . $extension;
            $storedPath = $file->storeAs('schoolmis-imports/tmp', $token);
            $absolutePath = Storage::path($storedPath);

            $preview = $this->importService->preview($absolutePath);
            $warnings = $preview['warnings'];

            if (($preview['valid_rows'] ?? 0) === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่พบแถวข้อมูล SchoolMIS ที่นำเข้าได้',
                    'preview' => $preview,
                ], 422);
            }

            foreach ($preview['detected_year_terms'] as $detected) {
                if ($detected !== $validated['academic_year'] . '-' . $validated['term']) {
                    $warnings[] = 'ปีการศึกษา/รอบที่ตรวจพบในไฟล์ (' . $detected . ') ไม่ตรงกับค่าที่เลือก';
                    break;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'ตรวจสอบไฟล์เรียบร้อยแล้ว',
                'upload_token' => $token,
                'source_filename' => $file->getClientOriginalName(),
                'preview' => array_merge($preview, [
                    'warnings' => array_values(array_unique($warnings)),
                ]),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SchoolmisController@preview: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่สามารถตรวจสอบไฟล์ SchoolMIS ได้',
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'term' => ['required', 'integer', 'min:1', 'max:3'],
                'upload_token' => ['required', 'string'],
                'source_filename' => ['required', 'string', 'max:255'],
                'mode' => ['nullable', 'in:replace'],
            ]);

            $academicYear = DB::table('academic_years')
                ->where('year', $validated['academic_year'])
                ->first();

            if (! $academicYear) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบปีการศึกษาที่เลือกในระบบ',
                ], 422);
            }

            $storedPath = 'schoolmis-imports/tmp/' . basename($validated['upload_token']);
            if (! Storage::exists($storedPath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบไฟล์ชั่วคราว กรุณาอัปโหลดใหม่อีกครั้ง',
                ], 422);
            }

            $parsed = $this->importService->parseForImport(Storage::path($storedPath));
            $mode = $validated['mode'] ?? 'replace';

            if ($parsed['valid_rows'] === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูล SchoolMIS ที่นำเข้าได้ในไฟล์นี้',
                ], 422);
            }

            $schoolMap = DB::table('system_school')
                ->select('id', 'smis')
                ->get()
                ->mapWithKeys(fn ($school) => [preg_replace('/\D+/', '', (string) $school->smis) => $school->id])
                ->all();

            $matchedRows = array_values(array_filter($parsed['rows'], function ($row) use ($schoolMap) {
                return isset($schoolMap[$row['school_smis']]);
            }));

            if (count($matchedRows) === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียนที่จับคู่กับ system_school ได้ จึงยังไม่บันทึกข้อมูลลงฐานข้อมูล',
                ], 422);
            }

            $importedRows = count($matchedRows);

            $importId = DB::transaction(function () use ($academicYear, $validated, $parsed, $mode, $schoolMap, $matchedRows, $importedRows) {
                if ($mode === 'replace') {
                    DB::table('schoolmis_records')
                        ->where('academic_year', $validated['academic_year'])
                        ->where('term', $validated['term'])
                        ->delete();
                }

                $importId = DB::table('schoolmis_imports')->insertGetId([
                    'academic_year_id' => $academicYear->id,
                    'academic_year' => $validated['academic_year'],
                    'term' => $validated['term'],
                    'source_filename' => $validated['source_filename'],
                    'stored_filename' => basename($validated['upload_token']),
                    'schema_version' => implode(',', $parsed['schema_versions']),
                    'total_rows' => $parsed['total_rows'],
                    'valid_rows' => $parsed['valid_rows'],
                    'imported_rows' => $importedRows,
                    'unmatched_rows' => $parsed['unmatched_rows'],
                    'invalid_rows' => $parsed['invalid_rows'],
                    'mode' => $mode,
                    'created_by' => $validated['created_by'] ?? auth()->id(),
                    'warnings' => json_encode($parsed['warnings'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $payload = [];
                foreach ($matchedRows as $row) {
                    $payload[] = [
                        'import_id' => $importId,
                        'academic_year_id' => $academicYear->id,
                        'academic_year' => $validated['academic_year'],
                        'term' => $validated['term'],
                        'school_id' => $schoolMap[$row['school_smis']] ?? null,
                        'school_smis' => $row['school_smis'],
                        'schema_version' => $row['schema_version'],
                        'raw_year_term' => $row['raw_year_term'],
                        'male_total' => $row['male_total'],
                        'female_total' => $row['female_total'],
                        'student_total' => $row['student_total'],
                        'room_total' => $row['room_total'],
                        'metrics' => json_encode($row['metrics'], JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('schoolmis_records')->upsert(
                    $payload,
                    ['academic_year', 'term', 'school_smis'],
                    ['import_id', 'academic_year_id', 'school_id', 'schema_version', 'raw_year_term', 'male_total', 'female_total', 'student_total', 'room_total', 'metrics', 'updated_at']
                );

                return $importId;
            });

            Storage::delete($storedPath);

            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูล SchoolMIS เรียบร้อยแล้ว (' . $importedRows . ' โรงเรียน)',
                'import_id' => $importId,
                'imported_rows' => $importedRows,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SchoolmisController@import: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล SchoolMIS',
            ], 500);
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $schemaLabels = [
            'k1' => 'อ.1',
            'k2' => 'อ.2',
            'k3' => 'อ.3',
            'pre_primary_total' => 'รวมอนุบาล',
            'p1' => 'ป.1',
            'p2' => 'ป.2',
            'p3' => 'ป.3',
            'p4' => 'ป.4',
            'p5' => 'ป.5',
            'p6' => 'ป.6',
            'primary_total' => 'รวมประถม',
            'm1' => 'ม.1',
            'm2' => 'ม.2',
            'm3' => 'ม.3',
            'lower_secondary_total' => 'รวม ม.ต้น',
            'm4' => 'ม.4',
            'm5' => 'ม.5',
            'm6' => 'ม.6',
            'upper_secondary_total' => 'รวม ม.ปลาย',
            'all_total' => 'รวมทั้งหมด',
        ];

        $headers = ['ปีการศึกษา-ภาคเรียน', 'รหัส SMIS'];
        $sampleRow = ['2569-1', '10012001'];

        foreach ($schemaLabels as $label) {
            $headers[] = $label . '_ชาย';
            $headers[] = $label . '_หญิง';
            $headers[] = $label . '_รวม';
            $headers[] = $label . '_ห้อง';

            $sampleRow[] = '10';
            $sampleRow[] = '10';
            $sampleRow[] = '20';
            $sampleRow[] = '1';
        }

        return \App\Support\SimpleXlsxExporter::downloadCsv(
            'schoolmis-template.csv',
            $headers,
            [$sampleRow]
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'term' => ['required', 'integer', 'min:1', 'max:3'],
            ]);

            $recordsQuery = DB::table('schoolmis_records')
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term']);

            $importsQuery = DB::table('schoolmis_imports')
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term']);

            $recordsCount = (clone $recordsQuery)->count();
            $importsCount = (clone $importsQuery)->count();

            if ($recordsCount === 0 && $importsCount === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูล SchoolMIS ของปีและรอบที่เลือก',
                ], 404);
            }

            DB::transaction(function () use ($recordsQuery, $importsQuery) {
                $recordsQuery->delete();
                $importsQuery->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูล SchoolMIS ของปีการศึกษา ' . $validated['academic_year'] . ' รอบ ' . $validated['term'] . ' เรียบร้อยแล้ว',
                'deleted_records' => $recordsCount,
                'deleted_imports' => $importsCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SchoolmisController@destroy: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล SchoolMIS',
            ], 500);
        }
    }

    public function deleteImport(int $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                DB::table('schoolmis_records')->where('import_id', $id)->delete();
                DB::table('schoolmis_imports')->where('id', $id)->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลนำเข้า SchoolMIS เรียบร้อยแล้ว',
            ]);
        } catch (\Throwable $e) {
            Log::error('SchoolmisController@deleteImport: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล',
            ], 500);
        }
    }
}
