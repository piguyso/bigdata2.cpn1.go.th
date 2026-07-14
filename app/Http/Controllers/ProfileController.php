<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user'           => $user,
            'teacherProfile' => null,
        ]);
    }

    /**
     * Display the user's password change form.
     */
    public function editPassword(Request $request): \Illuminate\View\View
    {
        return view('profile.password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Search schools by name for autocomplete.
     */
    public function searchSchools(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->query('q', '');
        
        if (strlen($q) < 2) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }
        
        $schools = \Illuminate\Support\Facades\DB::table('system_school as ss')
            ->leftJoin('system_group as sg', 'ss.schoolgroup', '=', 'sg.code')
            ->select([
                'ss.smis as school_code',
                'ss.schoolname as school_name',
                'sg.name as school_network'
            ])
            ->where('ss.schoolname', 'LIKE', '%' . $q . '%')
            ->limit(15)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $schools
        ]);
    }

    /**
     * Update the user's profile information via Axios (JSON, no page refresh).
     */
    public function updateApi(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'name'      => ['required', 'string', 'max:255'],
                'email'     => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
                'logo_data' => ['nullable', 'string'],
            ]);

            $user->name = $request->input('name');

            if ($user->email !== $request->input('email')) {
                $user->email = $request->input('email');
                $user->email_verified_at = null;
            }

            // Handle logo upload from Cropper (base64)
            if ($request->has('logo_data') && !empty($request->input('logo_data'))) {
                $data = $request->input('logo_data');

                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $rawData = substr($data, strpos($data, ',') + 1);
                    $type    = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decoded = base64_decode($rawData);
                        if ($decoded !== false) {
                            $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $type;
                            $filePath = 'logos/' . $fileName;

                            Storage::disk('public')->put($filePath, $decoded);

                            if ($user->logo) {
                                Storage::disk('public')->delete($user->logo);
                            }

                            $user->logo = $filePath;
                        }
                    }
                }
            }

            $user->save();

            return response()->json([
                'status'   => 'success',
                'message'  => 'บันทึกข้อมูลส่วนตัวสำเร็จ',
                'logo_url' => $user->logo ? asset('storage/' . $user->logo) : null,
                'name'     => $user->name,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the user's profile information from the standard web form.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->filled('logo_data')) {
            $data = $request->input('logo_data');

            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $rawData = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);

                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $decoded = base64_decode($rawData);

                    if ($decoded !== false) {
                        $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $type;
                        $filePath = 'logos/' . $fileName;

                        Storage::disk('public')->put($filePath, $decoded);

                        if ($user->logo) {
                            Storage::disk('public')->delete($user->logo);
                        }

                        $user->logo = $filePath;
                    }
                }
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's password via Axios (JSON, no page refresh).
     */
    public function updatePasswordApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password'      => ['required', 'current_password'],
                'password'              => ['required', Password::defaults(), 'confirmed'],
                'password_confirmation' => ['required'],
            ]);

            $request->user()->update([
                'password' => Hash::make($request->input('password')),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'อัปเดตรหัสผ่านสำเร็จ',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get teacher profile data for the logged-in user (JSON).
     */
    public function getTeacherData(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'ระบบประวัติครูถูกปิดใช้งานแล้ว',
        ]);
    }

    /**
     * Update teacher profile data via Axios (JSON, no page refresh).
     */
    public function updateTeacherApi(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'ระบบประวัติครูถูกปิดใช้งานแล้ว',
        ], 410);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
