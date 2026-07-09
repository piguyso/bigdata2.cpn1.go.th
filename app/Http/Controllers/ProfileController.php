<?php

namespace App\Http\Controllers;

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
        $teacherProfile = \Illuminate\Support\Facades\DB::table('teacher_profile')
            ->where('user_id', $user->id)
            ->first();

        $teacherData = null;
        if ($teacherProfile) {
            $teacherData = (array) $teacherProfile;
            $teacherData['educations'] = \Illuminate\Support\Facades\DB::table('teacher_educations')
                ->where('record_id', $teacherProfile->id)
                ->get()
                ->toArray();
            $teacherData['subjects'] = \Illuminate\Support\Facades\DB::table('teacher_subjects')
                ->where('record_id', $teacherProfile->id)
                ->get()
                ->toArray();
            $teacherData['cefr'] = \Illuminate\Support\Facades\DB::table('teacher_cefr')
                ->where('record_id', $teacherProfile->id)
                ->get()
                ->toArray();
            $teacherData['hsk'] = \Illuminate\Support\Facades\DB::table('teacher_hsk')
                ->where('record_id', $teacherProfile->id)
                ->get()
                ->toArray();
            $teacherData['awards'] = \Illuminate\Support\Facades\DB::table('teacher_awards')
                ->where('record_id', $teacherProfile->id)
                ->get()
                ->toArray();
        }

        return view('profile.edit', [
            'user'           => $user,
            'teacherProfile' => $teacherData,
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
        try {
            $user = $request->user();
            $teacherProfile = \Illuminate\Support\Facades\DB::table('teacher_profile')
                ->where('user_id', $user->id)
                ->first();

            if (!$teacherProfile) {
                return response()->json([
                    'status' => 'success',
                    'data'   => null,
                    'message' => 'ไม่พบข้อมูลประวัติครู',
                ]);
            }

            $id = $teacherProfile->id;
            $data = (array) $teacherProfile;

            $data['educations'] = \Illuminate\Support\Facades\DB::table('teacher_educations')
                ->where('record_id', $id)->orderBy('id')->get()->toArray();
            $data['subjects'] = \Illuminate\Support\Facades\DB::table('teacher_subjects')
                ->where('record_id', $id)->orderBy('id')->get()->toArray();
            $data['awards'] = \Illuminate\Support\Facades\DB::table('teacher_awards')
                ->where('record_id', $id)->orderBy('id')->get()->toArray();
            $data['cefr'] = \Illuminate\Support\Facades\DB::table('teacher_cefr')
                ->where('record_id', $id)->orderBy('id')->get()->toArray();
            $data['hsk'] = \Illuminate\Support\Facades\DB::table('teacher_hsk')
                ->where('record_id', $id)->orderBy('id')->get()->toArray();

            // Mask personalid
            if (!empty($data['personalid'])) {
                $pid = $data['personalid'];
                $data['personalid_masked'] = substr($pid, 0, 3) . '-xxxx-' . substr($pid, -4);
            } else {
                $data['personalid_masked'] = '';
            }

            // Resolve profile image URL:
            // - New uploads: teacher_images/xxx.jpg → serve from storage
            // - Legacy from e.cpn1.go.th: profile_image_url already has full URL
            if (!empty($data['profile_image_path']) && str_starts_with($data['profile_image_path'], 'teacher_images/')) {
                $data['profile_image_url_resolved'] = asset('storage/' . $data['profile_image_path']);
            } elseif (!empty($data['profile_image_url'])) {
                $data['profile_image_url_resolved'] = $data['profile_image_url'];
            } else {
                $data['profile_image_url_resolved'] = null;
            }

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update teacher profile data via Axios (JSON, no page refresh).
     */
    public function updateTeacherApi(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'school_code'         => ['nullable', 'string', 'max:10'],
                'school_name'         => ['required', 'string', 'max:255'],
                'school_network'      => ['nullable', 'string', 'max:100'],
                'prefix'              => ['required', 'string', 'max:20'],
                'first_name'          => ['required', 'string', 'max:100'],
                'last_name'           => ['required', 'string', 'max:100'],
                'birth_date'          => ['nullable', 'date'],
                'birth_year_be'       => ['nullable', 'integer', 'min:2400', 'max:2600'],
                'age'                 => ['nullable', 'integer', 'min:0', 'max:120'],
                'position'            => ['required', 'string', 'max:60'],
                'academic_rank'       => ['nullable', 'string', 'max:40'],
                'recruitment_subject' => ['nullable', 'string', 'max:150'],
                'appointed_date'      => ['nullable', 'date'],
                'appointed_year_be'   => ['nullable', 'integer', 'min:2400', 'max:2600'],
                'bachelor_major'      => ['nullable', 'string', 'max:150'],
                'master_major'        => ['nullable', 'string', 'max:150'],
                'doctoral_major'      => ['nullable', 'string', 'max:150'],
                'other_workload'      => ['nullable', 'string', 'max:5000'],
                'profile_image_data'  => ['nullable', 'string'],
                // Related tables
                'subjects'            => ['nullable', 'array', 'max:30'],
                'subjects.*.subject_name'  => ['required_with:subjects', 'string', 'max:100'],
                'subjects.*.subject_grade' => ['nullable', 'string', 'max:20'],
                'subjects.*.subject_hours' => ['nullable', 'integer', 'min:0', 'max:999'],
                'educations'          => ['nullable', 'array', 'max:10'],
                'educations.*.edu_level' => ['required_with:educations', 'string', 'max:30'],
                'educations.*.edu_field' => ['nullable', 'string', 'max:255'],
                'educations.*.edu_major' => ['nullable', 'string', 'max:255'],
                'awards'              => ['nullable', 'array', 'max:50'],
                'awards.*.work_name'   => ['nullable', 'string', 'max:255'],
                'awards.*.award_name'  => ['nullable', 'string', 'max:255'],
                'awards.*.award_date'  => ['nullable', 'date'],
                'awards.*.award_date_be' => ['nullable', 'integer'],
                'awards.*.issuer'      => ['nullable', 'string', 'max:255'],
                'cefr'                => ['nullable', 'array', 'max:10'],
                'cefr.*.source'       => ['required_with:cefr', 'string', 'max:100'],
                'cefr.*.cefr_level'   => ['nullable', 'in:A1,A2,B1,B2,C1,C2'],
                'cefr.*.cert_no'      => ['nullable', 'string', 'max:100'],
                'cefr.*.cert_date'    => ['nullable', 'date'],
                'cefr.*.cert_date_be' => ['nullable', 'integer'],
                'cefr.*.issuer'       => ['nullable', 'string', 'max:255'],
                'hsk'                 => ['nullable', 'array', 'max:10'],
                'hsk.*.source'        => ['nullable', 'string', 'max:30'],
                'hsk.*.hsk_level'     => ['nullable', 'string', 'max:50'],
                'hsk.*.cert_no'       => ['nullable', 'string', 'max:100'],
                'hsk.*.cert_date'     => ['nullable', 'date'],
                'hsk.*.cert_date_be'  => ['nullable', 'integer'],
                'hsk.*.issuer'        => ['nullable', 'string', 'max:255'],
            ], [
                'required' => 'กรุณากรอกข้อมูลในช่องนี้',
                'required_with' => 'กรุณากรอกข้อมูลในช่องนี้',
                'integer' => 'กรุณากรอกตัวเลขจำนวนเต็ม',
                'date' => 'กรุณากรอกรูปแบบวันที่ให้ถูกต้อง',
                'max' => 'กรุณากรอกข้อมูลไม่เกิน :max ตัวอักษร',
                'min' => 'กรุณากรอกข้อมูลไม่น้อยกว่า :min',
                'in' => 'ข้อมูลที่เลือกไม่ถูกต้อง',
                'school_name.required' => 'กรุณาระบุชื่อสถานศึกษา (ใช้ช่องพิมพ์ค้นหาเพื่อความถูกต้อง)',
                'prefix.required' => 'กรุณาเลือกคำนำหน้าชื่อ',
                'first_name.required' => 'กรุณากรอกชื่อจริง',
                'last_name.required' => 'กรุณากรอกนามสกุล',
                'position.required' => 'กรุณาเลือกตำแหน่งปฏิบัติหน้าที่',
                'subjects.*.subject_name.required_with' => 'กรุณาระบุชื่อวิชาที่สอน',
                'educations.*.edu_level.required_with' => 'กรุณาระบุระดับการศึกษา',
                'cefr.*.source.required_with' => 'กรุณาระบุแหล่งที่มาของใบรับรอง CEFR',
            ]);

            // Find existing teacher_profile record
            $existing = \Illuminate\Support\Facades\DB::table('teacher_profile')
                ->where('email', $user->email)
                ->first();

            $imageData = $request->input('profile_image_data', '');
            $imagePath = $existing ? $existing->profile_image_path : null;
            $imageName = $existing ? $existing->profile_image_name : null;

            // Handle profile image upload (base64 from cropper)
            if (!empty($imageData) && preg_match('/^data:image\/(\w+);base64,/', $imageData, $imgType)) {
                $rawData  = substr($imageData, strpos($imageData, ',') + 1);
                $imgExt   = strtolower($imgType[1]);
                if (in_array($imgExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $decoded = base64_decode($rawData);
                    if ($decoded !== false) {
                        $newName  = 'teacher_' . time() . '_' . uniqid() . '.' . $imgExt;
                        $newPath  = 'teacher_images/' . $newName;
                        Storage::disk('public')->put($newPath, $decoded);
                        // Delete old image
                        if ($imagePath) {
                            Storage::disk('public')->delete($imagePath);
                        }
                        $imagePath = $newPath;
                        $imageName = $newName;
                    }
                }
            }

            $recordData = [
                'school_code'         => $request->input('school_code') ?? '',
                'school_name'         => $request->input('school_name') ?? '',
                'school_network'      => $request->input('school_network') ?? '',
                'prefix'              => $request->input('prefix') ?? '',
                'first_name'          => $request->input('first_name') ?? '',
                'last_name'           => $request->input('last_name') ?? '',
                'birth_date'          => $request->input('birth_date') ?: null,
                'birth_year_be'       => $request->input('birth_year_be') ?: null,
                'age'                 => $request->input('age') ?: null,
                'position'            => $request->input('position') ?? '',
                'academic_rank'       => $request->input('academic_rank') ?? '',
                'recruitment_subject' => $request->input('recruitment_subject'),
                'appointed_date'      => $request->input('appointed_date') ?: null,
                'appointed_year_be'   => $request->input('appointed_year_be') ?: null,
                'bachelor_major'      => $request->input('bachelor_major'),
                'master_major'        => $request->input('master_major'),
                'doctoral_major'      => $request->input('doctoral_major'),
                'other_workload'      => $request->input('other_workload'),
                'profile_image_path'  => $imagePath,
                'profile_image_name'  => $imageName,
            ];

            if ($existing) {
                \Illuminate\Support\Facades\DB::table('teacher_profile')
                    ->where('id', $existing->id)
                    ->update($recordData);
                $recordId = $existing->id;
            } else {
                $recordData['email']      = $user->email;
                $recordData['created_at'] = now();
                $recordId = \Illuminate\Support\Facades\DB::table('teacher_profile')->insertGetId($recordData);
            }

            // Replace related table data (delete + re-insert)
            \Illuminate\Support\Facades\DB::table('teacher_subjects')->where('record_id', $recordId)->delete();
            foreach ((array) $request->input('subjects', []) as $s) {
                if (empty($s['subject_name'])) continue;
                \Illuminate\Support\Facades\DB::table('teacher_subjects')->insert([
                    'record_id'     => $recordId,
                    'subject_name'  => $s['subject_name'] ?? '',
                    'subject_grade' => $s['subject_grade'] ?? '',
                    'subject_hours' => $s['subject_hours'] ?? 0,
                ]);
            }

            \Illuminate\Support\Facades\DB::table('teacher_educations')->where('record_id', $recordId)->delete();
            foreach ((array) $request->input('educations', []) as $e) {
                if (empty($e['edu_level'])) continue;
                \Illuminate\Support\Facades\DB::table('teacher_educations')->insert([
                    'record_id'      => $recordId,
                    'edu_level'      => $e['edu_level'] ?? '',
                    'field_of_study' => $e['edu_field'] ?? '',
                    'major'          => $e['edu_major'] ?? '',
                ]);
            }

            \Illuminate\Support\Facades\DB::table('teacher_awards')->where('record_id', $recordId)->delete();
            foreach ((array) $request->input('awards', []) as $a) {
                if (empty($a['award_name']) && empty($a['work_name'])) continue;
                \Illuminate\Support\Facades\DB::table('teacher_awards')->insert([
                    'record_id'    => $recordId,
                    'work_name'    => $a['work_name'] ?? '',
                    'award_name'   => $a['award_name'] ?? '',
                    'award_date'   => $a['award_date'] ?: null,
                    'award_date_be'=> $a['award_date_be'] ?: null,
                    'issuer'       => $a['issuer'] ?? '',
                ]);
            }

            \Illuminate\Support\Facades\DB::table('teacher_cefr')->where('record_id', $recordId)->delete();
            foreach ((array) $request->input('cefr', []) as $c) {
                if (empty($c['source'])) continue;
                \Illuminate\Support\Facades\DB::table('teacher_cefr')->insert([
                    'record_id'   => $recordId,
                    'source'      => $c['source'],
                    'cefr_level'  => $c['cefr_level'] ?: null,
                    'cert_no'     => $c['cert_no'] ?? '',
                    'cert_date'   => $c['cert_date'] ?: null,
                    'cert_date_be'=> $c['cert_date_be'] ?: null,
                    'issuer'      => $c['issuer'] ?? '',
                ]);
            }

            \Illuminate\Support\Facades\DB::table('teacher_hsk')->where('record_id', $recordId)->delete();
            foreach ((array) $request->input('hsk', []) as $h) {
                if (empty($h['hsk_level'])) continue;
                \Illuminate\Support\Facades\DB::table('teacher_hsk')->insert([
                    'record_id'   => $recordId,
                    'source'      => $h['source'] ?? '',
                    'hsk_level'   => $h['hsk_level'] ?? '',
                    'cert_no'     => $h['cert_no'] ?? '',
                    'cert_date'   => $h['cert_date'] ?: null,
                    'cert_date_be'=> $h['cert_date_be'] ?: null,
                    'issuer'      => $h['issuer'] ?? '',
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'บันทึกข้อมูลประวัติครูสำเร็จ',
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
