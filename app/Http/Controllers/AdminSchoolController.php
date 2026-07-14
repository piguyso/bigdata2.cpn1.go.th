<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    public function index()
    {
        return view('admin.schools');
    }

    public function getData()
    {
        try {
            $schools = DB::table('system_school as schools')
                ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
                ->select('schools.*', 'groups.name as schoolgroup_name')
                ->orderBy('schools.schoolgroup')
                ->orderBy('schools.schoolname')
                ->get();

            $groups = DB::table('system_group')
                ->orderBy('code')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $schools,
                'groups' => $groups,
            ]);
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@getData: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลโรงเรียนได้',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $id = $request->input('id');
        $existingSchool = $id ? DB::table('system_school')->where('id', $id)->first() : null;

        try {
            $validated = $request->validate([
                'id' => ['nullable', 'integer'],
                'smis' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('system_school', 'smis')->ignore($id),
                ],
                'percode' => ['nullable', 'string', 'max:20'],
                'ministry' => ['nullable', 'string', 'max:20'],
                'schoolname' => ['required', 'string', 'max:1500'],
                'schoolname_eng' => ['nullable', 'string', 'max:999'],
                'schoolgroup' => ['required', 'string', Rule::exists('system_group', 'code')],
                'muti' => ['nullable', 'string', 'max:10'],
                'road' => ['nullable', 'string', 'max:100'],
                'muban' => ['nullable', 'string', 'max:100'],
                'tambon' => ['nullable', 'string', 'max:100'],
                'amper' => ['nullable', 'string', 'max:100'],
                'province' => ['nullable', 'string', 'max:100'],
                'postcode' => ['nullable', 'string', 'max:100'],
                'lat' => ['nullable', 'string', 'max:80'],
                'lng' => ['nullable', 'string', 'max:80'],
                'length_km' => ['nullable', 'string', 'max:10'],
                'maplink' => ['nullable', 'string', 'max:255'],
                'tel' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:150'],
                'website' => ['nullable', 'string', 'max:150'],
                'statusID' => ['nullable', 'string', 'max:1'],
                'statusDetail' => ['nullable', 'string', 'max:20'],
            ]);

            if ($id && ! DB::table('system_school')->where('id', $id)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียนที่ระบุ',
                ], 404);
            }

            $dataToSave = collect([
                'smis', 'percode', 'ministry', 'schoolname', 'schoolname_eng', 'schoolgroup',
                'muti', 'road', 'muban', 'tambon', 'amper', 'province', 'postcode',
                'lat', 'lng', 'length_km', 'maplink', 'tel', 'email', 'website',
                'statusID', 'statusDetail',
            ])->mapWithKeys(function ($field) use ($validated) {
                return [$field => $validated[$field] ?? ''];
            })->all();

            $dataToSave['province'] = $dataToSave['province'] ?: 'ชุมพร';
            $dataToSave['statusID'] = $dataToSave['statusID'] ?: '1';
            $dataToSave['statusDetail'] = $dataToSave['statusDetail'] ?: 'เปิด';

            if ($existingSchool) {
                $latChanged = trim((string) $existingSchool->lat) !== trim((string) $dataToSave['lat']);
                $lngChanged = trim((string) $existingSchool->lng) !== trim((string) $dataToSave['lng']);

                if ($latChanged || $lngChanged) {
                    $dataToSave['length_km'] = '';
                }
            }

            if ($id) {
                DB::table('system_school')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลโรงเรียนเรียบร้อยแล้ว';
            } else {
                DB::table('system_school')->insert($dataToSave);
                $message = 'เพิ่มข้อมูลโรงเรียนเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@store: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลโรงเรียน',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $school = DB::table('system_school')->where('id', $id)->first();

            if (! $school) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียน',
                ], 404);
            }

            DB::table('system_school')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลโรงเรียนเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            Log::error('AdminSchoolController@destroy: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลโรงเรียน',
            ], 500);
        }
    }
}
