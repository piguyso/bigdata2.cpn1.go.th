<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NetworkSchoolController extends Controller
{
    /**
     * Show the admin school management page.
     */
    public function index()
    {
        return view('admin.schools');
    }

    /**
     * Get all network schools as a JSON list.
     */
    public function getData()
    {
        try {
            $schools = DB::table('network_schools')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($school) {
                    $school->logo_url = $school->logo ? asset('storage/' . $school->logo) : null;
                    return $school;
                });
            
            return response()->json([
                'status' => 'success',
                'data' => $schools
            ]);
        } catch (\Exception $e) {
            Log::error('NetworkSchoolController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลโรงเรียนเครือข่ายได้'
            ], 500);
        }
    }

    /**
     * Get all network schools for public rendering.
     */
    public function getPublicList()
    {
        try {
            $schools = DB::table('network_schools')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($school) {
                    $school->logo_url = $school->logo ? asset('storage/' . $school->logo) : null;
                    return $school;
                });

            return response()->json([
                'status' => 'success',
                'data' => $schools
            ]);
        } catch (\Exception $e) {
            Log::error('NetworkSchoolController@getPublicList: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการโหลดข้อมูลโรงเรียนเครือข่าย'
            ], 500);
        }
    }

    /**
     * Create or update network school.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id' => ['nullable', 'integer'],
                'name' => ['required', 'string', 'max:255'],
                'district' => ['required', 'string', 'in:อำเภอเมืองชุมพร,อำเภอหลังสวน,อำเภอละแม,อำเภอพะโต๊ะ,อำเภอสวี,อำเภอทุ่งตะโก,อำเภอท่าแซะ,อำเภอปะทิว'],
                'address' => ['nullable', 'string', 'max:1000'],
                'website' => ['nullable', 'url', 'max:255'],
                'logo_data' => ['nullable', 'string'],
                'delete_logo' => ['nullable', 'boolean'],
            ]);

            $id = $request->input('id');
            $logoPath = null;

            if ($id) {
                $currentSchool = DB::table('network_schools')->where('id', $id)->first();
                if (!$currentSchool) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'ไม่พบข้อมูลโรงเรียนเครือข่ายที่ระบุ'
                    ], 404);
                }
                $logoPath = $currentSchool->logo;
            }

            if ($request->input('delete_logo') === true || $request->input('delete_logo') === 1) {
                if ($logoPath) {
                    Storage::disk('public')->delete($logoPath);
                    $logoPath = null;
                }
            }

            if ($request->filled('logo_data')) {
                $data = $request->input('logo_data');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decodedData = base64_decode($data);
                        
                        if ($decodedData !== false) {
                            if ($logoPath) {
                                Storage::disk('public')->delete($logoPath);
                            }

                            $fileName = 'school_logo_' . time() . '_' . uniqid() . '.' . $type;
                            $logoPath = 'school_logos/' . $fileName;
                            
                            Storage::disk('public')->put($logoPath, $decodedData);
                        }
                    }
                }
            }

            $dataToSave = [
                'name' => $request->input('name'),
                'district' => $request->input('district'),
                'address' => $request->input('address'),
                'website' => $request->input('website'),
                'logo' => $logoPath,
                'updated_at' => now(),
            ];

            if ($id) {
                DB::table('network_schools')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลโรงเรียนเครือข่ายเรียบร้อยแล้ว';
            } else {
                $dataToSave['created_at'] = now();
                DB::table('network_schools')->insert($dataToSave);
                $message = 'เพิ่มโรงเรียนเครือข่ายเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('NetworkSchoolController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลโรงเรียนเครือข่าย'
            ], 500);
        }
    }

    /**
     * Delete network school.
     */
    public function destroy($id)
    {
        try {
            $school = DB::table('network_schools')->where('id', $id)->first();
            
            if (!$school) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลโรงเรียนเครือข่าย'
                ], 404);
            }

            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }

            DB::table('network_schools')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบโรงเรียนเครือข่ายเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('NetworkSchoolController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบโรงเรียนเครือข่าย'
            ], 500);
        }
    }
}
