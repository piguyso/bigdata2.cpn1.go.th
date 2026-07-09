<?php

namespace App\Http\Controllers;

use App\Models\TeacherProfile;
use App\Models\TeacherEducation;
use App\Models\TeacherSubject;
use App\Models\TeacherAward;
use App\Models\TeacherCefr;
use App\Models\TeacherHsk;
use App\Models\User;
use App\Services\SurveyAlignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveyDashboardController extends Controller
{
    protected $alignmentService;

    public function __construct(SurveyAlignmentService $alignmentService)
    {
        $this->alignmentService = $alignmentService;
    }

    /**
     * Display the dashboard view.
     */
    public function index(): View
    {
        return view('dashboard');
    }

    /**
     * Get aggregate statistics for charts and counters.
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalRecords = TeacherProfile::count();
            $todayRecords = TeacherProfile::whereDate('created_at', today())->count();
            $thisMonthRecords = TeacherProfile::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
                
            $avgAge = (int)round(TeacherProfile::where('age', '>', 0)->avg('age'));

            // Position summary
            $positionSummary = TeacherProfile::selectRaw("COALESCE(NULLIF(position,''), 'ไม่ระบุ') AS label, COUNT(*) AS total")
                ->groupBy('position')
                ->orderBy('total', 'desc')
                ->get();

            // School summary (Top 10)
            $schoolSummary = TeacherProfile::selectRaw("COALESCE(NULLIF(school_name,''), 'ไม่ระบุ') AS label, COUNT(*) AS total")
                ->groupBy('school_name')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // Network summary (Top 10)
            $networkSummary = TeacherProfile::selectRaw("COALESCE(NULLIF(school_network,''), 'ไม่ระบุ') AS label, COUNT(*) AS total")
                ->groupBy('school_network')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // Education level summary
            // 3: Doctoral, 2: Master, 1: Bachelor
            $educationLevelSummary = DB::table('teacher_profile AS sr')
                ->leftJoin(DB::raw("(
                    SELECT record_id, MAX(CASE edu_level
                        WHEN 'ปริญญาเอก' THEN 3 WHEN 'ปริญญาโท' THEN 2 WHEN 'ปริญญาตรี' THEN 1 ELSE 0
                    END) AS max_level FROM teacher_educations GROUP BY record_id
                ) AS ed"), 'ed.record_id', '=', 'sr.id')
                ->selectRaw("CASE COALESCE(ed.max_level, 0)
                    WHEN 3 THEN 'ปริญญาเอก' WHEN 2 THEN 'ปริญญาโท' WHEN 1 THEN 'ปริญญาตรี' ELSE 'ไม่ระบุ'
                END AS label, COUNT(*) AS total")
                ->groupBy('label')
                ->orderBy('total', 'desc')
                ->get();

            // Gender summary
            $genderSummary = TeacherProfile::selectRaw("CASE WHEN prefix = 'นาย' THEN 'ชาย' WHEN prefix IN ('นาง','นางสาว') THEN 'หญิง' ELSE 'ไม่ระบุ' END AS label, COUNT(*) AS total")
                ->groupBy('label')
                ->get();

            // CEFR summary
            $cefrSummary = TeacherCefr::whereNotNull('cefr_level')
                ->where('cefr_level', '<>', '')
                ->selectRaw("source, cefr_level, COUNT(*) AS total")
                ->groupBy('source', 'cefr_level')
                ->orderBy('source', 'asc')
                ->get();

            // HSK summary
            $hskSummary = TeacherHsk::whereNotNull('hsk_level')
                ->where('hsk_level', '<>', '')
                ->selectRaw("source, hsk_level, COUNT(*) AS total")
                ->groupBy('source', 'hsk_level')
                ->orderBy('source', 'asc')
                ->get();

            // Top subjects
            $subjectTop = TeacherSubject::selectRaw("COALESCE(NULLIF(subject_name,''), 'ไม่ระบุ') AS label, COUNT(*) AS total")
                ->groupBy('subject_name')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // Top recruitment subjects (วิชาที่สอบบรรจุ)
            $recruitmentSubjectSummary = TeacherProfile::selectRaw("COALESCE(NULLIF(recruitment_subject,''), 'ไม่ระบุ') AS label, COUNT(*) AS total")
                ->groupBy('recruitment_subject')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // Academic rank summary (วิทยฐานะ)
            $academicRankSummary = TeacherProfile::selectRaw("COALESCE(NULLIF(academic_rank,''), 'ไม่มีวิทยฐานะ') AS label, COUNT(*) AS total")
                ->groupBy('academic_rank')
                ->orderBy('total', 'desc')
                ->get();

            // Other general counts
            $awardCount = TeacherAward::count();
            $cefrCount = TeacherCefr::count();
            $subjectCount = TeacherSubject::count();
            $hskCount = TeacherHsk::count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalRecords' => $totalRecords,
                    'todayRecords' => $todayRecords,
                    'thisMonthRecords' => $thisMonthRecords,
                    'avgAge' => $avgAge,
                    'positionSummary' => $positionSummary,
                    'schoolSummary' => $schoolSummary,
                    'networkSummary' => $networkSummary,
                    'educationLevelSummary' => $educationLevelSummary,
                    'genderSummary' => $genderSummary,
                    'cefrSummary' => $cefrSummary,
                    'hskSummary' => $hskSummary,
                    'subjectTop' => $subjectTop,
                    'recruitmentSubjectSummary' => $recruitmentSubjectSummary,
                    'academicRankSummary' => $academicRankSummary,
                    'counts' => [
                        'awards' => $awardCount,
                        'cefr' => $cefrCount,
                        'subjects' => $subjectCount,
                        'hsk' => $hskCount
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get detailed list of teachers for drilldown views.
     */
    public function getDrilldownData(Request $request): JsonResponse
    {
        try {
            $isAdmin = $request->user() && $request->user()->role === 'admin';
            
            $type = $request->input('type'); // 'school', 'network', 'position', 'cefr', 'hsk', 'education', 'all'
            $value = $request->input('value'); // The target filter value

            $query = TeacherProfile::with(['school', 'educations', 'subjects', 'awards', 'cefr', 'hsk']);

            switch ($type) {
                case 'school':
                    $query->where('school_name', $value);
                    break;
                case 'network':
                    $query->where('school_network', $value);
                    break;
                case 'position':
                    $query->where('position', $value);
                    break;
                case 'cefr':
                    $query->whereHas('cefr', function ($q) use ($value) {
                        $q->where('cefr_level', $value);
                    });
                    break;
                case 'hsk':
                    $query->whereHas('hsk', function ($q) use ($value) {
                        $q->where('hsk_level', $value);
                    });
                    break;
                case 'education':
                    if ($value === 'ปริญญาเอก') {
                        $query->whereHas('educations', function ($q) { $q->where('edu_level', 'ปริญญาเอก'); });
                    } elseif ($value === 'ปริญญาโท') {
                        $query->whereHas('educations', function ($q) { $q->where('edu_level', 'ปริญญาโท'); })
                              ->whereDoesntHave('educations', function ($q) { $q->where('edu_level', 'ปริญญาเอก'); });
                    } elseif ($value === 'ปริญญาตรี') {
                        $query->whereHas('educations', function ($q) { $q->where('edu_level', 'ปริญญาตรี'); })
                              ->whereDoesntHave('educations', function ($q) { $q->whereIn('edu_level', ['ปริญญาโท', 'ปริญญาเอก']); });
                    } else {
                        $query->whereDoesntHave('educations');
                    }
                    break;
                case 'gender':
                    if ($value === 'ชาย') {
                        $query->where('prefix', 'นาย');
                    } elseif ($value === 'หญิง') {
                        $query->whereIn('prefix', ['นาง', 'นางสาว']);
                    } else {
                        $query->whereNotIn('prefix', ['นาย', 'นาง', 'นางสาว'])->orWhereNull('prefix');
                    }
                    break;
                case 'subject':
                    $query->whereHas('subjects', function ($q) use ($value) {
                        $q->where('subject_name', $value);
                    });
                    break;
                case 'recruitment_subject':
                    $query->where('recruitment_subject', $value);
                    break;
                case 'academic_rank':
                    if ($value === 'ไม่มีวิทยฐานะ' || $value === 'ไม่ระบุ') {
                        $query->where(function($q) {
                            $q->whereNull('academic_rank')->orWhere('academic_rank', '');
                        });
                    } else {
                        $query->where('academic_rank', $value);
                    }
                    break;
            }

            $records = $query->orderBy('id', 'desc')->get();

            // Transform records and protect privacy
            $records->transform(function ($record) use ($isAdmin) {
                // Calculate major alignment
                $record->alignment = $this->alignmentService->evaluateAlignment($record->educations, $record->subjects);

                // Fetch LMS status
                $lmsMatched = false;
                $lmsCourses = [];
                $user = null;

                if ($record->email) {
                    $user = User::where('email', $record->email)->first();
                }

                if ($user) {
                    $lmsMatched = true;
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
                }

                $record->lms_matched = $lmsMatched;
                $record->lms_courses = $lmsCourses;

                // Strip sensitive data for general users
                if (!$isAdmin) {
                    unset($record->personalid);
                    unset($record->birth_date);
                    unset($record->birth_year_be);
                    unset($record->age);
                    unset($record->appointed_date);
                    unset($record->appointed_year_be);
                    unset($record->educations);
                }

                return $record;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ดึงข้อมูลเจาะลึกสำเร็จ',
                'data' => $records
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลเจาะลึก: ' . $e->getMessage()
            ], 422);
        }
    }
}
