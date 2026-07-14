<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SchoolGroupController extends Controller
{
    public function index()
    {
        return view('admin.school-group');
    }

    public function getData()
    {
        try {
            $groups = DB::table('system_group as groups')
                ->leftJoin('system_school as schools', 'groups.code', '=', 'schools.schoolgroup')
                ->select('groups.id', 'groups.code', 'groups.name', DB::raw('COUNT(schools.id) as schools_count'))
                ->groupBy('groups.id', 'groups.code', 'groups.name')
                ->orderBy('groups.code')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $groups,
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolGroupController@getData: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลเครือข่ายสถานศึกษาได้',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $id = $request->input('id');

        try {
            $validated = $request->validate([
                'id' => ['nullable', 'integer'],
                'code' => [
                    'required',
                    'string',
                    'max:2',
                    Rule::unique('system_group', 'code')->ignore($id),
                ],
                'name' => ['required', 'string', 'max:150'],
            ]);

            if ($id && ! DB::table('system_group')->where('id', $id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลเครือข่ายสถานศึกษาที่ระบุ',
                ], 404);
            }

            $dataToSave = [
                'code' => $validated['code'],
                'name' => $validated['name'],
            ];

            if ($id) {
                DB::table('system_group')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลเครือข่ายสถานศึกษาเรียบร้อยแล้ว';
            } else {
                DB::table('system_group')->insert($dataToSave);
                $message = 'เพิ่มเครือข่ายสถานศึกษาเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('SchoolGroupController@store: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลเครือข่ายสถานศึกษา',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $group = DB::table('system_group')->where('id', $id)->first();

            if (! $group) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลเครือข่ายสถานศึกษา',
                ], 404);
            }

            $schoolsCount = DB::table('system_school')->where('schoolgroup', $group->code)->count();
            if ($schoolsCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่สามารถลบได้ เนื่องจากยังมีโรงเรียนอยู่ในเครือข่ายนี้',
                ], 422);
            }

            DB::table('system_group')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลเครือข่ายสถานศึกษาเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            Log::error('SchoolGroupController@destroy: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลเครือข่ายสถานศึกษา',
            ], 500);
        }
    }
}
