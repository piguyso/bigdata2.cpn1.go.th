<?php

namespace App\Http\Controllers;

use App\Services\StudentDataImportService;
use App\Support\SimpleXlsxExporter;
use App\Support\StudentDataTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Illuminate\Support\Str;

class StudentDataImportController extends Controller
{
    public function __construct(private readonly StudentDataImportService $importService)
    {
    }

    public function index()
    {
        return view('admin.student-data-imports', [
            'dataTypes' => StudentDataTypes::all(),
            'defaultDataType' => StudentDataTypes::defaultKey(),
        ]);
    }

    public function getData(): JsonResponse
    {
        $years = DB::table('academic_years')
            ->orderByDesc('sort_order')
            ->orderByDesc('year')
            ->get();

        $imports = DB::table('student_data_imports as imports')
            ->leftJoin('users', 'imports.created_by', '=', 'users.id')
            ->select('imports.*', 'users.name as created_by_name')
            ->whereIn('imports.data_type', array_keys(StudentDataTypes::all()))
            ->orderByDesc('imports.id')
            ->limit(30)
            ->get();

        $dataSets = DB::table('student_data_records')
            ->select('academic_year', 'term', 'data_type', DB::raw('COUNT(*) as records_count'), DB::raw('SUM(total) as total_count'), DB::raw('MAX(updated_at) as latest_updated_at'))
            ->whereIn('data_type', array_keys(StudentDataTypes::all()))
            ->groupBy('academic_year', 'term', 'data_type')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->get()
            ->map(function ($row) {
                $row->data_label = StudentDataTypes::get($row->data_type)['label'] ?? $row->data_type;

                return $row;
            });

        return response()->json([
            'status' => 'success',
            'years' => $years,
            'active_year' => DB::table('academic_years')->where('is_active', true)->value('year'),
            'data_types' => StudentDataTypes::all(),
            'imports' => $imports,
            'data_sets' => $dataSets,
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'term' => ['required', 'integer', 'min:1', 'max:3'],
                'data_type' => ['required', 'string', Rule::in(StudentDataTypes::keys())],
                'file' => ['required', 'file', 'mimes:csv,txt'],
            ]);

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');
            $token = Str::uuid()->toString() . '.' . $extension;
            $storedPath = $file->storeAs('student-data-imports/tmp', $token);
            $preview = $this->importService->preview(
                Storage::path($storedPath),
                $validated['data_type'],
                $file->getClientOriginalName(),
                $validated['academic_year'],
                (string) $validated['term']
            );

            if (($preview['valid_rows'] ?? 0) === 0) {
                $detail = '';
                if (!empty($preview['invalid_samples'])) {
                    $firstErr = $preview['invalid_samples'][0];
                    $detail = ' (แถวที่ ' . $firstErr['row_number'] . ': ' . $firstErr['reason'] . ')';
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่พบข้อมูลที่นำเข้าได้' . $detail,
                    'preview' => $preview,
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'ตรวจสอบไฟล์เรียบร้อยแล้ว',
                'upload_token' => $token,
                'source_filename' => $file->getClientOriginalName(),
                'preview' => $preview,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('StudentDataImportController@preview: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'โครงสร้างไฟล์ไม่ถูกต้อง หรือไม่สามารถอ่านไฟล์ได้: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year' => ['required', 'digits:4'],
                'term' => ['required', 'integer', 'min:1', 'max:3'],
                'data_type' => ['required', 'string', Rule::in(StudentDataTypes::keys())],
                'upload_token' => ['required', 'string'],
                'source_filename' => ['required', 'string', 'max:255'],
            ]);

            $storedPath = 'student-data-imports/tmp/' . basename($validated['upload_token']);
            if (! Storage::exists($storedPath)) {
                return response()->json(['status' => 'error', 'message' => 'ไม่พบไฟล์ชั่วคราว กรุณาอัปโหลดใหม่'], 422);
            }

            $result = $this->importService->import(Storage::path($storedPath), $validated['upload_token'], [
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'],
                'data_type' => $validated['data_type'],
                'source_filename' => $validated['source_filename'],
                'created_by' => auth()->id(),
            ]);

            Storage::delete($storedPath);

            return response()->json([
                'status' => 'success',
                'message' => 'นำเข้าข้อมูลเรียบร้อยแล้ว',
                'import_id' => $result['import_id'],
                'imported_rows' => $result['imported_rows'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            Log::error('StudentDataImportController@import: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('StudentDataImportController@import: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล',
            ], 500);
        }
    }

    public function downloadTemplate(string $dataType)
    {
        $definition = StudentDataTypes::get($dataType);
        abort_unless($definition, 404);

        return SimpleXlsxExporter::downloadCsv(
            'student-data-' . $dataType . '-template.csv',
            StudentDataTypes::headers($dataType),
            [StudentDataTypes::templateRow($dataType)]
        );
    }

    public function delete(int $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                // Delete records
                DB::table('student_data_records')->where('import_id', $id)->delete();
                // Delete import log
                DB::table('student_data_imports')->where('id', $id)->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ลบชุดข้อมูลนำเข้าเรียบร้อยแล้ว',
            ]);
        } catch (\Throwable $e) {
            Log::error('StudentDataImportController@delete: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล',
            ], 500);
        }
    }
}
