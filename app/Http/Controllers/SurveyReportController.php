<?php

namespace App\Http\Controllers;

use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\SurveyAlignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SurveyReportController extends Controller
{
    protected $alignmentService;

    public function __construct(SurveyAlignmentService $alignmentService)
    {
        $this->alignmentService = $alignmentService;
    }

    /**
     * Display the index page for reports.
     */
    public function index(): View
    {
        return view('reports');
    }

    /**
     * Get JSON list of reports with search, filter, and pagination.
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $isAdmin = $request->user() && $request->user()->role === 'admin';

            $q_name = trim((string)$request->input('q_name'));
            $q_school = trim((string)$request->input('q_school'));
            $q_network = trim((string)$request->input('q_network'));

            $query = TeacherProfile::with(['school', 'educations', 'subjects', 'awards', 'cefr', 'hsk']);

            if ($q_name !== '') {
                $query->where(function ($q) use ($q_name) {
                    $q->where('first_name', 'LIKE', '%' . $q_name . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $q_name . '%')
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', '%' . $q_name . '%')
                      ->orWhere(DB::raw("CONCAT(prefix, ' ', first_name, ' ', last_name)"), 'LIKE', '%' . $q_name . '%');
                });
            }

            if ($q_school !== '') {
                $query->where('school_name', 'LIKE', '%' . $q_school . '%');
            }

            if ($q_network !== '') {
                $query->where('school_network', 'LIKE', '%' . $q_network . '%');
            }

            $query->orderBy('id', 'desc');

            // Paginate: 8 items per page
            $paginator = $query->paginate(8);

            // Process records
            $paginator->getCollection()->transform(function ($record) use ($isAdmin) {
                // Evaluate alignment
                $record->alignment = $this->alignmentService->evaluateAlignment($record->educations, $record->subjects);

                // Fetch LMS courses and matching state
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

                // Sensitive Data Protection
                if (!$isAdmin) {
                    unset($record->personalid);
                    unset($record->birth_date);
                    unset($record->birth_year_be);
                    unset($record->age);
                    unset($record->appointed_date);
                    unset($record->appointed_year_be);
                    unset($record->educations); // Hide detailed educations list
                }

                return $record;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ดึงข้อมูลสำเร็จ',
                'data' => $paginator
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete teacher profile and cascade relationships.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $record = TeacherProfile::findOrFail($id);

            // Delete relations manually to avoid db foreign constraint or cascade issues
            $record->educations()->delete();
            $record->subjects()->delete();
            $record->awards()->delete();
            $record->cefr()->delete();
            $record->hsk()->delete();
            $record->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'ลบข้อมูลบุคลากรและประวัติที่เกี่ยวข้องเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Export all data to Excel for Admin.
     */
    public function exportExcel(Request $request)
    {
        // Double check admin role
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงฟังก์ชันนี้');
        }

        try {
            // Get all records with all relations
            $records = TeacherProfile::with(['educations', 'subjects', 'awards', 'cefr', 'hsk'])
                ->orderBy('id', 'desc')
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('บันทึกข้อมูล');

            $headers = [
                'ID', 'รหัสโรงเรียน', 'ชื่อโรงเรียน', 'เครือข่าย', 'คำนำหน้า', 'ชื่อ', 'นามสกุล',
                'วันเกิด', 'ปีเกิด(พ.ศ.)', 'อายุ', 'ตำแหน่ง', 'วิทยฐานะ', 'วันที่บรรจุ', 'ปีบรรจุ(พ.ศ.)',
                'วิชาเอก (ป.ตรี)', 'วิชาเอก (ป.โท)', 'วิชาเอก (ป.เอก)', 'ภาระงานอื่น',
                'รูปถ่าย', 'รายวิชาที่สอน', 'รางวัลที่ภาคภูมิใจ', 
                'CEFR (สพฐ)', 'CEFR (อื่น ๆ)', 'HSK 3.0 (สพฐ)', 'HSK 3.0 (อื่น ๆ)', 'วันที่บันทึก'
            ];

            // Set header row
            foreach ($headers as $col => $header) {
                $cellAddress = Coordinate::stringFromColumnIndex($col + 1) . '1';
                $cell = $sheet->getCell($cellAddress);
                $cell->setValue($header);
                $cell->getStyle()->getFont()->setBold(true);
                $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
                $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $row = 2;
            foreach ($records as $record) {
                // Parse relations into text representation
                $subjectsText = $record->subjects->map(function ($s) {
                    return trim("{$s->subject_name} | {$s->subject_grade} | " . ($s->subject_hours ?? 0) . " ชม.");
                })->filter()->implode("\n");

                $awardsText = $record->awards->map(function ($a) {
                    $dateStr = $a->award_date ? date('j M Y', strtotime($a->award_date)) : '';
                    $yr = $a->award_date_be ?: ($dateStr ? (date('Y', strtotime($a->award_date)) + 543) : '');
                    return trim("{$a->work_name} | {$a->award_name} | " . ($yr ? "พ.ศ. {$yr}" : "") . " | {$a->issuer}");
                })->filter()->implode("\n");

                // Parse Language Exams
                $cefrObec = $record->cefr && $record->cefr->source === 'obec' ? $this->formatLangExam($record->cefr) : '';
                $cefrOther = $record->cefr && $record->cefr->source === 'other' ? $this->formatLangExam($record->cefr) : '';
                
                $hskObec = $record->hsk && $record->hsk->source === 'obec' ? $this->formatLangExam($record->hsk, 'hsk') : '';
                $hskOther = $record->hsk && $record->hsk->source === 'other' ? $this->formatLangExam($record->hsk, 'hsk') : '';

                $col = 1;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->id);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->school_code);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->school_name);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->school_network);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->prefix);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->first_name);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->last_name);
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $this->formatThaiDateShort($record->birth_date, $record->birth_year_be));
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->birth_year_be);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->age);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->position);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->academic_rank);
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $this->formatThaiDateShort($record->appointed_date, $record->appointed_year_be));
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->appointed_year_be);
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->bachelor_major);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->master_major);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->doctoral_major);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->other_workload);
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->profile_image_url ?: ($record->profile_image_path ?: ''));
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $subjectsText);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $awardsText);
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $cefrObec);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $cefrOther);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $hskObec);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $hskOther);
                
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $record->created_at ? date('Y-m-d H:i:s', strtotime($record->created_at)) : '');
                
                $row++;
            }

            // Auto-adjust column widths
            foreach (range(1, count($headers)) as $c) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
            }
            $sheet->getRowDimension(1)->setRowHeight(25);

            $filename = 'teacher_survey_report_' . date('Ymd_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $writer = new Xlsx($spreadsheet);
            
            // Clean output buffer to prevent corrupted downloads
            if (ob_get_length()) ob_clean();
            
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดาวน์โหลด Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format English/Chinese exam for text mapping.
     */
    private function formatLangExam($exam, $type = 'cefr'): string
    {
        if (!$exam) return '';
        $lvl = $type === 'cefr' ? $exam->cefr_level : $exam->hsk_level;
        $dateStr = $this->formatThaiDateShort($exam->cert_date, $exam->cert_date_be);
        return trim("{$lvl} | เลขใบรับรอง: {$exam->cert_no} | วันสอบ: {$dateStr} | ผู้ออกใบรับรอง: {$exam->issuer}");
    }

    /**
     * Format Thai short date presentation.
     */
    private function formatThaiDateShort($dateStr, $yearBe = null): string
    {
        if (!$dateStr) return '-';
        $time = strtotime($dateStr);
        if (!$time) return '-';

        $d = date('j', $time);
        $m = date('n', $time);
        $y = date('Y', $time);
        
        $months = [
            1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
            'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
        ];
        
        $thYear = $yearBe ?: ($y + 543);
        $thMonth = $months[$m] ?? '';
        
        return "{$d} {$thMonth} {$thYear}";
    }
}
