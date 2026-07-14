<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display the user management page.
     */
    public function index()
    {
        return view('admin.users');
    }

    /**
     * Get all users in JSON format.
     */
    public function getData()
    {
        try {
            $users = User::orderBy('id', 'desc')->get()->map(function ($user) {
                $user->logo_url = $user->logo ? asset('storage/' . $user->logo) : null;
                $user->profile_image_url = null;
                return $user;
            });

            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('UserController@getData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถโหลดข้อมูลผู้ใช้งานได้'
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => ['required', Rule::in(['admin', 'teacher', 'user'])],
                'password' => 'required|string|min:8',
            ], [
                'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
                'email.required' => 'กรุณากรอกอีเมล',
                'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
                'email.unique' => 'อีเมลนี้ถูกใช้งานในระบบแล้ว',
                'role.required' => 'กรุณาเลือกสิทธิ์การใช้งาน',
                'role.in' => 'บทบาทไม่ถูกต้อง',
                'password.required' => 'กรุณากรอกรหัสผ่าน',
                'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'เพิ่มข้อมูลผู้ใช้งานเรียบร้อยแล้ว',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('UserController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลผู้ใช้งาน'
            ], 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'role' => ['required', Rule::in(['admin', 'teacher', 'user'])],
                'password' => 'nullable|string|min:8',
            ], [
                'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
                'email.required' => 'กรุณากรอกอีเมล',
                'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
                'email.unique' => 'อีเมลนี้ถูกใช้งานในระบบแล้ว',
                'role.required' => 'กรุณาเลือกสิทธิ์การใช้งาน',
                'role.in' => 'บทบาทไม่ถูกต้อง',
                'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            ]);

            // ป้องกันไม่ให้ Admin ปลดล็อกบทบาทตัวเองออกจาก Admin (ป้องกัน Lockout)
            if ($user->id === Auth::id() && $request->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่สามารถเปลี่ยนบทบาทของตัวเองจากแอดมินเป็นบทบาทอื่นได้'
                ], 400);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'แก้ไขข้อมูลผู้ใช้งานเรียบร้อยแล้ว',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('UserController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูลผู้ใช้งาน'
            ], 500);
        }
    }

    /**
     * Delete the specified user.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // ป้องกันการลบตัวเอง
            if ($user->id === Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่สามารถลบบัญชีผู้ใช้งานของตัวคุณเองได้'
                ], 400);
            }

            // ป้องกันการลบแอดมินคนสุดท้าย (Last Admin lockout prevention)
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายของระบบได้'
                    ], 400);
                }
            }

            // ลบรูปโลโก้ / โปรไฟล์เดิมหากจัดเก็บไว้
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลผู้ใช้งานเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('UserController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลผู้ใช้งาน'
            ], 500);
        }
    }
}
