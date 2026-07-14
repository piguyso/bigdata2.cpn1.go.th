<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index()
    {
        return view('admin.academic-years');
    }

    public function getData(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->queryYears()->get(),
                'active_year' => $this->activeYear(),
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicYearController@getData: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลปีการศึกษาได้',
            ], 500);
        }
    }

    public function getPublicList(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->queryYears()->get(),
                'active_year' => $this->activeYear(),
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicYearController@getPublicList: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลปีการศึกษาได้',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $id = $request->input('id');

        try {
            $validated = $request->validate([
                'id' => ['nullable', 'integer'],
                'year' => [
                    'required',
                    'digits:4',
                    Rule::unique('academic_years', 'year')->ignore($id),
                ],
                'name' => ['nullable', 'string', 'max:100'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
                'is_active' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ]);

            if ($id && ! DB::table('academic_years')->where('id', $id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลปีการศึกษาที่ระบุ',
                ], 404);
            }

            $isActive = (bool) ($validated['is_active'] ?? false);

            if ($isActive) {
                DB::table('academic_years')->update(['is_active' => false]);
            }

            $data = [
                'year' => $validated['year'],
                'name' => $validated['name'] ?: 'ปีการศึกษา ' . $validated['year'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'is_active' => $isActive,
                'sort_order' => $validated['sort_order'] ?? (int) $validated['year'],
                'updated_at' => now(),
            ];

            if ($id) {
                DB::table('academic_years')->where('id', $id)->update($data);
                $message = 'แก้ไขปีการศึกษาเรียบร้อยแล้ว';
            } else {
                $data['created_at'] = now();
                DB::table('academic_years')->insert($data);
                $message = 'เพิ่มปีการศึกษาเรียบร้อยแล้ว';
            }

            if (! DB::table('academic_years')->where('is_active', true)->exists()) {
                DB::table('academic_years')
                    ->orderByDesc('sort_order')
                    ->orderByDesc('year')
                    ->limit(1)
                    ->update(['is_active' => true]);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('AcademicYearController@store: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกปีการศึกษา',
            ], 500);
        }
    }

    public function setActive($id): JsonResponse
    {
        try {
            $year = DB::table('academic_years')->where('id', $id)->first();

            if (! $year) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลปีการศึกษา',
                ], 404);
            }

            DB::transaction(function () use ($id) {
                DB::table('academic_years')->update(['is_active' => false]);
                DB::table('academic_years')->where('id', $id)->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ตั้งปีการศึกษาปัจจุบันเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicYearController@setActive: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการตั้งปีการศึกษาปัจจุบัน',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $year = DB::table('academic_years')->where('id', $id)->first();

            if (! $year) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลปีการศึกษา',
                ], 404);
            }

            if ($year->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่สามารถลบปีการศึกษาปัจจุบันได้',
                ], 422);
            }

            if (DB::table('courses')->where('academic_year', $year->year)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลหลักสูตรอยู่ในปีการศึกษานี้',
                ], 422);
            }

            DB::table('academic_years')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบปีการศึกษาเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicYearController@destroy: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบปีการศึกษา',
            ], 500);
        }
    }

    private function queryYears()
    {
        return DB::table('academic_years')
            ->orderByDesc('sort_order')
            ->orderByDesc('year')
            ->orderByDesc('id');
    }

    private function activeYear(): ?string
    {
        return DB::table('academic_years')
            ->where('is_active', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->value('year');
    }
}
