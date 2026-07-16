<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class HrmsOpenDataService
{
    private const DATASET_IDS = [18, 23, 25, 26, 27];

    private const DEFAULT_AREA = 'สพป.ชุมพร เขต 1';

    private const DATASET_18_LABELS = [
        'countGroupPrathomwai' => 'ปฐมวัย',
        'countGroupPrathomsuksa' => 'ประถมศึกษา',
        'countGroupThai' => 'ภาษาไทย',
        'countGroupMath' => 'คณิตศาสตร์',
        'countGroupScience01' => 'วิทยาศาสตร์ทั่วไป',
        'countGroupScience011' => 'ฟิสิกส์',
        'countGroupScience02' => 'เคมี',
        'countGroupScience03' => 'ชีววิทยา',
        'countGroupScience04' => 'โลก ดาราศาสตร์ และอวกาศ',
        'countGroupScience05' => 'วิทยาศาสตร์อื่น ๆ',
        'countGroupSocial' => 'สังคมศึกษา',
        'countGroupHealth01' => 'สุขศึกษา',
        'countGroupHealth02' => 'พลศึกษา',
        'countGroupHealth03' => 'สุขศึกษาและพลศึกษาอื่น ๆ',
        'countGroupArt01' => 'ทัศนศิลป์',
        'countGroupArt02' => 'ดนตรี',
        'countGroupArt03' => 'นาฏศิลป์',
        'countGroupArt04' => 'ศิลปะอื่น ๆ',
        'countGroupArt05' => 'ศิลปศึกษา',
        'countGroupCareer01' => 'เกษตร',
        'countGroupCareer02' => 'คหกรรม',
        'countGroupCareer03' => 'อุตสาหกรรม',
        'countGroupCareer04' => 'พาณิชยกรรม',
        'countGroupCareer05' => 'คอมพิวเตอร์',
        'countGroupCareer06' => 'การงานอาชีพอื่น ๆ',
        'countGroupForeign01' => 'ภาษาอังกฤษ',
        'countGroupForeign02' => 'ภาษาจีน',
        'countGroupForeign03' => 'ภาษาญี่ปุ่น',
        'countGroupForeign04' => 'ภาษาเกาหลี',
        'countGroupForeign05' => 'ภาษาฝรั่งเศส',
        'countGroupForeign06' => 'ภาษาเมียนมา',
        'countGroupForeign07' => 'ภาษาเวียดนาม',
        'countGroupForeign08' => 'ภาษาเขมร',
        'countGroupForeign09' => 'ภาษามลายู',
        'countGroupForeign10' => 'ภาษาอาหรับ',
        'countGroupForeign11' => 'ภาษาสเปน',
        'countGroupForeign12' => 'ภาษาเยอรมัน',
        'countGroupForeign13' => 'ภาษาต่างประเทศอื่น ๆ',
        'countGroupForActivity01' => 'กิจกรรมพัฒนาผู้เรียน',
        'countGroupForActivity02' => 'แนะแนว',
        'countGroupOther01' => 'บรรณารักษ์',
        'countGroupOther02' => 'โสตทัศนศึกษา',
        'countGroupOther03' => 'การศึกษาพิเศษ',
    ];

    private const DATASET_23_LABELS = [
        'countMajorG01Prathomwai' => 'ปฐมวัย',
        'countMajorG02Prathomsuksa' => 'ประถมศึกษา',
        'countMajorG03Thai' => 'ภาษาไทย',
        'countMajorG04Math' => 'คณิตศาสตร์',
        'countMajorG05English' => 'ภาษาอังกฤษ',
        'countMajorG06Social' => 'สังคมศึกษา',
        'countMajorG07Science' => 'วิทยาศาสตร์',
        'chemical' => 'เคมี',
        'biology' => 'ชีววิทยา',
        'physics' => 'ฟิสิกส์',
        'countMajorG08Health01' => 'สุขศึกษา',
        'countMajorG08Health02' => 'พลศึกษา',
        'countMajorG09Art' => 'ศิลปะ',
        'musiceducation' => 'ดนตรีศึกษา',
        'music' => 'ดนตรี',
        'Dance' => 'นาฏศิลป์',
        'computer' => 'คอมพิวเตอร์',
        'homeeconomics' => 'คหกรรม',
        'Agriculture' => 'เกษตร',
        'industry' => 'อุตสาหกรรม',
        'librarian' => 'บรรณารักษ์',
        'AudiovisualEducation' => 'โสตทัศนศึกษา',
        'countMajorG11Foreign01' => 'ภาษาจีน',
        'countMajorG11Foreign02' => 'ภาษาญี่ปุ่น',
        'countMajorG11Foreign03' => 'ภาษาเกาหลี',
        'countMajorG11Foreign04' => 'ภาษาฝรั่งเศส',
        'countMajorG11Foreign05' => 'ภาษาเมียนมา',
        'countMajorG11Foreign06' => 'ภาษาเวียดนาม',
        'countMajorG11Foreign07' => 'ภาษาเขมร',
        'countMajorG11Foreign08' => 'ภาษาต่างประเทศอื่น ๆ',
        'Guidancepsychology' => 'แนะแนว/จิตวิทยา',
        'Burmeselanguage' => 'ภาษาเมียนมา',
        'Vietnameselanguage' => 'ภาษาเวียดนาม',
        'Khmerlanguage' => 'ภาษาเขมร',
        'Commerce/BusinessAdministration' => 'พาณิชยกรรม/บริหารธุรกิจ',
        'specialeducation' => 'การศึกษาพิเศษ',
    ];

    private const DATASET_25_LABELS = [
        'Basicservices' => 'บริการพื้นฐาน',
        'support' => 'สายสนับสนุน',
        'mechanic' => 'ช่าง/เทคนิค',
        'total' => 'รวมทั้งหมด',
    ];

    private const DATASET_27_LABELS = [
        'lessTeacherTpye017' => 'จำนวนขาดแคลนครูรวม',
        'lessTeacherTpye01' => 'ขาดแคลนครูประจำการ',
        'lessTeacherTpye016' => 'ขาดแคลนครูเฉพาะทาง',
        'lessPerson014' => 'ขาดแคลนบุคลากรสนับสนุน',
        'teacherSciAndMath' => 'ครูวิทย์-คณิต',
        'turakan15000' => 'ธุรการอัตรา 15,000',
        'turakan9000' => 'ธุรการอัตรา 9,000',
        'teacherSchoolSpacial04' => 'โรงเรียนเฉพาะด้านกลุ่ม 4',
        'teacherSchoolSpacial06' => 'โรงเรียนเฉพาะด้านกลุ่ม 6',
        'teacherSchoolSpacial07' => 'โรงเรียนเฉพาะด้านกลุ่ม 7',
        'teacherSchoolSpacial09' => 'โรงเรียนเฉพาะด้านกลุ่ม 9',
        'personEmp' => 'บุคลากรรวมในชุดนี้',
        'totalAll' => 'รวมตัวชี้วัดทั้งหมด',
    ];

    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function getDashboardPayload(?string $area = null): array
    {
        $datasets = $this->fetchDatasets();
        $selectedArea = $this->resolveArea($area, $this->extractAreas($datasets));

        return [
            'selectedArea' => $selectedArea,
            'fetchedAt' => now()->toIso8601String(),
            'overview' => $this->buildOverview($datasets, $selectedArea),
            'majorSummary' => $this->buildKeyedSummary($this->findAreaRow($datasets[23] ?? [], $selectedArea), self::DATASET_23_LABELS, ['No', 'areaName', 'AreaName', 'SumAll']),
            'groupSummary' => $this->buildKeyedSummary($this->findAreaRow($datasets[18] ?? [], $selectedArea), self::DATASET_18_LABELS, ['No', 'areaName', 'AreaName', 'Total']),
            'supportSummary' => $this->buildKeyedSummary($this->findAreaRow($datasets[25] ?? [], $selectedArea), self::DATASET_25_LABELS, ['No', 'no', 'areaName', 'AreaName']),
            'indicatorSummary' => $this->buildKeyedSummary($this->findAreaRow($datasets[27] ?? [], $selectedArea), self::DATASET_27_LABELS, ['No', 'areaName', 'AreaName'], 12),
            'rawCodeSummary' => [
                'dataset26' => $this->buildRawCodeSummary($this->findAreaRow($datasets[26] ?? [], $selectedArea), ['No', 'areaName', 'AreaName']),
                'dataset27' => $this->buildRawCodeSummary($this->findAreaRow($datasets[27] ?? [], $selectedArea), ['No', 'areaName', 'AreaName']),
            ],
        ];
    }

    private function fetchDatasets(): array
    {
        $config = config('services.hrms_opendata');
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $timeout = max(5, (int) ($config['timeout'] ?? 20));
        $cacheMinutes = max(1, (int) ($config['cache_minutes'] ?? 15));

        if ($baseUrl === '') {
            throw new RuntimeException('ยังไม่ได้ตั้งค่า HRMS opendata base URL');
        }

        return Cache::remember('hrms_opendata_personnel_dashboard', now()->addMinutes($cacheMinutes), function () use ($baseUrl, $timeout) {
            $datasets = [];

            foreach (self::DATASET_IDS as $datasetId) {
                $response = $this->http
                    ->withoutVerifying()
                    ->acceptJson()
                    ->timeout($timeout)
                    ->get($baseUrl.'/dataset_personal/'.$datasetId)
                    ->throw();

                $payload = $response->json();

                if (! is_array($payload)) {
                    throw new RuntimeException('รูปแบบข้อมูล HRMS opendata ไม่ถูกต้องที่ dataset '.$datasetId);
                }

                $datasets[$datasetId] = $payload;
            }

            return $datasets;
        });
    }

    private function extractAreas(array $datasets): array
    {
        return collect($datasets[23] ?? [])
            ->map(fn (array $row) => $this->extractAreaName($row))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveArea(?string $area, array $areas): ?string
    {
        if (in_array(self::DEFAULT_AREA, $areas, true)) {
            return self::DEFAULT_AREA;
        }

        return $areas[0] ?? null;
    }

    private function buildOverview(array $datasets, ?string $area): array
    {
        $dataset18 = $this->findAreaRow($datasets[18] ?? [], $area);
        $dataset23 = $this->findAreaRow($datasets[23] ?? [], $area);
        $dataset25 = $this->findAreaRow($datasets[25] ?? [], $area);
        $dataset27 = $this->findAreaRow($datasets[27] ?? [], $area);

        return [
            [
                'label' => 'บุคลากรตามกลุ่มวิชา',
                'value' => (int) ($dataset18['Total'] ?? 0),
                'icon' => 'fa-solid fa-users-viewfinder',
                'iconBg' => 'bg-orange-50 text-orange-500',
                'note' => 'จาก dataset 18',
            ],
            [
                'label' => 'บุคลากรตามวิชาเอก',
                'value' => (int) ($dataset23['SumAll'] ?? 0),
                'icon' => 'fa-solid fa-graduation-cap',
                'iconBg' => 'bg-sky-50 text-sky-500',
                'note' => 'จาก dataset 23',
            ],
            [
                'label' => 'บุคลากรสายสนับสนุน',
                'value' => (int) ($dataset25['total'] ?? 0),
                'icon' => 'fa-solid fa-briefcase',
                'iconBg' => 'bg-emerald-50 text-emerald-500',
                'note' => 'จาก dataset 25',
            ],
            [
                'label' => 'ตัวชี้วัดรวม HRMS',
                'value' => (int) ($dataset27['totalAll'] ?? 0),
                'icon' => 'fa-solid fa-chart-simple',
                'iconBg' => 'bg-violet-50 text-violet-500',
                'note' => 'จาก dataset 27',
            ],
        ];
    }

    private function buildKeyedSummary(?array $row, array $labels, array $excludeKeys = [], int $limit = 10): array
    {
        if ($row === null) {
            return [];
        }

        return collect($row)
            ->reject(fn ($value, $key) => in_array($key, $excludeKeys, true))
            ->map(function ($value, $key) use ($labels) {
                $number = is_numeric($value) ? (int) $value : 0;

                return [
                    'key' => (string) $key,
                    'label' => $labels[$key] ?? (string) $key,
                    'value' => $number,
                ];
            })
            ->filter(fn (array $item) => $item['value'] > 0)
            ->sortByDesc('value')
            ->take($limit)
            ->values()
            ->all();
    }

    private function buildRawCodeSummary(?array $row, array $excludeKeys = []): array
    {
        if ($row === null) {
            return [];
        }

        return collect($row)
            ->reject(fn ($value, $key) => in_array($key, $excludeKeys, true))
            ->map(fn ($value, $key) => [
                'key' => (string) $key,
                'value' => is_numeric($value) ? (int) $value : 0,
            ])
            ->filter(fn (array $item) => $item['value'] > 0)
            ->sortByDesc('value')
            ->take(12)
            ->values()
            ->all();
    }

    private function findAreaRow(array $rows, ?string $area): ?array
    {
        if ($area === null) {
            return null;
        }

        return collect($rows)
            ->first(fn (array $row) => $this->extractAreaName($row) === $area);
    }

    private function extractAreaName(array $row): ?string
    {
        $areaName = trim((string) ($row['areaName'] ?? $row['AreaName'] ?? ''));

        return $areaName !== '' ? $areaName : null;
    }
}
