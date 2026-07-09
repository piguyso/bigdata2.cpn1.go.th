<?php

namespace App\Http\Controllers;

use App\Models\PlcGroup;
use App\Models\PlcGroupMember;
use App\Models\PlcStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// Define a local clean helper to prevent Call to undefined function clean()
function clean($val) {
    if (is_null($val)) return null;
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

class PlcController extends Controller
{
    /**
     * Map file paths array containing strings or objects into unified array with URLs and names.
     */
    private function mapStepFiles($filePaths)
    {
        $mapped = [];
        if (is_array($filePaths)) {
            foreach ($filePaths as $item) {
                if (is_array($item)) {
                    $path = $item['path'] ?? '';
                    $name = $item['name'] ?? basename($path);
                } else {
                    $path = $item;
                    $name = basename($path);
                }
                $mapped[] = [
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'name' => $name
                ];
            }
        }
        return $mapped;
    }

    /**
     * Display the main PLC page.
     */
    public function index()
    {
        return view('plc');
    }

    /**
     * Fetch PLC groups and members data.
     */
    public function getData()
    {
        try {
            $user = Auth::user();
            
            // Build query (everyone can view all groups, non-members will be read-only on frontend)
            $query = PlcGroup::with(['creator.teacherProfile', 'members.user.teacherProfile', 'steps']);
            
            $groups = $query->orderBy('created_at', 'desc')->get()->map(function($group) {
                // Map status progress color for each step
                // 0 = pending (gray), 1 = submitted/in progress (orange), 2 = approved/completed (green)
                // Let's return mapped steps
                $mappedSteps = $group->steps->map(function($step) {
                    return array_merge($step->toArray(), [
                        'file_path' => $this->mapStepFiles($step->file_path),
                        'step3_plan_file_paths' => $this->mapStepFiles($step->step3_plan_file_paths),
                        'step6_final_file_paths' => $this->mapStepFiles($step->step6_final_file_paths),
                    ]);
                });

                $schoolName = $group->school_name ?: ($group->creator && $group->creator->teacherProfile 
                    ? $group->creator->teacherProfile->school_name 
                    : 'ไม่ระบุโรงเรียน');

                return array_merge($group->toArray(), [
                    'steps' => $mappedSteps,
                    'school_name' => $schoolName
                ]);
            });

            // Get unique network groups from network_schools and fallback to default list
            $defaultNetworks = [
                'เมืองชุมพร 1', 'เมืองชุมพร 2', 'เมืองชุมพร 3', 'เมืองชุมพร 4', 'เมืองชุมพร 5',
                'ท่าแซะ 1', 'ท่าแซะ 2', 'ท่าแซะ 3', 'ปะทิว 1', 'ปะทิว 2'
            ];
            $dbNetworks = DB::table('network_schools')
                ->distinct()
                ->whereNotNull('school_group')
                ->where('school_group', '<>', '')
                ->pluck('school_group')
                ->toArray();
            $networks = array_values(array_unique(array_merge($dbNetworks, $defaultNetworks)));

             // Get current user's school and network from profile
             $profile = DB::table('teacher_profile')
                 ->where('user_id', $user->id)
                 ->first();

             $network = $profile ? trim($profile->school_network) : null;

             // Get list of all users to choose from when adding members
             $teachersQuery = User::select('id', 'name', 'role')
                 ->with('teacherProfile')
                 ->orderBy('name');

             if ($user->role !== 'admin' && !empty($network)) {
                 $teachersQuery->whereHas('teacherProfile', function($q) use ($network) {
                     $q->where('school_network', $network);
                 });
             }

             $teachers = $teachersQuery->get()->map(function($t) {
                 $schoolName = ($t->teacherProfile && $t->teacherProfile->school_name) ? $t->teacherProfile->school_name : null;
                 return [
                     'id' => $t->id,
                     'name' => $t->name . ($schoolName ? ' (' . $schoolName . ')' : '')
                 ];
             });

             // Get list of all schools
             $schools = DB::table('network_schools')
                 ->select('id', 'name', 'school_group')
                 ->orderBy('name')
                 ->get()
                 ->map(function($sch) {
                     return [
                         'id' => $sch->id,
                         'name' => trim($sch->name),
                         'school_group' => trim($sch->school_group)
                     ];
                 });
             
             $currentUserData = [
                 'id' => $user->id,
                 'name' => $user->name,
                 'role' => $user->role,
                 'school_name' => $profile ? trim($profile->school_name) : null,
                 'school_group' => $profile ? trim($profile->school_network) : null,
             ];

             $departments = [
                 'คณิตศาสตร์',
                 'วิทยาศาสตร์และเทคโนโลยี',
                 'วิทยาการคำนวณ',
                 'ภาษาไทย',
                 'ภาษาต่างประเทศ',
                 'สังคมศึกษา ศาสนา และวัฒนธรรม',
                 'สุขศึกษาและพลศึกษา',
                 'ศิลปะ',
                 'การงานอาชีพ',
                 'กิจกรรมพัฒนาผู้เรียน'
             ];

             return response()->json([
                 'status' => 'success',
                 'data' => [
                     'groups' => $groups,
                     'teachers' => $teachers,
                     'networks' => $networks,
                     'schools' => $schools,
                     'currentUser' => $currentUserData,
                     'departments' => $departments
                 ]
             ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save (Create or Update) PLC group.
     */
    public function storeGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer|exists:plc_groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester' => 'required|integer|in:1,2',
            'academic_year' => 'required|string|max:10',
            'department' => 'required|string|max:150',
            'school_group' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|integer|exists:users,id',
            'members.*.role' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ข้อมูลไม่ถูกต้อง: ' . implode(', ', $validator->errors()->all()),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $groupId = $request->input('id');
            
            if ($groupId) {
                // Update
                $group = PlcGroup::findOrFail($groupId);
                
                // Auth check: only creator or admin can update group settings
                if ($group->creator_user_id != $user->id && $user->role !== 'admin') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'คุณไม่มีสิทธิ์แก้ไขกลุ่ม PLC นี้'
                    ], 403);
                }

                $group->update([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'semester' => $request->input('semester'),
                    'academic_year' => $request->input('academic_year'),
                    'department' => $request->input('department'),
                    'school_group' => $request->input('school_group'),
                    'school_name' => $request->input('school_name'),
                ]);
            } else {
                // Create
                $group = PlcGroup::create([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'semester' => $request->input('semester'),
                    'academic_year' => $request->input('academic_year'),
                    'department' => $request->input('department'),
                    'school_group' => $request->input('school_group'),
                    'school_name' => $request->input('school_name'),
                    'creator_user_id' => $user->id,
                ]);

                // Auto-generate 6 PLC Steps
                $stepNames = [
                    1 => 'Step 1: Plan (ค้นหาปัญหาและตั้งเป้าหมาย)',
                    2 => 'Step 2: Design (ออกแบบนวัตกรรมและร่างแผนฯ)',
                    3 => 'Step 3: Develop (วิพากษ์และปรับปรุงแผนฯ)',
                    4 => 'Step 4: Do & See (เปิดห้องเรียนและสังเกตการณ์)',
                    5 => 'Step 5: Reflect (สะท้อนผลและประเมิน KPI)',
                    6 => 'Step 6: Publish (สรุปองค์ความรู้และเผยแพร่ Best Practice)',
                ];

                foreach ($stepNames as $seq => $name) {
                    PlcStep::create([
                        'plc_group_id' => $group->id,
                        'step_name' => $name,
                        'sequence' => $seq,
                        'status' => 0,
                    ]);
                }
            }

            // Sync Members
            // Always keep creator as 'ครูต้นแบบ' (Model Teacher) if not explicitly set differently, but let's sync all sent members
            PlcGroupMember::where('plc_group_id', $group->id)->delete();
            
            $membersData = $request->input('members', []);
            
            // Add creator if not in list
            $creatorInList = false;
            foreach ($membersData as $m) {
                if ($m['user_id'] == $group->creator_user_id) {
                    $creatorInList = true;
                    break;
                }
            }
            
            if (!$creatorInList && !$groupId) {
                // For new groups, make sure the creator is added as ครูต้นแบบ
                PlcGroupMember::create([
                    'plc_group_id' => $group->id,
                    'user_id' => $group->creator_user_id,
                    'role' => 'ครูต้นแบบ',
                ]);
            }

            foreach ($membersData as $member) {
                PlcGroupMember::create([
                    'plc_group_id' => $group->id,
                    'user_id' => $member['user_id'],
                    'role' => $member['role'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกข้อมูลกลุ่ม PLC สำเร็จ',
                'data' => $group->load(['creator.teacherProfile', 'members.user.teacherProfile', 'steps'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete PLC group and its files.
     */
    public function destroyGroup($id)
    {
        try {
            $group = PlcGroup::findOrFail($id);
            $user = Auth::user();

            // Auth check: only creator or admin can delete
            if ($group->creator_user_id != $user->id && $user->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่มีสิทธิ์ลบกลุ่ม PLC นี้'
                ], 403);
            }

            DB::beginTransaction();

            // Delete folder on disk
            $folderPath = "uploads/plc/{$group->id}";
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->deleteDirectory($folderPath);
            }

            $group->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบกลุ่ม PLC และข้อมูลที่เกี่ยวข้องสำเร็จ'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบกลุ่ม: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save Step fields (non-file updates).
     */
    public function saveStep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'step_id' => 'required|integer|exists:plc_steps,id',
            'status' => 'nullable|integer|in:0,1,2,3',
            // Step 1 fields
            'step1_problem_statement' => 'nullable|string',
            'step1_root_cause' => 'nullable|string',
            'step1_goal_kpi' => 'nullable|string',
            'step1_timeline_step2' => 'nullable|date',
            'step1_timeline_step3' => 'nullable|date',
            'step1_timeline_step4' => 'nullable|date',
            'step1_timeline_step5' => 'nullable|date',
            'step1_timeline_step6' => 'nullable|date',
            // Step 2 fields
            'step2_unit_name' => 'nullable|string',
            'step2_grade_subject' => 'nullable|string',
            'step2_learning_objectives' => 'nullable|string',
            'step2_innovation' => 'nullable|string',
            // Step 3 fields
            'step3_change_log' => 'nullable|string',
            'step3_ready_status' => 'nullable|integer|in:0,1',
            // Step 4 fields
            'step4_class_date' => 'nullable|date_format:Y-m-d\TH:i,Y-m-d H:i:s,Y-m-d H:i',
            'step4_period' => 'nullable|string|max:100',
            'step4_room' => 'nullable|string|max:100',
            // Step 5 fields
            'step5_self_reflection' => 'nullable|string',
            'step5_total_students' => 'nullable|integer|min:0',
            'step5_passed_students' => 'nullable|integer|min:0',
            'step5_qualitative_result' => 'nullable|string',
            // Step 6 fields
            'step6_best_practice' => 'nullable|string',
            'step6_visibility' => 'nullable|string|in:group,school,public',
            'admin_comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ข้อมูลฟอร์มไม่ถูกต้อง: ' . implode(', ', $validator->errors()->all()),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $step = PlcStep::with('group')->findOrFail($request->input('step_id'));
            $user = Auth::user();

            // Access control check: user must be admin or member of the group
            $isMember = PlcGroupMember::where('plc_group_id', $step->plc_group_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isMember && $user->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่ใช่สมาชิกของกลุ่ม PLC นี้'
                ], 403);
            }

            // Only Admin can update status to Approved (2) or Needs Revision (3), and comment
            // Members can submit (1) or drafts (0)
            $updateData = [];

            if ($user->role === 'admin') {
                if ($request->has('status')) {
                    $updateData['status'] = $request->input('status');
                    if (in_array($request->input('status'), [2, 3])) {
                        $updateData['reviewer_user_id'] = $user->id;
                        $updateData['reviewed_at'] = now();
                    }
                }
                if ($request->has('admin_comment')) {
                    $updateData['admin_comment'] = $request->input('admin_comment');
                }
            } else {
                // If it's a member saving
                // Only creator (Model Teacher) or someone with Role == 'ครูต้นแบบ' can update step fields
                $memberRole = PlcGroupMember::where('plc_group_id', $step->plc_group_id)
                    ->where('user_id', $user->id)
                    ->value('role');

                if ($memberRole !== 'ครูต้นแบบ' && $step->group->creator_user_id != $user->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'เฉพาะครูต้นแบบเท่านั้นที่สามารถบันทึกข้อมูลหลักของบทเรียนได้'
                    ], 403);
                }

                if ($request->has('status')) {
                    $status = $request->input('status');
                    if (in_array($status, [0, 2])) {
                        $updateData['status'] = $status;
                        if ($status == 2) {
                            $updateData['submitted_by'] = $user->id;
                            $updateData['submitted_at'] = now();
                            $updateData['reviewer_user_id'] = $user->id;
                            $updateData['reviewed_at'] = now();
                        }
                    } else {
                        $updateData['status'] = 0; // Default to Draft
                    }
                }
            }

            // Map and sanitize standard inputs based on sequence
            if ($step->sequence == 1) {
                $fields = [
                    'step1_problem_statement', 'step1_root_cause', 'step1_goal_kpi',
                    'step1_timeline_step2', 'step1_timeline_step3', 'step1_timeline_step4',
                    'step1_timeline_step5', 'step1_timeline_step6'
                ];
            } elseif ($step->sequence == 2) {
                $fields = ['step2_unit_name', 'step2_grade_subject', 'step2_learning_objectives', 'step2_innovation'];
            } elseif ($step->sequence == 3) {
                $fields = ['step3_change_log', 'step3_ready_status'];
            } elseif ($step->sequence == 4) {
                $fields = ['step4_class_date', 'step4_period', 'step4_room'];
            } elseif ($step->sequence == 5) {
                $fields = ['step5_self_reflection', 'step5_total_students', 'step5_passed_students', 'step5_qualitative_result'];
            } elseif ($step->sequence == 6) {
                $fields = ['step6_best_practice', 'step6_visibility'];
            } else {
                $fields = [];
            }

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $val = $request->input($field);
                    // Sanitize Rich Text for Step 6 best practice or descriptions
                    if ($field === 'step6_best_practice') {
                        $val = clean($val); // Clean helper (standard HTMLPurifier in Laravel)
                    }
                    $updateData[$field] = $val;
                }
            }

            $step->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกข้อมูลขั้นตอนสำเร็จ',
                'data' => $step
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload files for steps.
     */
    public function uploadStepFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'step_id' => 'required|integer|exists:plc_steps,id',
            'files' => 'required|array',
            'files.*' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip',
            'file_field' => 'required|string|in:file_path,step3_plan_file_paths,step6_final_file_paths'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง: ' . implode(', ', $validator->errors()->all()),
            ], 422);
        }

        try {
            $step = PlcStep::findOrFail($request->input('step_id'));
            $user = Auth::user();
            $field = $request->input('file_field');

            // Access check
            $group = PlcGroup::findOrFail($step->plc_group_id);
            $isCreator = $group->creator_user_id == $user->id;
            $isMember = PlcGroupMember::where('plc_group_id', $step->plc_group_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isMember && !$isCreator && $user->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่มีสิทธิ์อัปโหลดไฟล์สำหรับกลุ่มนี้'
                ], 403);
            }

            $groupId = $step->plc_group_id;
            $sequence = $step->sequence;

            // Get existing paths array
            $currentPaths = $step->getAttribute($field) ?: [];
            if (!is_array($currentPaths)) {
                $currentPaths = [];
            }

            foreach ($request->file('files') as $file) {
                // File name: sanitize name and append timestamp hash to avoid duplicates and language errors
                $originalName = $file->getClientOriginalName();
                $cleanName = preg_replace('/[^A-Za-z0-9\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $extension = $file->getClientOriginalExtension();
                $fileName = $cleanName . '_' . time() . '_' . uniqid() . '.' . $extension;

                // Save inside public disk: uploads/plc/{groupId}/step{sequence}/
                $dirPath = "uploads/plc/{$groupId}/step{$sequence}";
                $storedPath = $file->storeAs($dirPath, $fileName, 'public');

                $currentPaths[] = [
                    'path' => $storedPath,
                    'name' => $originalName
                ];
            }

            $step->update([
                $field => $currentPaths
            ]);

            // Formulate response array using helper
            $mappedFiles = $this->mapStepFiles($currentPaths);

            return response()->json([
                'status' => 'success',
                'message' => 'อัปโหลดไฟล์สำเร็จ',
                'data' => $mappedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete specific file from step.
     */
    public function deleteStepFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'step_id' => 'required|integer|exists:plc_steps,id',
            'file_path' => 'required|string',
            'file_field' => 'required|string|in:file_path,step3_plan_file_paths,step6_final_file_paths'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ข้อมูลไม่ถูกต้อง: ' . implode(', ', $validator->errors()->all()),
            ], 422);
        }

        try {
            $step = PlcStep::findOrFail($request->input('step_id'));
            $user = Auth::user();
            $targetPath = $request->input('file_path');
            $field = $request->input('file_field');

            // Access check
            $group = PlcGroup::findOrFail($step->plc_group_id);
            $isCreator = $group->creator_user_id == $user->id;
            $isMember = PlcGroupMember::where('plc_group_id', $step->plc_group_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isMember && !$isCreator && $user->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่มีสิทธิ์ลบไฟล์สำหรับกลุ่มนี้'
                ], 403);
            }

            // Verify file is indeed owned by the step (can be string or array/object)
            $currentPaths = $step->getAttribute($field) ?: [];
            $found = false;
            foreach ($currentPaths as $item) {
                $path = is_array($item) ? ($item['path'] ?? '') : $item;
                if ($path === $targetPath) {
                    $found = true;
                    break;
                }
            }

            if (!is_array($currentPaths) || !$found) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่พบไฟล์นี้ในบันทึกขั้นตอน'
                ], 404);
            }

            // Delete from disk securely (confirm it is inside public/uploads/plc)
            if (str_starts_with($targetPath, 'uploads/plc/') && Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->delete($targetPath);
            }

            // Remove from array and update
            $newPaths = [];
            foreach ($currentPaths as $item) {
                $path = is_array($item) ? ($item['path'] ?? '') : $item;
                if ($path !== $targetPath) {
                    $newPaths[] = $item;
                }
            }

            $step->update([
                $field => $newPaths
            ]);

            // Formulate response array using helper
            $mappedFiles = $this->mapStepFiles($newPaths);

            return response()->json([
                'status' => 'success',
                'message' => 'ลบไฟล์สำเร็จ',
                'data' => $mappedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบไฟล์: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit reflection/comment on steps 2, 3, 4, 5.
     */
    public function saveComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'step_id' => 'required|integer|exists:plc_steps,id',
            'type' => 'required|string|in:step2_idea_sharing,step3_supervision_notes,step4_observations,step5_peer_reflections',
            
            // Comments fields: depending on type, we validate keys
            'comment' => 'nullable|string', // Used by Step 2 & Step 3
            'learning_behavior' => 'nullable|string', // Step 4
            'problems' => 'nullable|string', // Step 4
            'response' => 'nullable|string', // Step 4
            'strengths' => 'nullable|string', // Step 5
            'suggestions' => 'nullable|string', // Step 5
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'ข้อมูลความคิดเห็นไม่ถูกต้อง',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $step = PlcStep::findOrFail($request->input('step_id'));
            $user = Auth::user();
            $type = $request->input('type');

            // Access check: Only members, creators, or admins can comment (read-only for others)
            $group = PlcGroup::findOrFail($step->plc_group_id);
            $isCreator = $group->creator_user_id == $user->id;
            $isAdmin = $user->role === 'admin';
            $isMember = $group->members()->where('user_id', $user->id)->exists();

            if (!$isCreator && !$isAdmin && !$isMember) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'คุณไม่ได้เป็นสมาชิกของกลุ่ม PLC นี้ จึงไม่สามารถบันทึกความคิดเห็นได้'
                ], 403);
            }
            
            // Get current comments list
            $comments = $step->getAttribute($type) ?: [];
            if (!is_array($comments)) {
                $comments = [];
            }

            // Remove previous comment by the same user to avoid duplicates
            $comments = array_values(array_filter($comments, function($item) use ($user) {
                return isset($item['user_id']) && $item['user_id'] != $user->id;
            }));

            // Prepare new comment item based on type
            $commentItem = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'updated_at' => now()->toDateTimeString(),
            ];

            if ($type === 'step2_idea_sharing' || $type === 'step3_supervision_notes') {
                $commentItem['comment'] = clean($request->input('comment'));
            } elseif ($type === 'step4_observations') {
                $commentItem['learning_behavior'] = clean($request->input('learning_behavior'));
                $commentItem['problems'] = clean($request->input('problems'));
                $commentItem['response'] = clean($request->input('response'));
            } elseif ($type === 'step5_peer_reflections') {
                $commentItem['strengths'] = clean($request->input('strengths'));
                $commentItem['suggestions'] = clean($request->input('suggestions'));
            }

            $comments[] = $commentItem;

            $step->update([
                $type => $comments
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกความคิดเห็นสำเร็จ',
                'data' => $comments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกความคิดเห็น: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single teacher details (for member information modal).
     */
    public function getTeacherDetail($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $currentUser = Auth::user();
            $isAdmin = $currentUser && $currentUser->role === 'admin';
            $isSelf = $currentUser && $currentUser->id == $user->id;

            $record = \App\Models\TeacherProfile::with(['school', 'educations', 'subjects', 'awards', 'cefr', 'hsk'])
                ->where('user_id', $user->id)
                ->first();

            if (!$record) {
                // If they don't have a teacher profile yet, return basic user info
                $basicData = [
                    'id' => 0,
                    'prefix' => '',
                    'first_name' => $user->name,
                    'last_name' => '',
                    'position' => 'ครูผู้สอน',
                    'school_name' => 'ไม่ระบุโรงเรียน',
                    'school_network' => 'ไม่ระบุเครือข่าย',
                    'email' => $user->email,
                    'recruitment_subject' => '',
                    'other_workload' => '',
                    'personalid' => '',
                    'birth_date' => '',
                    'age' => '-',
                    'school' => null,
                    'educations' => [],
                    'subjects' => [],
                    'awards' => [],
                    'lms_courses' => [],
                    'alignment' => [
                        'label' => 'ไม่มีข้อมูล',
                        'desc' => 'ไม่พบข้อมูลประวัติการศึกษาและรายวิชาที่สอนในระบบเพื่อวิเคราะห์ความเข้ากันได้'
                    ]
                ];

                if (!$isAdmin && !$isSelf) {
                    unset($basicData['personalid']);
                    unset($basicData['birth_date']);
                    unset($basicData['age']);
                    unset($basicData['educations']);
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $basicData
                ]);
            }

            // Evaluate alignment
            $alignmentService = resolve(\App\Services\SurveyAlignmentService::class);
            $record->alignment = $alignmentService->evaluateAlignment($record->educations, $record->subjects);

            // Fetch LMS courses and matching state
            $lmsCourses = DB::table('lms_enrollments')
                ->join('lms_courses', 'lms_enrollments.course_id', '=', 'lms_courses.id')
                ->where('lms_enrollments.user_id', $user->id)
                ->select('lms_courses.id', 'lms_courses.title')
                ->get()
                ->map(function ($enroll) use ($user) {
                    $total = DB::table('lms_lessons')->where('course_id', $enroll->id)->count();
                    $done = DB::table('lms_lesson_progress')
                        ->where('user_id', $user->id)
                        ->where('course_id', $enroll->id)
                        ->count();
                    return [
                        'title' => $enroll->title,
                        'progress' => $total > 0 ? min((int)round(($done / $total) * 100), 100) : 0
                    ];
                });

            $record->lms_courses = $lmsCourses;

            // Sensitive Data Protection
            if (!$isAdmin && !$isSelf) {
                unset($record->personalid);
                unset($record->birth_date);
                unset($record->birth_year_be);
                unset($record->age);
                unset($record->appointed_date);
                unset($record->appointed_year_be);
                unset($record->educations); // Hide detailed educations list
            }

            return response()->json([
                'status' => 'success',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
