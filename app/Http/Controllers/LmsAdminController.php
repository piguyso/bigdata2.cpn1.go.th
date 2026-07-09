<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LmsAdminController extends Controller
{
    // --- Courses Management ---
    public function coursesIndex()
    {
        return view('admin.lms.courses');
    }

    public function coursesData()
    {
        $courses = DB::table('lms_courses')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($c) {
                if ($c->cover_url) {
                    $coverUrl = $c->cover_url;
                    if (str_starts_with($coverUrl, '/1/uploads/')) {
                        $coverUrl = 'storage/' . substr($coverUrl, 3);
                    } elseif (str_starts_with($coverUrl, '1/uploads/')) {
                        $coverUrl = 'storage/' . substr($coverUrl, 2);
                    }
                    $c->cover_url = asset($coverUrl);
                } else {
                    $c->cover_url = null;
                }

                if ($c->certificate_bg_url) {
                    $certUrl = $c->certificate_bg_url;
                    if (str_starts_with($certUrl, '/1/uploads/')) {
                        $certUrl = 'storage/' . substr($certUrl, 3);
                    } elseif (str_starts_with($certUrl, '1/uploads/')) {
                        $certUrl = 'storage/' . substr($certUrl, 2);
                    }
                    $c->certificate_bg_url = asset($certUrl);
                } else {
                    $c->certificate_bg_url = null;
                }

                return $c;
            });

        return response()->json([
            'status' => 'success',
            'data' => $courses
        ]);
    }

    public function courseStore(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pass_threshold' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,published'],
            'category' => ['nullable', 'string', 'max:100'],
            'level' => ['required', 'in:ทั่วไป,ต้น,กลาง,สูง'],
            'cover_file' => ['nullable', 'image', 'max:2048'],
            'cover_image_data' => ['nullable', 'string'],
            'delete_cover_image' => ['nullable'],
            'certificate_bg_file' => ['nullable', 'image', 'max:5120'],
        ]);

        $id = $request->input('id');
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description') ?: '',
            'pass_threshold' => $request->input('pass_threshold'),
            'status' => $request->input('status'),
            'category' => $request->input('category') ?: 'ทั่วไป',
            'level' => $request->input('level'),
            'updated_at' => now(),
        ];

        // จัดการลบรูปภาพปกเดิม
        if ($request->input('delete_cover_image') == 1) {
            if ($id) {
                $old = DB::table('lms_courses')->where('id', $id)->value('cover_url');
                if ($old && str_starts_with($old, 'storage/')) {
                    $oldPath = substr($old, 8); // ลบ 'storage/' ออก
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $data['cover_url'] = '';
        }

        // จัดการอัปโหลดรูปภาพปกจาก Cropper (Base64)
        if ($request->filled('cover_image_data')) {
            $base64 = $request->input('cover_image_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64Data = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        // ลบรูปภาพปกเดิม
                        if ($id && !isset($data['cover_url'])) {
                            $old = DB::table('lms_courses')->where('id', $id)->value('cover_url');
                            if ($old && str_starts_with($old, 'storage/')) {
                                $oldPath = substr($old, 8);
                                Storage::disk('public')->delete($oldPath);
                            }
                        }

                        $fileName = 'course_cover_' . time() . '_' . uniqid() . '.' . $type;
                        $path = 'lms/covers/' . $fileName;
                        Storage::disk('public')->put($path, $decoded);
                        $data['cover_url'] = 'storage/' . $path;
                    }
                }
            }
        } elseif ($request->hasFile('cover_file')) {
            // จัดการอัปโหลดรูปภาพปกแบบไฟล์ปกติ (เผื่อใช้)
            $file = $request->file('cover_file');
            if ($file->isValid() && $file->getRealPath()) {
                $path = $file->store('lms/covers', 'public');
                $data['cover_url'] = 'storage/' . $path;
            }
        }

        if ($request->hasFile('certificate_bg_file')) {
            $file = $request->file('certificate_bg_file');
            if ($file->isValid() && $file->getRealPath()) {
                $path = $file->store('lms/certificates', 'public');
                $data['certificate_bg_url'] = 'storage/' . $path;
            }
        }

        if ($id) {
            DB::table('lms_courses')->where('id', $id)->update($data);
            $msg = 'แก้ไขหลักสูตรอบรมสำเร็จ';
        } else {
            $data['created_by'] = Auth::id();
            $data['created_at'] = now();
            $data['thumbnail_url'] = '';
            // กำหนดค่าเริ่มต้นของ cover_url เผื่อว่าไม่ได้ใส่รูปปกตอนสร้างใหม่
            if (!isset($data['cover_url'])) {
                $data['cover_url'] = '';
            }
            DB::table('lms_courses')->insert($data);
            $msg = 'สร้างหลักสูตรอบรมใหม่สำเร็จ';
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    public function courseDestroy($id)
    {
        DB::transaction(function() use ($id) {
            // Delete submissions
            DB::table('lms_lesson_submissions')->where('course_id', $id)->delete();
            // Delete progress
            DB::table('lms_lesson_progress')->where('course_id', $id)->delete();
            // Delete activity
            DB::table('lms_lesson_activity')->where('course_id', $id)->delete();
            // Delete enrollments
            DB::table('lms_enrollments')->where('course_id', $id)->delete();
            
            // Delete quiz questions & answers
            $quizIds = DB::table('lms_quizzes')->where('course_id', $id)->pluck('id');
            if ($quizIds->isNotEmpty()) {
                DB::table('lms_quiz_answers')->whereIn('attempt_id', function($q) use ($quizIds) {
                    $q->select('id')->from('lms_quiz_attempts')->whereIn('quiz_id', $quizIds);
                })->delete();
                DB::table('lms_quiz_attempts')->whereIn('quiz_id', $quizIds)->delete();
                
                $questionIds = DB::table('lms_quiz_questions')->whereIn('quiz_id', $quizIds)->pluck('id');
                if ($questionIds->isNotEmpty()) {
                    DB::table('lms_quiz_options')->whereIn('question_id', $questionIds)->delete();
                    DB::table('lms_quiz_questions')->whereIn('quiz_id', $quizIds)->delete();
                }
                DB::table('lms_quizzes')->where('course_id', $id)->delete();
            }

            // Delete lessons
            DB::table('lms_lessons')->where('course_id', $id)->delete();
            
            // Delete course itself
            DB::table('lms_courses')->where('id', $id)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'ลบหลักสูตรอบรมและข้อมูลการเรียนที่เกี่ยวข้องทั้งหมดเรียบร้อยแล้ว'
        ]);
    }

    // --- Lessons Management ---
    public function lessonsIndex()
    {
        $courses = DB::table('lms_courses')->orderBy('id', 'desc')->get();
        return view('admin.lms.lessons', compact('courses'));
    }

    public function lessonsData(Request $request)
    {
        $courseId = $request->query('course_id');
        $query = DB::table('lms_lessons as l')
            ->join('lms_courses as c', 'c.id', '=', 'l.course_id')
            ->select('l.*', 'c.title as course_title');
        
        if ($courseId) {
            $query->where('l.course_id', $courseId);
        }

        $lessons = $query->orderBy('l.course_id', 'desc')
            ->orderBy('l.sort_order', 'asc')
            ->orderBy('l.id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lessons
        ]);
    }

    public function lessonStore(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'in:video,pdf,image,embed'],
            'content_url' => ['nullable', 'string', 'max:1000'],
            'content_html' => ['nullable', 'string'],
            'rubric_html' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer'],
            'min_focus_seconds' => ['required', 'integer', 'min:0'],
            'require_submission' => ['required', 'boolean'],
            'media_file' => ['nullable', 'file', 'max:51200'],
        ]);

        $id = $request->input('id');
        $data = [
            'course_id' => $request->input('course_id'),
            'title' => $request->input('title'),
            'content_type' => $request->input('content_type'),
            'content_url' => $request->input('content_url') ?: '',
            'content_html' => $request->input('content_html') ?: '',
            'rubric_html' => $request->input('rubric_html') ?: '',
            'sort_order' => $request->input('sort_order'),
            'min_focus_seconds' => $request->input('min_focus_seconds'),
            'require_submission' => $request->input('require_submission') ? 1 : 0,
            'min_video_seconds' => 0,
            'updated_at' => now(),
        ];

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid() && $file->getRealPath()) {
                $path = $file->store('lms/contents', 'public');
                $data['content_url'] = 'storage/' . $path;
            }
        }

        if ($id) {
            DB::table('lms_lessons')->where('id', $id)->update($data);
            $msg = 'แก้ไขบทเรียนสำเร็จ';
        } else {
            $data['created_at'] = now();
            DB::table('lms_lessons')->insert($data);
            $msg = 'สร้างบทเรียนใหม่สำเร็จ';
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    public function lessonDestroy($id)
    {
        DB::transaction(function() use ($id) {
            DB::table('lms_lesson_submissions')->where('lesson_id', $id)->delete();
            DB::table('lms_lesson_progress')->where('lesson_id', $id)->delete();
            DB::table('lms_lesson_activity')->where('lesson_id', $id)->delete();
            DB::table('lms_lessons')->where('id', $id)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'ลบบทเรียนเรียบร้อยแล้ว'
        ]);
    }

    // --- Quizzes Management ---
    public function quizzesIndex()
    {
        $courses = DB::table('lms_courses')->orderBy('id', 'desc')->get();
        return view('admin.lms.quizzes', compact('courses'));
    }

    public function quizzesData(Request $request)
    {
        $courseId = $request->query('course_id');
        $query = DB::table('lms_quizzes as q')
            ->join('lms_courses as c', 'c.id', '=', 'q.course_id')
            ->select('q.*', 'c.title as course_title', DB::raw('(SELECT COUNT(*) FROM lms_quiz_questions qq WHERE qq.quiz_id = q.id) as question_count'));

        if ($courseId) {
            $query->where('q.course_id', $courseId);
        }

        $quizzes = $query->orderBy('q.course_id', 'desc')
            ->orderBy('q.id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $quizzes
        ]);
    }

    public function quizStore(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'quiz_type' => ['required', 'in:pre,post'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $id = $request->input('id');
        $data = [
            'course_id' => $request->input('course_id'),
            'title' => $request->input('title'),
            'quiz_type' => $request->input('quiz_type'),
            'instructions' => $request->input('instructions') ?: '',
            'is_active' => $request->input('is_active') ? 1 : 0,
            'header_image' => '',
            'time_limit_mins' => 0,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('lms_quizzes')->where('id', $id)->update($data);
            $msg = 'แก้ไขแบบทดสอบสำเร็จ';
        } else {
            $data['created_at'] = now();
            DB::table('lms_quizzes')->insert($data);
            $msg = 'สร้างแบบทดสอบสำเร็จ';
        }

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    public function quizDestroy($id)
    {
        DB::transaction(function() use ($id) {
            DB::table('lms_quiz_answers')->whereIn('attempt_id', function($q) use ($id) {
                $q->select('id')->from('lms_quiz_attempts')->where('quiz_id', $id);
            })->delete();
            DB::table('lms_quiz_attempts')->where('quiz_id', $id)->delete();
            
            $questionIds = DB::table('lms_quiz_questions')->where('quiz_id', $id)->pluck('id');
            if ($questionIds->isNotEmpty()) {
                DB::table('lms_quiz_options')->whereIn('question_id', $questionIds)->delete();
                DB::table('lms_quiz_questions')->where('quiz_id', $id)->delete();
            }
            
            DB::table('lms_quizzes')->where('id', $id)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'ลบแบบทดสอบเรียบร้อยแล้ว'
        ]);
    }

    // --- Quiz Questions Management ---
    public function questionsIndex(Request $request)
    {
        $quizId = $request->query('quiz_id');
        $quiz = DB::table('lms_quizzes')->where('id', $quizId)->first();
        if (!$quiz) {
            return redirect()->route('admin.lms.quizzes.index')->with('error', 'ไม่พบแบบทดสอบดังกล่าว');
        }
        return view('admin.lms.questions', compact('quiz'));
    }

    public function questionsData(Request $request)
    {
        $quizId = $request->query('quiz_id');
        $questions = DB::table('lms_quiz_questions')
            ->where('quiz_id', $quizId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($questions as $q) {
            $q->options = DB::table('lms_quiz_options')
                ->where('question_id', $q->id)
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $questions
        ]);
    }

    public function questionStore(Request $request)
    {
        $request->validate([
            'quiz_id' => ['required', 'integer'],
            'question_text' => ['required', 'string'],
            'sort_order' => ['required', 'integer'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['required', 'boolean'],
        ]);

        $id = $request->input('id');
        $quizId = $request->input('quiz_id');
        
        $data = [
            'quiz_id' => $quizId,
            'question_text' => $request->input('question_text'),
            'sort_order' => $request->input('sort_order'),
            'media_url' => '',
            'question_type' => 'single',
            'updated_at' => now(),
        ];

        DB::transaction(function() use ($id, $quizId, $data, $request) {
            if ($id) {
                DB::table('lms_quiz_questions')->where('id', $id)->update($data);
                DB::table('lms_quiz_options')->where('question_id', $id)->delete();
                $questionId = $id;
            } else {
                $data['created_at'] = now();
                $questionId = DB::table('lms_quiz_questions')->insertGetId($data);
            }

            $options = $request->input('options');
            $optionRecords = [];
            foreach ($options as $opt) {
                $optionRecords[] = [
                    'question_id' => $questionId,
                    'option_text' => $opt['option_text'],
                    'is_correct' => $opt['is_correct'] ? 1 : 0,
                    'option_image_url' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('lms_quiz_options')->insert($optionRecords);
        });

        return response()->json([
            'status' => 'success',
            'message' => $id ? 'แก้ไขคำถามและตัวเลือกสำเร็จ' : 'เพิ่มคำถามใหม่สำเร็จ'
        ]);
    }

    public function questionDestroy($id)
    {
        DB::transaction(function() use ($id) {
            DB::table('lms_quiz_options')->where('question_id', $id)->delete();
            DB::table('lms_quiz_questions')->where('id', $id)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'ลบคำถามเรียบร้อยแล้ว'
        ]);
    }

    // --- Submissions Evaluation Management ---
    public function submissionsIndex()
    {
        $courses = DB::table('lms_courses')->orderBy('id', 'desc')->get();
        return view('admin.lms.submissions', compact('courses'));
    }

    public function submissionsData(Request $request)
    {
        $courseId = $request->query('course_id');
        $status = $request->query('status', 'pending');

        $query = DB::table('lms_lesson_submissions as s')
            ->join('lms_courses as c', 'c.id', '=', 's.course_id')
            ->join('lms_lessons as l', 'l.id', '=', 's.lesson_id')
            ->select('s.*', 'c.title as course_title', 'l.title as lesson_title', 'l.rubric_html');

        if ($courseId) {
            $query->where('s.course_id', $courseId);
        }

        if ($status !== 'all') {
            $query->where('s.status', $status);
        }

        $submissions = $query->orderBy('s.submitted_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $submissions
        ]);
    }

    public function submissionEvaluate(Request $request, $id)
    {
        $request->validate([
            'decision' => ['required', 'in:passed,failed'],
            'admin_comment' => ['nullable', 'string'],
        ]);

        $submission = DB::table('lms_lesson_submissions')->where('id', $id)->first();
        if (!$submission) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลการส่งงานดังกล่าว'
            ], 404);
        }

        $decision = $request->input('decision');
        $comment = $request->input('admin_comment') ?: '';

        DB::transaction(function() use ($id, $submission, $decision, $comment) {
            DB::table('lms_lesson_submissions')
                ->where('id', $id)
                ->update([
                    'status' => $decision,
                    'admin_comment' => $comment,
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);

            if ($decision === 'passed') {
                $lesson = DB::table('lms_lessons')->where('id', $submission->lesson_id)->first();
                $focusSec = $lesson ? ($lesson->min_focus_seconds ?: 30) : 30;

                DB::table('lms_lesson_progress')->updateOrInsert(
                    [
                        'user_id' => $submission->user_id,
                        'lesson_id' => $submission->lesson_id,
                    ],
                    [
                        'course_id' => $submission->course_id,
                        'focus_seconds' => $focusSec,
                        'completed_at' => now(),
                    ]
                );
            } else {
                DB::table('lms_lesson_progress')
                    ->where('user_id', $submission->user_id)
                    ->where('lesson_id', $submission->lesson_id)
                    ->delete();
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => $decision === 'passed' ? 'บันทึกการประเมิน: ผ่าน เรียบร้อยแล้ว' : 'บันทึกการประเมิน: ไม่ผ่าน/ต้องแก้ไข เรียบร้อยแล้ว'
        ]);
    }
}
