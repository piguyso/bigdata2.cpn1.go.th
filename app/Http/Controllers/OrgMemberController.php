<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OrgMemberController extends Controller
{
    /**
     * Show the org members admin page.
     */
    public function index()
    {
        return view('admin.org');
    }

    /**
     * Show the public org members structure page.
     */
    public function publicIndex()
    {
        try {
            $members = DB::table('org_members')
                ->orderBy('level', 'asc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($member) {
                    $member->photo_url = $member->photo ? asset('storage/' . $member->photo) : null;
                    return $member;
                });

            return view('org', compact('members'));
        } catch (\Exception $e) {
            Log::error('OrgMemberController@publicIndex: ' . $e->getMessage());
            return redirect('/');
        }
    }

    /**
     * Get all org members as a JSON list (Admin).
     */
    public function getData()
    {
        try {
            $members = DB::table('org_members')
                ->orderBy('level', 'asc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($member) {
                    $member->photo_url = $member->photo ? asset('storage/' . $member->photo) : null;
                    return $member;
                });

            return response()->json([
                'status' => 'success',
                'data' => $members
            ]);
        } catch (\Exception $e) {
            Log::error('OrgMemberController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลโครงสร้างบุคลากรได้'
            ], 500);
        }
    }

    /**
     * Get org members list for public homepage chart rendering.
     */
    public function getPublicList()
    {
        try {
            $members = DB::table('org_members')
                ->orderBy('level', 'asc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($member) {
                    $member->photo_url = $member->photo ? asset('storage/' . $member->photo) : null;
                    return $member;
                });

            return response()->json([
                'status' => 'success',
                'data' => $members
            ]);
        } catch (\Exception $e) {
            Log::error('OrgMemberController@getPublicList: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการโหลดโครงสร้างบุคลากรศูนย์'
            ], 500);
        }
    }

    /**
     * Create or update org member.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id' => ['nullable', 'integer'],
                'name' => ['required', 'string', 'max:255'],
                'position' => ['required', 'string', 'max:255'],
                'role' => ['required', 'string', 'in:advisor,member'],
                'committee' => ['nullable', 'string', 'in:operations,executive,academic,finance'],
                'role_title' => ['nullable', 'string', 'max:255'],
                'level' => ['required', 'integer', 'min:1'],
                'sort_order' => ['required', 'integer'],
                'photo_data' => ['nullable', 'string'],
                'delete_photo' => ['nullable', 'boolean'],
            ]);

            $id = $request->input('id');
            $photoPath = null;

            if ($id) {
                $currentMember = DB::table('org_members')->where('id', $id)->first();
                if (!$currentMember) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'ไม่พบข้อมูลบุคลากรที่ระบุ'
                    ], 404);
                }
                $photoPath = $currentMember->photo;
            }

            // 1. Process photo deletion
            if ($request->input('delete_photo') === true || $request->input('delete_photo') === 1) {
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                    $photoPath = null;
                }
            }

            // 2. Process photo upload (Base64)
            if ($request->filled('photo_data')) {
                $data = $request->input('photo_data');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decodedData = base64_decode($data);
                        
                        if ($decodedData !== false) {
                            if ($photoPath) {
                                Storage::disk('public')->delete($photoPath);
                            }

                            $fileName = 'member_photo_' . time() . '_' . uniqid() . '.' . $type;
                            $photoPath = 'org_photos/' . $fileName;
                            
                            Storage::disk('public')->put($photoPath, $decodedData);
                        }
                    }
                }
            }

            $dataToSave = [
                'name' => $request->input('name'),
                'position' => $request->input('position'),
                'role' => $request->input('role'),
                'committee' => $request->input('committee', 'operations') ?: 'operations',
                'role_title' => $request->input('role_title'),
                'level' => $request->input('level', 1),
                'sort_order' => $request->input('sort_order'),
                'photo' => $photoPath,
                'updated_at' => now(),
            ];

            if ($id) {
                DB::table('org_members')->where('id', $id)->update($dataToSave);
                $message = 'แก้ไขข้อมูลบุคลากรเรียบร้อยแล้ว';
            } else {
                $dataToSave['created_at'] = now();
                DB::table('org_members')->insert($dataToSave);
                $message = 'เพิ่มบุคลากรในโครงสร้างเรียบร้อยแล้ว';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('OrgMemberController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลบุคลากร'
            ], 500);
        }
    }

    /**
     * Delete org member.
     */
    public function destroy($id)
    {
        try {
            $member = DB::table('org_members')->where('id', $id)->first();
            
            if (!$member) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูลบุคลากร'
                ], 404);
            }

            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }

            DB::table('org_members')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลบุคลากรเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('OrgMemberController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลบุคลากร'
            ], 500);
        }
    }
}
