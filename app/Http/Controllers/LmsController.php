<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LmsController extends Controller
{
    /**
     * Format legacy video url to Youtube embed.
     */
    public static function embedVideoUrl($url)
    {
        $url = trim($url);
        if ($url === '') return '';

        $parts = parse_url($url);
        if (!is_array($parts)) return $url;

        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if (str_contains($host, 'youtu.be')) {
            $videoId = $path !== '' ? explode('/', $path)[0] : '';
            return $videoId !== '' ? 'https://www.youtube.com/embed/' . rawurlencode($videoId) : $url;
        }

        if (str_contains($host, 'youtube.com')) {
            $segments = $path === '' ? [] : explode('/', $path);
            $videoId = '';
            if (!empty($query['v'])) {
                $videoId = $query['v'];
            } elseif (($segments[0] ?? '') === 'embed' && !empty($segments[1])) {
                $videoId = $segments[1];
            } elseif (($segments[0] ?? '') === 'shorts' && !empty($segments[1])) {
                $videoId = $segments[1];
            } elseif (($segments[0] ?? '') === 'live' && !empty($segments[1])) {
                $videoId = $segments[1];
            }
            return $videoId !== '' ? 'https://www.youtube.com/embed/' . rawurlencode($videoId) : $url;
        }

        if (str_contains($host, 'vimeo.com')) {
            $videoId = '';
            $segments = $path === '' ? [] : explode('/', $path);
            foreach (array_reverse($segments) as $segment) {
                if (ctype_digit($segment)) {
                    $videoId = $segment;
                    break;
                }
            }
            return $videoId !== '' ? 'https://player.vimeo.com/video/' . rawurlencode($videoId) : $url;
        }

        return $url;
    }
    /**
     * Check if user is enrolled in a course.
     */
    private function isEnrolled($userId, $courseId)
    {
        if (!$userId) return false;
        return DB::table('lms_enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }

    /**
     * Get details of a single course inside LMS.
     */
    public function courseShow($id)
    {
        $userId = Auth::id();
        $course = DB::table('lms_courses')->where('id', $id)->first();
        if (!$course) {
            return abort(404, 'ไม่พบหลักสูตร');
        }

        if ($course->cover_url) {
            $coverUrl = $course->cover_url;
            if (str_starts_with($coverUrl, '/1/uploads/')) {
                $coverUrl = 'storage/' . substr($coverUrl, 3);
            } elseif (str_starts_with($coverUrl, '1/uploads/')) {
                $coverUrl = 'storage/' . substr($coverUrl, 2);
            }
            $course->cover_url = asset($coverUrl);
        }

        // Only allow admins to view drafts
        if ($course->status !== 'published' && Auth::user()->role !== 'admin') {
            return abort(403, 'หลักสูตรนี้ยังไม่เปิดเผยแพร่');
        }

        $isEnrolled = $this->isEnrolled($userId, $id);

        $lessons = DB::table('lms_lessons')
            ->where('course_id', $id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $completedMap = [];
        if ($isEnrolled) {
            $completedMap = DB::table('lms_lesson_progress')
                ->where('user_id', $userId)
                ->where('course_id', $id)
                ->pluck('lesson_id')
                ->mapWithKeys(fn($lessonId) => [$lessonId => true])
                ->toArray();
        }

        $quizzes = DB::table('lms_quizzes')
            ->where('course_id', $id)
            ->where('is_active', 1)
            ->get();

        $preQuiz = $quizzes->where('quiz_type', 'pre')->first();
        $postQuiz = $quizzes->where('quiz_type', 'post')->first();

        $prePercent = null;
        $postPercent = null;

        if ($preQuiz) {
            $preAttempt = DB::table('lms_quiz_attempts')
                ->where('quiz_id', $preQuiz->id)
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();
            if ($preAttempt) {
                $prePercent = (float)$preAttempt->percent;
            }
        }

        if ($postQuiz) {
            $postAttempt = DB::table('lms_quiz_attempts')
                ->where('quiz_id', $postQuiz->id)
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();
            if ($postAttempt) {
                $postPercent = (float)$postAttempt->percent;
            }
        }

        $totalLessons = count($lessons);
        $completedLessonsCount = count($completedMap);
        $allLessonsCompleted = $totalLessons > 0 && $completedLessonsCount >= $totalLessons;
        $improvement = ($prePercent !== null && $postPercent !== null) ? ($postPercent - $prePercent) : null;
        $passThreshold = (float)($course->pass_threshold ?? 60);
        $passByPost = ($postPercent !== null && $postPercent >= $passThreshold);
        
        $pretestRequired = ($preQuiz !== null);
        $pretestDone = !$pretestRequired || $prePercent !== null;

        // Submissions status
        $requiredLessons = $lessons->where('require_submission', 1)->pluck('id')->toArray();
        $requiredJobCount = count($requiredLessons);
        $passedJobCount = 0;
        if ($requiredJobCount > 0) {
            $passedJobCount = DB::table('lms_lesson_submissions')
                ->where('user_id', $userId)
                ->where('course_id', $id)
                ->whereIn('lesson_id', $requiredLessons)
                ->where('status', 'passed')
                ->count();
        }
        $allRequiredJobsPassed = ($requiredJobCount === 0 || $passedJobCount >= $requiredJobCount);
        $coursePassed = $passByPost && $allRequiredJobsPassed;

        return view('lms.course', compact(
            'course', 'lessons', 'isEnrolled', 'completedMap', 'preQuiz', 'postQuiz',
            'prePercent', 'postPercent', 'allLessonsCompleted', 'improvement',
            'passThreshold', 'passByPost', 'pretestDone', 'requiredJobCount',
            'passedJobCount', 'allRequiredJobsPassed', 'coursePassed'
        ));
    }

    /**
     * Enroll in a course.
     */
    public function enroll($id)
    {
        $userId = Auth::id();
        DB::table('lms_enrollments')->insertOrIgnore([
            'user_id' => $userId,
            'course_id' => $id,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ลงทะเบียนเข้าเรียนสำเร็จ',
        ]);
    }

    /**
     * Unenroll from a course.
     */
    public function unenroll($id)
    {
        $userId = Auth::id();

        DB::transaction(function() use ($userId, $id) {
            DB::table('lms_quiz_answers')->whereIn('attempt_id', function($q) use ($userId, $id) {
                $q->select('id')->from('lms_quiz_attempts')->where('user_id', $userId)->where('course_id', $id);
            })->delete();

            DB::table('lms_quiz_attempts')->where('user_id', $userId)->where('course_id', $id)->delete();
            DB::table('lms_lesson_activity')->where('user_id', $userId)->where('course_id', $id)->delete();
            DB::table('lms_lesson_progress')->where('user_id', $userId)->where('course_id', $id)->delete();
            DB::table('lms_lesson_submissions')->where('user_id', $userId)->where('course_id', $id)->delete();
            DB::table('lms_enrollments')->where('user_id', $userId)->where('course_id', $id)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'ยกเลิกการลงทะเบียนเรียบร้อยแล้ว ข้อมูลการเรียนทั้งหมดถูกลบออกแล้ว',
        ]);
    }

    /**
     * Show a lesson.
     */
    public function lessonShow($id)
    {
        $userId = Auth::id();
        $lesson = DB::table('lms_lessons')->where('id', $id)->first();
        if (!$lesson) {
            return abort(404, 'ไม่พบบทเรียน');
        }

        $courseId = $lesson->course_id;
        $course = DB::table('lms_courses')->where('id', $courseId)->first();
        if (!$course) {
            return abort(404, 'ไม่พบหลักสูตร');
        }

        if (!$this->isEnrolled($userId, $courseId)) {
            return redirect()->route('lms.courses.show', $courseId)->with('error', 'กรุณาลงทะเบียนเรียนก่อนเข้าสู่บทเรียน');
        }

        // Get adjacent lessons for next/prev navigation
        $lessons = DB::table('lms_lessons')
            ->where('course_id', $courseId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentIndex = $lessons->search(fn($l) => $l->id == $id);
        $prevLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < count($lessons) - 1 ? $lessons[$currentIndex + 1] : null;

        // Current completion status
        $isCompleted = DB::table('lms_lesson_progress')
            ->where('user_id', $userId)
            ->where('lesson_id', $id)
            ->exists();

        // Get submission if required
        $submission = null;
        if ($lesson->require_submission) {
            $submission = DB::table('lms_lesson_submissions')
                ->where('user_id', $userId)
                ->where('lesson_id', $id)
                ->first();
        }

        return view('lms.lesson', compact('course', 'lesson', 'prevLesson', 'nextLesson', 'isCompleted', 'submission'));
    }

    /**
     * Complete a lesson.
     */
    public function completeLesson($id)
    {
        $userId = Auth::id();
        $lesson = DB::table('lms_lessons')->where('id', $id)->first();
        if (!$lesson) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบบทเรียน'], 404);
        }

        DB::table('lms_lesson_progress')->insertOrIgnore([
            'user_id' => $userId,
            'course_id' => $lesson->course_id,
            'lesson_id' => $id,
            'focus_seconds' => $lesson->min_focus_seconds ?: 30,
            'completed_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'บันทึกความคืบหน้าการเรียนสำเร็จ',
        ]);
    }

    /**
     * Submit lesson assignment.
     */
    public function submitAssignment(Request $request, $id)
    {
        $userId = Auth::id();
        $lesson = DB::table('lms_lessons')->where('id', $id)->first();
        if (!$lesson) {
            return back()->with('error', 'ไม่พบบทเรียน');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB limit
            'student_note' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $fileName = 'submission_' . time() . '_' . uniqid() . '.' . $ext;
            $filePath = $file->storeAs('submissions', $fileName, 'public');

            // Delete old submission if it exists
            $oldSub = DB::table('lms_lesson_submissions')
                ->where('user_id', $userId)
                ->where('lesson_id', $id)
                ->first();
            if ($oldSub && $oldSub->file_url) {
                Storage::disk('public')->delete(str_replace('storage/', '', $oldSub->file_url));
            }

            // Get user name and school
            $profile = DB::table('teacher_profile')->where('user_id', Auth::id())->first();
            $sName = $profile ? ($profile->prefix . $profile->first_name . ' ' . $profile->last_name) : Auth::user()->name;
            $sSchool = $profile ? $profile->school_name : '';

            DB::table('lms_lesson_submissions')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'lesson_id' => $id,
                ],
                [
                    'course_id' => $lesson->course_id,
                    'file_url' => 'storage/' . $filePath,
                    'status' => 'pending',
                    'student_note' => $request->input('student_note') ?: '',
                    'admin_comment' => '',
                    'student_name' => $sName,
                    'student_school' => $sSchool,
                    'submitted_at' => now(),
                ]
            );

            return back()->with('success', 'ส่งงานเรียบร้อยแล้ว');
        }

        return back()->with('error', 'การอัปโหลดไฟล์ไม่สำเร็จ');
    }

    /**
     * Show quiz.
     */
    public function quizShow(Request $request)
    {
        $userId = Auth::id();
        $courseId = $request->query('course_id');
        $quizType = $request->query('type'); // 'pre' or 'post'

        $quiz = DB::table('lms_quizzes')
            ->where('course_id', $courseId)
            ->where('quiz_type', $quizType)
            ->where('is_active', 1)
            ->first();

        if (!$quiz) {
            return abort(404, 'ไม่พบแบบทดสอบที่ระบุ');
        }

        // Get questions and their options
        $questions = DB::table('lms_quiz_questions')
            ->where('quiz_id', $quiz->id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($questions as $q) {
            $q->options = DB::table('lms_quiz_options')
                ->where('question_id', $q->id)
                ->get();
        }

        return view('lms.quiz', compact('quiz', 'questions', 'courseId', 'quizType'));
    }

    /**
     * Submit quiz.
     */
    public function submitQuiz(Request $request, $id)
    {
        $userId = Auth::id();
        $quiz = DB::table('lms_quizzes')->where('id', $id)->first();
        if (!$quiz) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบแบบทดสอบ'], 404);
        }

        $answers = $request->input('answers', []); // question_id => option_id
        $questions = DB::table('lms_quiz_questions')->where('quiz_id', $id)->get();
        $totalQuestions = count($questions);
        
        if ($totalQuestions === 0) {
            return response()->json(['status' => 'error', 'message' => 'แบบทดสอบไม่มีคำถาม'], 422);
        }

        $score = 0;
        $answerRecords = [];

        foreach ($questions as $q) {
            $selectedOptionId = $answers[$q->id] ?? null;
            $isCorrect = 0;

            if ($selectedOptionId) {
                $option = DB::table('lms_quiz_options')->where('id', $selectedOptionId)->where('question_id', $q->id)->first();
                if ($option && $option->is_correct) {
                    $isCorrect = 1;
                    $score++;
                }
            }

            $answerRecords[] = [
                'question_id' => $q->id,
                'option_id' => $selectedOptionId,
                'is_correct' => $isCorrect,
            ];
        }

        $percent = ($score / $totalQuestions) * 100;

        $attemptId = DB::table('lms_quiz_attempts')->insertGetId([
            'quiz_id' => $id,
            'course_id' => $quiz->course_id,
            'user_id' => $userId,
            'score' => $score,
            'total' => $totalQuestions,
            'percent' => $percent,
            'submitted_at' => now(),
        ]);

        foreach ($answerRecords as &$record) {
            $record['attempt_id'] = $attemptId;
        }
        DB::table('lms_quiz_answers')->insert($answerRecords);

        return response()->json([
            'status' => 'success',
            'message' => 'ส่งแบบทดสอบเรียบร้อยแล้ว',
            'data' => [
                'score' => $score,
                'total' => $totalQuestions,
                'percent' => round($percent, 2),
            ],
        ]);
    }

    /**
     * Download Certificate PDF.
     */
    public function downloadCertificate($courseId)
    {
        $userId = Auth::id();
        
        // Include Dompdf autoload from legacy site
        $autoloadPath = 'C:\\inetpub\\wwwroot\\ee.cpn1.go.th\\e\\e.cpn1.go.th\\vendor\\autoload.php';
        if (is_file($autoloadPath)) {
            require_once $autoloadPath;
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            return abort(500, 'ระบบพิมพ์เกียรติบัตร (Dompdf) ยังไม่ได้ติดตั้งสมบูรณ์');
        }

        // Validate course completion
        $course = DB::table('lms_courses')->where('id', $courseId)->first();
        if (!$course) {
            return abort(404, 'ไม่พบหลักสูตร');
        }

        $postQuiz = DB::table('lms_quizzes')
            ->where('course_id', $courseId)
            ->where('quiz_type', 'post')
            ->where('is_active', 1)
            ->first();

        $postPercent = null;
        if ($postQuiz) {
            $postAttempt = DB::table('lms_quiz_attempts')
                ->where('quiz_id', $postQuiz->id)
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();
            if ($postAttempt) {
                $postPercent = (float)$postAttempt->percent;
            }
        }

        $passThreshold = (float)($course->pass_threshold ?? 60);
        $passByPost = ($postPercent !== null && $postPercent >= $passThreshold);

        // Submissions validation
        $requiredLessons = DB::table('lms_lessons')
            ->where('course_id', $courseId)
            ->where('require_submission', 1)
            ->pluck('id')
            ->toArray();
        $requiredJobCount = count($requiredLessons);
        $passedJobCount = 0;
        if ($requiredJobCount > 0) {
            $passedJobCount = DB::table('lms_lesson_submissions')
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->whereIn('lesson_id', $requiredLessons)
                ->where('status', 'passed')
                ->count();
        }
        $allRequiredJobsPassed = ($requiredJobCount === 0 || $passedJobCount >= $requiredJobCount);

        if (!$passByPost || !$allRequiredJobsPassed) {
            return redirect()->route('lms.courses.show', $courseId)->with('error', 'คุณยังไม่ผ่านเงื่อนไขการสำเร็จการศึกษาในหลักสูตรนี้');
        }

        // Retrieve user fullname from profile
        $profile = DB::table('teacher_profile')->where('user_id', Auth::id())->first();
        $fullName = $profile ? ($profile->prefix . $profile->first_name . ' ' . $profile->last_name) : Auth::user()->name;

        $dateText = date('d/m/') . (date('Y') + 543);

        // Background Image Data URI (from storage or legacy uploads folder)
        $backgroundImageDataUri = null;
        if ($course->certificate_bg_url) {
            $bgUrl = $course->certificate_bg_url;
            $fullPath = null;
            if (str_starts_with($bgUrl, 'storage/')) {
                $fullPath = storage_path('app/public/' . substr($bgUrl, 8));
            } elseif (str_contains($bgUrl, 'storage/')) {
                $pos = strpos($bgUrl, 'storage/');
                $fullPath = storage_path('app/public/' . substr($bgUrl, $pos + 8));
            } else {
                $relative = trim(str_replace('/1/uploads/lms/', '', $bgUrl), '/');
                $fullPath = 'C:\\inetpub\\wwwroot\\ee.cpn1.go.th\\e\\e.cpn1.go.th\\uploads\\lms\\' . str_replace('/', '\\', $relative);
            }

            if ($fullPath && is_file($fullPath)) {
                $binary = @file_get_contents($fullPath);
                if ($binary) {
                    $mime = 'image/jpeg';
                    if (str_ends_with(strtolower($fullPath), '.png')) {
                        $mime = 'image/png';
                    } elseif (str_ends_with(strtolower($fullPath), '.webp')) {
                        $mime = 'image/webp';
                    }
                    $backgroundImageDataUri = 'data:' . $mime . ';base64,' . base64_encode($binary);
                }
            }
        }

        $backgroundStyle = $backgroundImageDataUri
            ? "background-image:url('" . $backgroundImageDataUri . "');"
            : '';

        // Load fonts manually
        $fontRegularPath = 'C:\\inetpub\\wwwroot\\ee.cpn1.go.th\\e\\e.cpn1.go.th\\lms\\assets\\fonts\\Sarabun-Regular.ttf';
        $fontBoldPath = 'C:\\inetpub\\wwwroot\\ee.cpn1.go.th\\e\\e.cpn1.go.th\\lms\\assets\\fonts\\Sarabun-Bold.ttf';
        $fontFaceCss = '';
        if (is_file($fontRegularPath) && is_file($fontBoldPath)) {
            $regBinary = @file_get_contents($fontRegularPath);
            $boldBinary = @file_get_contents($fontBoldPath);
            if ($regBinary && $boldBinary) {
                $fontFaceCss = "
                @font-face {
                    font-family: 'Sarabun';
                    font-style: normal;
                    font-weight: 400;
                    src: url('data:font/ttf;base64," . base64_encode($regBinary) . "') format('truetype');
                }
                @font-face {
                    font-family: 'Sarabun';
                    font-style: normal;
                    font-weight: 700;
                    src: url('data:font/ttf;base64," . base64_encode($boldBinary) . "') format('truetype');
                }
                ";
            }
        }

        $html = view('lms.certificate', [
            'fontFaceCss' => $fontFaceCss,
            'backgroundStyle' => $backgroundStyle,
            'fullName' => $fullName,
            'courseTitle' => $course->title,
            'dateText' => $dateText,
        ])->render();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Sarabun');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $filename = 'certificate_course_' . $courseId . '_user_' . $userId . '.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
