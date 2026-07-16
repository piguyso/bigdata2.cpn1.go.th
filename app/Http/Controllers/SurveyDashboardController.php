<?php

namespace App\Http\Controllers;

use App\Services\SchoolDistanceService;
use App\Support\SchoolLogo;
use App\Support\SimpleXlsxExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SurveyDashboardController extends Controller
{
    public function __construct(
        private readonly SchoolDistanceService $schoolDistanceService
    ) {
    }

    private const SCHOOL_SIZE_LABELS = [
        'small' => 'เล็ก',
        'medium' => 'กลาง',
        'large' => 'ใหญ่',
        'special' => 'ใหญ่พิเศษ',
    ];

    private const GRADE_LABELS = [
        'k1' => 'อนุบาล 1',
        'k2' => 'อนุบาล 2',
        'k3' => 'อนุบาล 3',
        'p1' => 'ประถมศึกษาปีที่ 1',
        'p2' => 'ประถมศึกษาปีที่ 2',
        'p3' => 'ประถมศึกษาปีที่ 3',
        'p4' => 'ประถมศึกษาปีที่ 4',
        'p5' => 'ประถมศึกษาปีที่ 5',
        'p6' => 'ประถมศึกษาปีที่ 6',
        'm1' => 'มัธยมศึกษาปีที่ 1',
        'm2' => 'มัธยมศึกษาปีที่ 2',
        'm3' => 'มัธยมศึกษาปีที่ 3',
        'm4' => 'มัธยมศึกษาปีที่ 4',
        'm5' => 'มัธยมศึกษาปีที่ 5',
        'm6' => 'มัธยมศึกษาปีที่ 6',
        'voc1' => 'ปวช. 1',
        'voc2' => 'ปวช. 2',
        'voc3' => 'ปวช. 3',
    ];

    private const LEVEL_LABELS = [
        'pre_primary_total' => 'รวมก่อนประถม',
        'primary_total' => 'รวมประถมศึกษา',
        'lower_secondary_total' => 'รวมมัธยมศึกษาตอนต้น',
        'upper_secondary_total' => 'รวมมัธยมศึกษาตอนปลาย',
    ];

    private const LEVEL_TREND_LABELS = [
        'pre_primary_total' => 'ก่อนประถม',
        'primary_total' => 'ประถมศึกษา',
        'lower_secondary_total' => 'มัธยมศึกษาตอนต้น',
        'upper_secondary_total' => 'มัธยมศึกษาตอนปลาย',
        'all_total' => 'รวมทั้งหมด',
    ];

    public function index(): View
    {
        return view('dashboard');
    }

    public function schools(Request $request): View
    {
        return view('dashboard-school-size', [
            'size' => 'all',
            'sizeLabel' => 'ทั้งหมด',
            'isAll' => true,
            'pageTitle' => 'โรงเรียนทั้งหมด',
            'pageBreadcrumb' => 'รายชื่อโรงเรียนทั้งหมด',
            'summaryLabel' => 'รายการ',
            'filterType' => 'all_schools',
            'filterValue' => 'all',
            'sizeGroup' => 'all',
            'sizeGroupLabel' => 'ทั้งหมด',
            'isOpportunity' => false,
            'sizeRoutePrefix' => url('/schools/size'),
            'allRoute' => route('dashboard.schools'),
            'allLabel' => 'โรงเรียนทั้งหมด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-school',
            'cardTheme' => 'default',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียน',
            'showSizeLinks' => true,
            'showOpportunityLinks' => false,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function schoolsBySize(string $size, Request $request): View
    {
        abort_unless(array_key_exists($size, self::SCHOOL_SIZE_LABELS), 404);

        return view('dashboard-school-size', [
            'size' => $size,
            'sizeLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isAll' => false,
            'pageTitle' => 'โรงเรียนขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'pageBreadcrumb' => 'รายชื่อโรงเรียนขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'summaryLabel' => 'ขนาดโรงเรียน',
            'filterType' => 'school_size',
            'filterValue' => $size,
            'sizeGroup' => 'all',
            'sizeGroupLabel' => 'ทุกโรงเรียน',
            'isOpportunity' => false,
            'sizeRoutePrefix' => url('/schools/size'),
            'allRoute' => route('dashboard.schools'),
            'allLabel' => 'โรงเรียนทั้งหมด',
            'showSizeBreakdown' => false,
            'pageIcon' => 'fa-solid fa-school',
            'cardTheme' => 'default',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'showSizeLinks' => false,
            'showOpportunityLinks' => false,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function opportunitySchools(Request $request): View
    {
        return view('dashboard-school-size', [
            'size' => 'all',
            'sizeLabel' => 'ขยายโอกาส',
            'isAll' => false,
            'pageTitle' => 'โรงเรียนขยายโอกาส',
            'pageBreadcrumb' => 'รายชื่อโรงเรียนขยายโอกาส',
            'summaryLabel' => 'ประเภท',
            'filterType' => 'opportunity_schools',
            'filterValue' => 'opportunity',
            'sizeGroup' => 'all',
            'sizeGroupLabel' => 'ทุกขนาด',
            'isOpportunity' => true,
            'sizeRoutePrefix' => url('/schools/opportunity/size'),
            'allRoute' => route('dashboard.opportunity-schools'),
            'allLabel' => 'โรงเรียนขยายโอกาสทั้งหมด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-building-columns',
            'cardTheme' => 'opportunity',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนขยายโอกาส',
            'showSizeLinks' => true,
            'showOpportunityLinks' => true,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function opportunitySchoolsBySize(string $size, Request $request): View
    {
        abort_unless(array_key_exists($size, self::SCHOOL_SIZE_LABELS), 404);

        return view('dashboard-school-size', [
            'size' => $size,
            'sizeLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isAll' => false,
            'pageTitle' => 'โรงเรียนขยายโอกาสขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'pageBreadcrumb' => 'รายชื่อโรงเรียนขยายโอกาสขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'summaryLabel' => 'ขนาดโรงเรียน',
            'filterType' => 'opportunity_school_size',
            'filterValue' => $size,
            'sizeGroup' => $size,
            'sizeGroupLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isOpportunity' => true,
            'sizeRoutePrefix' => url('/schools/opportunity/size'),
            'allRoute' => route('dashboard.opportunity-schools'),
            'allLabel' => 'โรงเรียนขยายโอกาสทั้งหมด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-building-columns',
            'cardTheme' => 'opportunity',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนขยายโอกาสขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'showSizeLinks' => true,
            'showOpportunityLinks' => true,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function networkSchools(string $network, Request $request): View
    {
        $network = urldecode($network);

        return view('dashboard-school-size', [
            'size' => 'all',
            'sizeLabel' => $network,
            'isAll' => false,
            'pageTitle' => 'โรงเรียนในเครือข่าย'.$network,
            'pageBreadcrumb' => 'รายชื่อโรงเรียนเครือข่าย'.$network,
            'summaryLabel' => 'เครือข่าย',
            'filterType' => 'network',
            'filterValue' => $network,
            'sizeGroup' => 'all',
            'sizeGroupLabel' => 'ทุกขนาด',
            'isOpportunity' => false,
            'sizeRoutePrefix' => url('/schools/network/'.rawurlencode($network).'/size'),
            'allRoute' => route('dashboard.network-schools', ['network' => $network]),
            'allLabel' => 'ทุกขนาด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-layer-group',
            'cardTheme' => 'network',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนในเครือข่าย'.$network,
            'showSizeLinks' => true,
            'showOpportunityLinks' => false,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function networkSchoolsBySize(string $network, string $size, Request $request): View
    {
        abort_unless(array_key_exists($size, self::SCHOOL_SIZE_LABELS), 404);
        $network = urldecode($network);

        return view('dashboard-school-size', [
            'size' => $size,
            'sizeLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isAll' => false,
            'pageTitle' => 'โรงเรียนเครือข่าย'.$network.' ขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'pageBreadcrumb' => 'รายชื่อโรงเรียนเครือข่าย'.$network.' ขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'summaryLabel' => 'ขนาดโรงเรียน',
            'filterType' => 'network_school_size',
            'filterValue' => $network.'||'.$size,
            'sizeGroup' => $size,
            'sizeGroupLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isOpportunity' => false,
            'sizeRoutePrefix' => url('/schools/network/'.rawurlencode($network).'/size'),
            'allRoute' => route('dashboard.network-schools', ['network' => $network]),
            'allLabel' => 'ทุกขนาด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-layer-group',
            'cardTheme' => 'network',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนเครือข่าย'.$network.' ขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'showSizeLinks' => true,
            'showOpportunityLinks' => false,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function districtSchools(string $district, Request $request): View
    {
        $district = urldecode($district);

        return view('dashboard-school-size', [
            'size' => 'all',
            'sizeLabel' => $district,
            'isAll' => false,
            'pageTitle' => 'โรงเรียนในอำเภอ'.$district,
            'pageBreadcrumb' => 'รายชื่อโรงเรียนอำเภอ'.$district,
            'summaryLabel' => 'อำเภอ',
            'filterType' => 'district',
            'filterValue' => $district,
            'sizeGroup' => 'all',
            'sizeGroupLabel' => 'ทุกขนาด',
            'isOpportunity' => false,
            'sizeRoutePrefix' => url('/schools/district/'.rawurlencode($district).'/size'),
            'allRoute' => route('dashboard.district-schools', ['district' => $district]),
            'allLabel' => 'ทุกขนาด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-map-location-dot',
            'cardTheme' => 'district',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนในอำเภอ'.$district,
            'showSizeLinks' => true,
            'showOpportunityLinks' => false,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function districtSchoolsBySize(string $district, string $size, Request $request): View
    {
        abort_unless(array_key_exists($size, self::SCHOOL_SIZE_LABELS), 404);
        $district = urldecode($district);

        return view('dashboard-school-size', [
            'size' => $size,
            'sizeLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isAll' => false,
            'pageTitle' => 'โรงเรียนอำเภอ'.$district.' ขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'pageBreadcrumb' => 'รายชื่อโรงเรียนอำเภอ'.$district.' ขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'summaryLabel' => 'ขนาดโรงเรียน',
            'filterType' => 'district_school_size',
            'filterValue' => $district.'||'.$size,
            'sizeGroup' => $size,
            'sizeGroupLabel' => self::SCHOOL_SIZE_LABELS[$size],
            'isOpportunity' => false,
            'sizeRoutePrefix' => url('/schools/district/'.rawurlencode($district).'/size'),
            'allRoute' => route('dashboard.district-schools', ['district' => $district]),
            'allLabel' => 'ทุกขนาด',
            'showSizeBreakdown' => true,
            'pageIcon' => 'fa-solid fa-map-location-dot',
            'cardTheme' => 'district',
            'emptyTitle' => 'ไม่พบข้อมูลโรงเรียนอำเภอ'.$district.' ขนาด'.self::SCHOOL_SIZE_LABELS[$size],
            'showSizeLinks' => true,
            'showOpportunityLinks' => false,
            'academicYear' => $request->string('academic_year')->toString(),
            'term' => (int) $request->input('term', 0),
        ]);
    }

    public function getStats(Request $request): JsonResponse
    {
        $availableYears = DB::table('academic_years')
            ->orderByDesc('sort_order')
            ->orderByDesc('year')
            ->pluck('year')
            ->values();

        $activeYear = DB::table('academic_years')
            ->where('is_active', true)
            ->value('year');

        $selectedYear = $request->string('academic_year')->toString();
        if ($selectedYear === '' || ! $availableYears->contains($selectedYear)) {
            $selectedYear = $availableYears->contains($activeYear) ? $activeYear : ($availableYears->first() ?? '');
        }

        $availableTerms = collect();
        if ($selectedYear !== '') {
            $availableTerms = DB::table('schoolmis_records')
                ->where('academic_year', $selectedYear)
                ->select('term')
                ->distinct()
                ->orderBy('term')
                ->pluck('term')
                ->values();

            if ($availableTerms->isEmpty()) {
                $availableTerms = collect([1, 2]);
            }
        }

        $selectedTerm = (int) $request->input('term', 0);
        if ($selectedTerm === 0 || ! $availableTerms->contains($selectedTerm)) {
            $selectedTerm = (int) ($availableTerms->last() ?? 0);
        }

        if ($selectedYear === '' || $selectedTerm === 0) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'availableYears' => $availableYears,
                    'availableTerms' => $availableTerms,
                    'selectedYear' => $selectedYear,
                    'selectedTerm' => $selectedTerm,
                    'overview' => [
                        'schoolsCount' => 0,
                        'opportunitySchoolsCount' => 0,
                        'matchedSchoolsCount' => 0,
                        'studentTotal' => 0,
                        'roomTotal' => 0,
                        'maleTotal' => 0,
                        'femaleTotal' => 0,
                        'avgStudentsPerSchool' => 0,
                        'schoolSizeSummary' => [
                            'small' => 0,
                            'medium' => 0,
                            'large' => 0,
                            'special' => 0,
                        ],
                        'opportunitySchoolSizeSummary' => [
                            'small' => 0,
                            'medium' => 0,
                            'large' => 0,
                            'special' => 0,
                        ],
                    ],
                    'genderSummary' => [],
                    'genderTrend' => [],
                    'levelSummary' => [],
                    'networkSummary' => [],
                    'districtSummary' => [],
                    'topSchools' => [],
                    'latestImport' => null,
                ],
            ]);
        }

        $records = DB::table('schoolmis_records as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis')
            ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('records.academic_year', $selectedYear)
            ->where('records.term', $selectedTerm)
            ->select(
                'records.*',
                'schools.schoolname',
                'schools.amper',
                'schools.tambon',
                'schools.schoolgroup',
                'schools.logo_path',
                'groups.name as schoolgroup_name'
            )
            ->orderBy('records.school_smis')
            ->get();

        $overview = [
            'schoolsCount' => $records->count(),
            'opportunitySchoolsCount' => 0,
            'matchedSchoolsCount' => $records->whereNotNull('schoolname')->count(),
            'studentTotal' => (int) $records->sum('student_total'),
            'roomTotal' => (int) $records->sum('room_total'),
            'maleTotal' => (int) $records->sum('male_total'),
            'femaleTotal' => (int) $records->sum('female_total'),
            'avgStudentsPerSchool' => $records->count() > 0
                ? round($records->sum('student_total') / $records->count(), 1)
                : 0,
            'schoolSizeSummary' => [
                'small' => 0,
                'medium' => 0,
                'large' => 0,
                'special' => 0,
            ],
            'opportunitySchoolSizeSummary' => [
                'small' => 0,
                'medium' => 0,
                'large' => 0,
                'special' => 0,
            ],
        ];

        foreach ($records as $record) {
            $total = (int) $record->student_total;
            $metrics = json_decode((string) $record->metrics, true) ?: [];

            if ($this->hasOpportunityStudents($metrics)) {
                $overview['opportunitySchoolsCount']++;
            }

            $sizeKey = $this->schoolSizeKey($total);
            $overview['schoolSizeSummary'][$sizeKey]++;

            if ($this->hasOpportunityStudents($metrics)) {
                $overview['opportunitySchoolSizeSummary'][$sizeKey]++;
            }
        }

        $genderSummary = [
            ['label' => 'ชาย', 'total' => $overview['maleTotal']],
            ['label' => 'หญิง', 'total' => $overview['femaleTotal']],
        ];

        $genderTrend = DB::table('schoolmis_records')
            ->select(
                'academic_year',
                'term',
                DB::raw('SUM(male_total) as male_total'),
                DB::raw('SUM(female_total) as female_total'),
                DB::raw('SUM(student_total) as student_total')
            )
            ->groupBy('academic_year', 'term')
            ->orderBy('academic_year')
            ->orderBy('term')
            ->get()
            ->map(function ($row) {
                return [
                    'academic_year' => $row->academic_year,
                    'term' => (int) $row->term,
                    'male_total' => (int) $row->male_total,
                    'female_total' => (int) $row->female_total,
                    'student_total' => (int) $row->student_total,
                    'label' => $row->academic_year.' / '.$row->term,
                ];
            })
            ->values();

        $levelBuckets = self::LEVEL_TREND_LABELS;

        $levelTotals = [];
        foreach ($levelBuckets as $key => $label) {
            $levelTotals[$key] = [
                'key' => $key,
                'label' => $label,
                'male' => 0,
                'female' => 0,
                'total' => 0,
                'rooms' => 0,
            ];
        }

        foreach ($records as $record) {
            $metrics = json_decode((string) $record->metrics, true) ?: [];
            foreach ($levelBuckets as $key => $label) {
                if (! isset($metrics[$key])) {
                    continue;
                }

                $levelTotals[$key]['male'] += (int) ($metrics[$key]['male'] ?? 0);
                $levelTotals[$key]['female'] += (int) ($metrics[$key]['female'] ?? 0);
                $levelTotals[$key]['total'] += (int) ($metrics[$key]['total'] ?? 0);
                $levelTotals[$key]['rooms'] += (int) ($metrics[$key]['rooms'] ?? 0);
            }
        }

        $networkSummary = $records
            ->groupBy(fn ($row) => $row->schoolgroup_name ?: ($row->schoolgroup ?: 'ไม่ระบุเครือข่าย'))
            ->map(function ($group, $label) {
                $sizeSummary = [
                    'small' => 0,
                    'medium' => 0,
                    'large' => 0,
                    'special' => 0,
                ];

                foreach ($group as $row) {
                    $sizeSummary[$this->schoolSizeKey((int) $row->student_total)]++;
                }

                return [
                    'label' => $label,
                    'schools' => $group->count(),
                    'students' => (int) $group->sum('student_total'),
                    'rooms' => (int) $group->sum('room_total'),
                    'sizeSummary' => $sizeSummary,
                ];
            })
            ->sortBy(fn ($item) => $this->networkSortKey((string) $item['label']))
            ->values()
            ->take(10)
            ->all();

        $districtSummary = $records
            ->groupBy(fn ($row) => $row->amper ?: 'ไม่ระบุอำเภอ')
            ->map(function ($group, $label) {
                $sizeSummary = [
                    'small' => 0,
                    'medium' => 0,
                    'large' => 0,
                    'special' => 0,
                ];

                foreach ($group as $row) {
                    $sizeSummary[$this->schoolSizeKey((int) $row->student_total)]++;
                }

                return [
                    'label' => $label,
                    'schools' => $group->count(),
                    'students' => (int) $group->sum('student_total'),
                    'rooms' => (int) $group->sum('room_total'),
                    'sizeSummary' => $sizeSummary,
                ];
            })
            ->sortByDesc('students')
            ->values()
            ->take(10)
            ->all();

        $topSchools = $records
            ->map(function ($row) {
                return [
                    'school_smis' => $row->school_smis,
                    'school_name' => $row->schoolname ?: 'ไม่พบชื่อโรงเรียนในระบบ',
                    'logo_url' => SchoolLogo::url($row->logo_path ?? null),
                    'network' => $row->schoolgroup_name ?: ($row->schoolgroup ?: '-'),
                    'district' => $row->amper ?: '-',
                    'students' => (int) $row->student_total,
                    'rooms' => (int) $row->room_total,
                ];
            })
            ->sortByDesc('students')
            ->values()
            ->take(12)
            ->all();

        $latestImport = DB::table('schoolmis_imports')
            ->where('academic_year', $selectedYear)
            ->where('term', $selectedTerm)
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'availableYears' => $availableYears,
                'availableTerms' => $availableTerms,
                'selectedYear' => $selectedYear,
                'selectedTerm' => $selectedTerm,
                'overview' => $overview,
                'genderSummary' => $genderSummary,
                'genderTrend' => $genderTrend,
                'levelSummary' => array_values($levelTotals),
                'networkSummary' => $networkSummary,
                'districtSummary' => $districtSummary,
                'topSchools' => $topSchools,
                'latestImport' => $latestImport,
            ],
        ]);
    }

    public function getLevelTrend(Request $request): JsonResponse
    {
        $level = $request->string('level')->toString();

        if ($level === '' || ! array_key_exists($level, self::LEVEL_TREND_LABELS)) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'level' => $level,
                    'levelLabel' => null,
                    'points' => [],
                    'summary' => [
                        'first' => null,
                        'latest' => null,
                        'change' => 0,
                        'changePercent' => 0,
                    ],
                ],
            ]);
        }

        $points = DB::table('schoolmis_records')
            ->select('academic_year', 'term', 'metrics')
            ->orderBy('academic_year')
            ->orderBy('term')
            ->get()
            ->groupBy(fn ($row) => $row->academic_year.'-'.$row->term)
            ->map(function ($rows) use ($level) {
                $first = $rows->first();
                $maleTotal = 0;
                $femaleTotal = 0;
                $studentTotal = 0;
                $roomTotal = 0;

                foreach ($rows as $row) {
                    $metrics = json_decode((string) $row->metrics, true) ?: [];
                    $bucket = $metrics[$level] ?? [];
                    $maleTotal += (int) ($bucket['male'] ?? 0);
                    $femaleTotal += (int) ($bucket['female'] ?? 0);
                    $studentTotal += (int) ($bucket['total'] ?? 0);
                    $roomTotal += (int) ($bucket['rooms'] ?? 0);
                }

                return [
                    'academic_year' => $first->academic_year,
                    'term' => (int) $first->term,
                    'male_total' => $maleTotal,
                    'female_total' => $femaleTotal,
                    'student_total' => $studentTotal,
                    'room_total' => $roomTotal,
                    'schools_count' => $rows->count(),
                    'label' => $first->academic_year.' / '.$first->term,
                ];
            })
            ->values();

        $firstPoint = $points->first();
        $latestPoint = $points->last();
        $change = $firstPoint && $latestPoint ? ((int) $latestPoint['student_total'] - (int) $firstPoint['student_total']) : 0;
        $changePercent = $firstPoint && (int) $firstPoint['student_total'] > 0
            ? round(($change * 100) / (int) $firstPoint['student_total'], 1)
            : 0.0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'level' => $level,
                'levelLabel' => self::LEVEL_TREND_LABELS[$level],
                'points' => $points,
                'summary' => [
                    'first' => $firstPoint,
                    'latest' => $latestPoint,
                    'change' => $change,
                    'changePercent' => $changePercent,
                ],
            ],
        ]);
    }

    public function getDrilldownData(Request $request): JsonResponse
    {
        $selectedYear = $request->string('academic_year')->toString();
        $selectedTerm = (int) $request->input('term', 0);
        $type = $request->string('type')->toString();
        $value = $request->string('value')->toString();

        if ($selectedYear === '' || $selectedTerm === 0 || $type === '' || $value === '') {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->attachSchoolLogoUrls($this->getSchoolListingRows($selectedYear, $selectedTerm, $type, $value))->values(),
        ]);
    }

    private function getSchoolListingRows(string $selectedYear, int $selectedTerm, string $type, string $value, bool $fullSelect = false)
    {
        $query = DB::table('schoolmis_records as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis')
            ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('records.academic_year', $selectedYear)
            ->where('records.term', $selectedTerm)
            ->select(...$this->schoolListingSelectColumns($fullSelect));

        if (! in_array($type, ['all_schools', 'network', 'district', 'school_size', 'network_school_size', 'district_school_size', 'opportunity_schools', 'opportunity_school_size'], true)) {
            return collect();
        }

        if ($type === 'network') {
            $query->where(function ($inner) use ($value) {
                $inner->where('groups.name', $value)
                    ->orWhere('schools.schoolgroup', $value);
            });
        }

        if ($type === 'district') {
            $query->where('schools.amper', $value);
        }

        if ($type === 'network_school_size') {
            [$networkValue, $sizeValue] = array_pad(explode('||', $value, 2), 2, '');

            if ($networkValue === '' || $sizeValue === '') {
                return collect();
            }

            $query->where(function ($inner) use ($networkValue) {
                $inner->where('groups.name', $networkValue)
                    ->orWhere('schools.schoolgroup', $networkValue);
            });

            [$minStudents, $maxStudents] = $this->schoolSizeRange($sizeValue);

            if ($minStudents === null && $maxStudents === null) {
                return collect();
            }

            if ($minStudents !== null) {
                $query->where('records.student_total', '>=', $minStudents);
            }

            if ($maxStudents !== null) {
                $query->where('records.student_total', '<=', $maxStudents);
            }
        }

        if ($type === 'district_school_size') {
            [$districtValue, $sizeValue] = array_pad(explode('||', $value, 2), 2, '');

            if ($districtValue === '' || $sizeValue === '') {
                return collect();
            }

            $query->where('schools.amper', $districtValue);

            [$minStudents, $maxStudents] = $this->schoolSizeRange($sizeValue);

            if ($minStudents === null && $maxStudents === null) {
                return collect();
            }

            if ($minStudents !== null) {
                $query->where('records.student_total', '>=', $minStudents);
            }

            if ($maxStudents !== null) {
                $query->where('records.student_total', '<=', $maxStudents);
            }
        }

        if ($type === 'school_size') {
            [$minStudents, $maxStudents] = $this->schoolSizeRange($value);

            if ($minStudents === null && $maxStudents === null) {
                return collect();
            }

            if ($minStudents !== null) {
                $query->where('records.student_total', '>=', $minStudents);
            }

            if ($maxStudents !== null) {
                $query->where('records.student_total', '<=', $maxStudents);
            }
        }

        if (in_array($type, ['opportunity_schools', 'opportunity_school_size'], true)) {
            $rows = $query->orderByDesc('records.student_total')->get()
                ->filter(function ($row) {
                    $metrics = json_decode((string) $row->metrics, true) ?: [];

                    return $this->hasOpportunityStudents($metrics);
                });

            if ($type === 'opportunity_school_size') {
                [$minStudents, $maxStudents] = $this->schoolSizeRange($value);

                if ($minStudents === null && $maxStudents === null) {
                    return collect();
                }

                $rows = $rows->filter(function ($row) use ($minStudents, $maxStudents) {
                    $students = (int) $row->student_total;

                    if ($minStudents !== null && $students < $minStudents) {
                        return false;
                    }

                    if ($maxStudents !== null && $students > $maxStudents) {
                        return false;
                    }

                    return true;
                });
            }

            return $rows->values();
        }

        return $query->orderByDesc('records.student_total')->get();
    }

    public function getSchoolTrend(Request $request): JsonResponse
    {
        $schoolSmis = $request->string('school_smis')->toString();

        if ($schoolSmis === '') {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'school' => null,
                    'points' => [],
                    'summary' => [
                        'first' => null,
                        'latest' => null,
                        'change' => 0,
                        'changePercent' => 0,
                    ],
                ],
            ]);
        }

        $school = DB::table('system_school as schools')
            ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('schools.smis', $schoolSmis)
            ->select(
                'schools.smis',
                'schools.schoolname',
                'schools.amper',
                'schools.logo_path',
                'groups.name as schoolgroup_name'
            )
            ->first();

        if ($school) {
            $school->logo_url = SchoolLogo::url($school->logo_path ?? null);
        }

        $records = DB::table('schoolmis_records')
            ->where('school_smis', $schoolSmis)
            ->select('academic_year', 'term', 'student_total', 'room_total')
            ->orderBy('academic_year')
            ->orderByDesc('term')
            ->get();

        $points = $records
            ->groupBy('academic_year')
            ->map(function ($yearRecords) {
                $latest = $yearRecords->sortByDesc('term')->first();

                return [
                    'academic_year' => $latest->academic_year,
                    'term' => (int) $latest->term,
                    'student_total' => (int) $latest->student_total,
                    'room_total' => (int) $latest->room_total,
                ];
            })
            ->sortBy('academic_year')
            ->values();

        $firstPoint = $points->first();
        $latestPoint = $points->last();
        $change = $firstPoint && $latestPoint
            ? $latestPoint['student_total'] - $firstPoint['student_total']
            : 0;
        $changePercent = $firstPoint && (int) $firstPoint['student_total'] > 0
            ? round(($change * 100) / (int) $firstPoint['student_total'], 1)
            : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'school' => $school,
                'points' => $points,
                'summary' => [
                    'first' => $firstPoint,
                    'latest' => $latestPoint,
                    'change' => $change,
                    'changePercent' => $changePercent,
                ],
            ],
        ]);
    }

    public function getSchoolStudentDetail(Request $request): JsonResponse
    {
        $schoolSmis = $request->string('school_smis')->toString();
        $academicYear = $request->string('academic_year')->toString();
        $term = (int) $request->input('term', 0);

        if ($schoolSmis === '' || $academicYear === '' || $term === 0) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'school' => null,
                    'gradeRows' => [],
                    'levelRows' => [],
                    'summary' => $this->emptyMetricRow('รวมทั้งหมด'),
                ],
            ]);
        }

        $record = DB::table('schoolmis_records as records')
            ->leftJoin('system_school as schools', 'records.school_smis', '=', 'schools.smis')
            ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('records.school_smis', $schoolSmis)
            ->where('records.academic_year', $academicYear)
            ->where('records.term', $term)
            ->select(
                'records.school_smis',
                'records.academic_year',
                'records.term',
                'records.metrics',
                'records.male_total',
                'records.female_total',
                'records.student_total',
                'records.room_total',
                'schools.schoolname',
                'schools.amper',
                'schools.logo_path',
                'groups.name as schoolgroup_name'
            )
            ->first();

        if (! $record) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'school' => null,
                    'gradeRows' => [],
                    'levelRows' => [],
                    'summary' => $this->emptyMetricRow('รวมทั้งหมด'),
                ],
            ]);
        }

        $metrics = json_decode((string) $record->metrics, true) ?: [];

        $gradeRows = collect(self::GRADE_LABELS)
            ->map(fn ($label, $key) => $this->metricRow($key, $label, $metrics[$key] ?? null))
            ->filter(fn ($row) => $row['male'] > 0 || $row['female'] > 0 || $row['total'] > 0 || $row['rooms'] > 0)
            ->values();

        $levelRows = collect(self::LEVEL_LABELS)
            ->map(fn ($label, $key) => $this->metricRow($key, $label, $metrics[$key] ?? null))
            ->filter(fn ($row) => $row['male'] > 0 || $row['female'] > 0 || $row['total'] > 0 || $row['rooms'] > 0)
            ->values();

        $summary = $this->metricRow('all_total', 'รวมทั้งหมด', $metrics['all_total'] ?? [
            'male' => $record->male_total,
            'female' => $record->female_total,
            'total' => $record->student_total,
            'rooms' => $record->room_total,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'school' => [
                    'school_smis' => $record->school_smis,
                    'schoolname' => $record->schoolname ?: 'ไม่พบชื่อโรงเรียนในระบบ',
                    'logo_url' => SchoolLogo::url($record->logo_path ?? null),
                    'academic_year' => $record->academic_year,
                    'term' => (int) $record->term,
                    'district' => $record->amper ?: '-',
                    'network' => $record->schoolgroup_name ?: '-',
                ],
                'gradeRows' => $gradeRows,
                'levelRows' => $levelRows,
                'summary' => $summary,
            ],
        ]);
    }

    public function getSchoolInfo(Request $request): JsonResponse
    {
        $schoolSmis = $request->string('school_smis')->toString();

        if ($schoolSmis === '') {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        $school = DB::table('system_school as schools')
            ->leftJoin('system_group as groups', 'schools.schoolgroup', '=', 'groups.code')
            ->where('schools.smis', $schoolSmis)
            ->select(
                'schools.smis',
                'schools.percode',
                'schools.ministry',
                'schools.schoolname',
                'schools.schoolname_eng',
                'schools.schoolgroup',
                'groups.name as schoolgroup_name',
                'schools.logo_path',
                'schools.muti',
                'schools.road',
                'schools.muban',
                'schools.tambon',
                'schools.amper',
                'schools.province',
                'schools.postcode',
                'schools.lat',
                'schools.lng',
                'schools.length_km',
                'schools.maplink',
                'schools.tel',
                'schools.email',
                'schools.website',
                'schools.statusID',
                'schools.statusDetail'
            )
            ->first();

        if (! $school) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        $resolvedDistance = $this->schoolDistanceService->resolveAndPersistForSchool($schoolSmis);

        if ($resolvedDistance !== null) {
            $school->length_km = $resolvedDistance;
        }

        $lat = trim((string) $school->lat);
        $lng = trim((string) $school->lng);
        $maplink = trim((string) $school->maplink);

        if ($maplink === '' && $lat !== '' && $lng !== '') {
            $maplink = 'https://www.google.com/maps?q='.$lat.','.$lng;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'smis' => $school->smis,
                'percode' => $school->percode,
                'ministry' => $school->ministry,
                'schoolname' => $school->schoolname,
                'schoolname_eng' => $school->schoolname_eng,
                'logo_url' => SchoolLogo::url($school->logo_path ?? null),
                'schoolgroup' => $school->schoolgroup,
                'schoolgroup_name' => $school->schoolgroup_name,
                'muti' => $school->muti,
                'road' => $school->road,
                'muban' => $school->muban,
                'tambon' => $school->tambon,
                'amper' => $school->amper,
                'province' => $school->province,
                'postcode' => $school->postcode,
                'lat' => $lat,
                'lng' => $lng,
                'length_km' => $school->length_km,
                'maplink' => $maplink,
                'tel' => $school->tel,
                'email' => $school->email,
                'website' => $school->website,
                'statusID' => $school->statusID,
                'statusDetail' => $school->statusDetail,
                'full_address' => $this->schoolAddress($school),
            ],
        ]);
    }

    public function getStudentTrend(): JsonResponse
    {
        $points = DB::table('schoolmis_records')
            ->select(
                'academic_year',
                'term',
                DB::raw('SUM(student_total) as student_total'),
                DB::raw('SUM(room_total) as room_total'),
                DB::raw('COUNT(*) as schools_count')
            )
            ->groupBy('academic_year', 'term')
            ->orderBy('academic_year')
            ->orderBy('term')
            ->get()
            ->map(function ($row) {
                return [
                    'academic_year' => $row->academic_year,
                    'term' => (int) $row->term,
                    'student_total' => (int) $row->student_total,
                    'room_total' => (int) $row->room_total,
                    'schools_count' => (int) $row->schools_count,
                    'label' => $row->academic_year.' / '.$row->term,
                ];
            })
            ->values();

        $firstPoint = $points->first();
        $latestPoint = $points->last();
        $change = $firstPoint && $latestPoint
            ? $latestPoint['student_total'] - $firstPoint['student_total']
            : 0;
        $changePercent = $firstPoint && (int) $firstPoint['student_total'] > 0
            ? round(($change * 100) / (int) $firstPoint['student_total'], 1)
            : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'points' => $points,
                'summary' => [
                    'first' => $firstPoint,
                    'latest' => $latestPoint,
                    'change' => $change,
                    'changePercent' => $changePercent,
                ],
            ],
        ]);
    }

    public function exportSchoolsXlsx(Request $request): BinaryFileResponse
    {
        $selectedYear = $request->string('academic_year')->toString();
        $selectedTerm = (int) $request->input('term', 0);
        $type = $request->string('type')->toString();
        $value = $request->string('value')->toString();

        if ($selectedYear === '' || $selectedTerm === 0 || $type === '' || $value === '') {
            abort(422, 'กรุณาเลือกปีการศึกษา รอบข้อมูล และประเภทข้อมูลก่อนส่งออก');
        }

        $rows = $this->getSchoolListingRows($selectedYear, $selectedTerm, $type, $value, true);
        $headers = $this->schoolExportHeaders();
        $exportRows = $rows->map(fn ($row) => $this->mapSchoolExportRow($row))->all();
        $filename = $this->buildSchoolExportFilename($selectedYear, $selectedTerm, $type, $value);

        return SimpleXlsxExporter::download($filename, $headers, $exportRows);
    }

    private function schoolSizeRange(string $size): array
    {
        return match ($size) {
            'small' => [null, 119],
            'medium' => [120, 719],
            'large' => [720, 1679],
            'special' => [1680, null],
            default => [null, null],
        };
    }

    private function schoolListingSelectColumns(bool $fullSelect = false): array
    {
        if (! $fullSelect) {
            return [
                'records.school_smis',
                'records.student_total',
                'records.room_total',
                'records.metrics',
                'schools.schoolname',
                'schools.logo_path',
                'schools.amper',
                'groups.name as schoolgroup_name',
            ];
        }

        return [
            'records.academic_year',
            'records.term',
            'records.school_smis',
            'records.male_total',
            'records.female_total',
            'records.student_total',
            'records.room_total',
            'records.metrics',
            'schools.percode',
            'schools.ministry',
            'schools.schoolname',
            'schools.schoolname_eng',
            'schools.logo_path',
            'schools.schoolgroup',
            'groups.name as schoolgroup_name',
            'schools.muti',
            'schools.road',
            'schools.muban',
            'schools.tambon',
            'schools.amper',
            'schools.province',
            'schools.postcode',
            'schools.lat',
            'schools.lng',
            'schools.length_km',
            'schools.maplink',
            'schools.tel',
            'schools.email',
            'schools.website',
            'schools.statusID',
            'schools.statusDetail',
        ];
    }

    private function attachSchoolLogoUrls($rows)
    {
        return collect($rows)->map(function ($row) {
            $row->logo_url = SchoolLogo::url($row->logo_path ?? null);

            return $row;
        });
    }

    private function schoolSizeKey(int $total): string
    {
        if ($total <= 119) {
            return 'small';
        }

        if ($total <= 719) {
            return 'medium';
        }

        if ($total <= 1679) {
            return 'large';
        }

        return 'special';
    }

    private function schoolSizeLabel(string $size): string
    {
        return self::SCHOOL_SIZE_LABELS[$size] ?? $size;
    }

    private function schoolExportHeaders(): array
    {
        return [
            'ปีการศึกษา',
            'รอบ',
            'SMIS',
            'PERCODE',
            'กระทรวง',
            'ชื่อโรงเรียน',
            'ชื่อโรงเรียนอังกฤษ',
            'เครือข่าย (รหัส)',
            'เครือข่ายสถานศึกษา',
            'หมู่',
            'ถนน',
            'ตำบล',
            'อำเภอ',
            'จังหวัด',
            'รหัสไปรษณีย์',
            'ที่อยู่',
            'ระยะทาง (กม.)',
            'Latitude',
            'Longitude',
            'แผนที่',
            'โทรศัพท์',
            'อีเมล',
            'เว็บไซต์',
            'สถานะ',
            'รายละเอียดสถานะ',
            'ชายรวม',
            'หญิงรวม',
            'นักเรียนรวม',
            'ห้องเรียนรวม',
            'ขนาดโรงเรียน',
            'โรงเรียนขยายโอกาส',
            'อนุบาล 1 ชาย',
            'อนุบาล 1 หญิง',
            'อนุบาล 1 รวม',
            'อนุบาล 1 ห้อง',
            'อนุบาล 2 ชาย',
            'อนุบาล 2 หญิง',
            'อนุบาล 2 รวม',
            'อนุบาล 2 ห้อง',
            'อนุบาล 3 ชาย',
            'อนุบาล 3 หญิง',
            'อนุบาล 3 รวม',
            'อนุบาล 3 ห้อง',
            'ป.1 ชาย',
            'ป.1 หญิง',
            'ป.1 รวม',
            'ป.1 ห้อง',
            'ป.2 ชาย',
            'ป.2 หญิง',
            'ป.2 รวม',
            'ป.2 ห้อง',
            'ป.3 ชาย',
            'ป.3 หญิง',
            'ป.3 รวม',
            'ป.3 ห้อง',
            'ป.4 ชาย',
            'ป.4 หญิง',
            'ป.4 รวม',
            'ป.4 ห้อง',
            'ป.5 ชาย',
            'ป.5 หญิง',
            'ป.5 รวม',
            'ป.5 ห้อง',
            'ป.6 ชาย',
            'ป.6 หญิง',
            'ป.6 รวม',
            'ป.6 ห้อง',
            'ม.1 ชาย',
            'ม.1 หญิง',
            'ม.1 รวม',
            'ม.1 ห้อง',
            'ม.2 ชาย',
            'ม.2 หญิง',
            'ม.2 รวม',
            'ม.2 ห้อง',
            'ม.3 ชาย',
            'ม.3 หญิง',
            'ม.3 รวม',
            'ม.3 ห้อง',
            'ม.4 ชาย',
            'ม.4 หญิง',
            'ม.4 รวม',
            'ม.4 ห้อง',
            'ม.5 ชาย',
            'ม.5 หญิง',
            'ม.5 รวม',
            'ม.5 ห้อง',
            'ม.6 ชาย',
            'ม.6 หญิง',
            'ม.6 รวม',
            'ม.6 ห้อง',
            'ปวช.1 ชาย',
            'ปวช.1 หญิง',
            'ปวช.1 รวม',
            'ปวช.1 ห้อง',
            'ปวช.2 ชาย',
            'ปวช.2 หญิง',
            'ปวช.2 รวม',
            'ปวช.2 ห้อง',
            'ปวช.3 ชาย',
            'ปวช.3 หญิง',
            'ปวช.3 รวม',
            'ปวช.3 ห้อง',
            'รวมก่อนประถม ชาย',
            'รวมก่อนประถม หญิง',
            'รวมก่อนประถม รวม',
            'รวมก่อนประถม ห้อง',
            'รวมประถม ชาย',
            'รวมประถม หญิง',
            'รวมประถม รวม',
            'รวมประถม ห้อง',
            'รวมมัธยมต้น ชาย',
            'รวมมัธยมต้น หญิง',
            'รวมมัธยมต้น รวม',
            'รวมมัธยมต้น ห้อง',
            'รวมมัธยมปลาย ชาย',
            'รวมมัธยมปลาย หญิง',
            'รวมมัธยมปลาย รวม',
            'รวมมัธยมปลาย ห้อง',
            'รวมทั้งหมด ชาย',
            'รวมทั้งหมด หญิง',
            'รวมทั้งหมด รวม',
            'รวมทั้งหมด ห้อง',
        ];
    }

    private function mapSchoolExportRow(object $row): array
    {
        $metrics = json_decode((string) $row->metrics, true) ?: [];
        $schoolSize = $this->schoolSizeLabel($this->schoolSizeKey((int) $row->student_total));

        return array_merge(
            [
                $row->academic_year,
                (string) $row->term,
                $row->school_smis,
                $row->percode,
                $row->ministry,
                $row->schoolname ?: 'ไม่พบชื่อโรงเรียนในระบบ',
                $row->schoolname_eng,
                $row->schoolgroup,
                $row->schoolgroup_name ?: $row->schoolgroup,
                $row->muban,
                $row->road,
                $row->tambon,
                $row->amper,
                $row->province,
                $row->postcode,
                $this->schoolAddress($row),
                $row->length_km,
                trim((string) $row->lat),
                trim((string) $row->lng),
                trim((string) $row->maplink),
                $row->tel,
                $row->email,
                $row->website,
                $row->statusID,
                $row->statusDetail,
                (string) $row->male_total,
                (string) $row->female_total,
                (string) $row->student_total,
                (string) $row->room_total,
                $schoolSize,
                $this->hasOpportunityStudents($metrics) ? 'ใช่' : 'ไม่ใช่',
            ],
            $this->metricExportValues($metrics, [
                'k1', 'k2', 'k3',
                'p1', 'p2', 'p3', 'p4', 'p5', 'p6',
                'm1', 'm2', 'm3', 'm4', 'm5', 'm6',
                'voc1', 'voc2', 'voc3',
                'pre_primary_total',
                'primary_total',
                'lower_secondary_total',
                'upper_secondary_total',
                'all_total',
            ])
        );
    }

    private function metricExportValues(array $metrics, array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $metric = $metrics[$key] ?? [];
            $values[] = (string) ((int) ($metric['male'] ?? 0));
            $values[] = (string) ((int) ($metric['female'] ?? 0));
            $values[] = (string) ((int) ($metric['total'] ?? 0));
            $values[] = (string) ((int) ($metric['rooms'] ?? 0));
        }

        return $values;
    }

    private function buildSchoolExportFilename(string $year, int $term, string $type, string $value): string
    {
        $typeLabel = match ($type) {
            'all_schools' => 'all-schools',
            'school_size' => 'school-size-'.$value,
            'network_school_size' => 'network-school-size-'.$value,
            'district_school_size' => 'district-school-size-'.$value,
            'opportunity_schools' => 'opportunity-schools',
            'opportunity_school_size' => 'opportunity-school-size-'.$value,
            'network' => 'network-'.$value,
            'district' => 'district-'.$value,
            default => $type,
        };

        $safeType = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $typeLabel) ?: 'schools';

        return 'schools_'.$year.'_term'.$term.'_'.$safeType.'.xlsx';
    }

    private function networkSortKey(string $label): string
    {
        $normalized = preg_replace('/\s+/u', '', trim($label)) ?? $label;

        if (preg_match('/^เมือง(?:ชุมพร)?(\d+)$/u', $normalized, $matches)) {
            return '1-'.str_pad((string) $matches[1], 2, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^ท่าแซะ(\d+)$/u', $normalized, $matches)) {
            return '2-'.str_pad((string) $matches[1], 2, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^ปะทิว(\d+)$/u', $normalized, $matches)) {
            return '3-'.str_pad((string) $matches[1], 2, '0', STR_PAD_LEFT);
        }

        return '9-'.$normalized;
    }

    private function hasOpportunityStudents(array $metrics): bool
    {
        return (int) data_get($metrics, 'm3.total', 0) > 0
            || (int) data_get($metrics, 'm4.total', 0) > 0
            || (int) data_get($metrics, 'm5.total', 0) > 0
            || (int) data_get($metrics, 'm6.total', 0) > 0;
    }

    private function metricRow(string $key, string $label, ?array $metric): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'male' => (int) ($metric['male'] ?? 0),
            'female' => (int) ($metric['female'] ?? 0),
            'total' => (int) ($metric['total'] ?? 0),
            'rooms' => (int) ($metric['rooms'] ?? 0),
        ];
    }

    private function emptyMetricRow(string $label): array
    {
        return [
            'key' => 'all_total',
            'label' => $label,
            'male' => 0,
            'female' => 0,
            'total' => 0,
            'rooms' => 0,
        ];
    }

    private function schoolAddress(object $school): string
    {
        $parts = array_filter([
            $school->muban ? 'หมู่ '.$school->muban : null,
            $school->road ? 'ถ.'.$school->road : null,
            $school->tambon ? 'ต.'.$school->tambon : null,
            $school->amper ? 'อ.'.$school->amper : null,
            $school->province ? 'จ.'.$school->province : null,
            $school->postcode ?: null,
        ]);

        return implode(' ', $parts);
    }
}
