<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\StudentDataImportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('student-data:import-bigdata {path=BIGDATA}', function (StudentDataImportService $service) {
    $basePath = base_path($this->argument('path'));
    if (! is_dir($basePath)) {
        $this->error('ไม่พบโฟลเดอร์ BIGDATA: ' . $basePath);

        return self::FAILURE;
    }

    $map = [
        'จำนวนนักเรียนขาดแคลน' => 'shortage',
        'จำนวนนักเรียนจำแนกตามอายุ' => 'age',
        'จำนวนนักเรียนด้อยโอกาส' => 'disadvantaged',
        'จำนวนนักเรียนพิการและด้อยโอกาส' => 'disabled_disadvantaged',
        'จำนวนนักเรียนพิการ' => 'disabled',
        'จำนวนนักเรียนที่อยู่ห่างเกิน 3 กม.จำแนกตามการเดินทาง' => 'distance_transport',
        'จำนวนนักเรียนพักนอน' => 'boarding',
        'จำนวนนักเรียนจำหน่าย' => 'disposal',
        'จำนวนนักเรียนออกกลางคัน' => 'dropout',
        'จำนวนนักเรียนแยกตามศาสนา' => 'religion',
        'จำนวนนักเรียนแยกตามสัญชาติ' => 'nationality',
        'จำนวนนักเรียนไม่ผ่านการตัดสินการประเมินปลายปีจำแนกตามเกณฑ์รายชั้น' => 'failed_assessment',
        'ตารางตรวจพินิจข้อมูลนักเรียน' => 'inspection',
        'ภาวะโภชนาการ น้ำหนักตามเกณฑ์ส่วนสูง' => 'nutrition_weight_height',
        'ภาวะโภชนาการ ส่วนสูงตามเกณฑ์อายุ' => 'nutrition_height_age',
        'จำนวนนักเรียนรหัส G' => 'g_code',
        'จำนวนนักเรียนจบ ป.6 ศึกษาต่อจำแนกตามเพศ สังกัด' => 'graduate_p6_continue',
        'จำนวนนักเรียนจบการศึกษา ม.3 ศึกษาต่อ.ไม่ศึกษาต่อ' => 'graduate_m3_continue',
        'จำนวนนักเรียนจบการศึกษา ม.6 ศึกษาต่อ.ไม่ศึกษาต่อ' => 'graduate_m6_continue',
        'จำนวนนักเรียนจบชั้นอนุบาล, ป.3, ป.6, ม.3, ม.6 และปวช. จำแนกตามเวลาที่ใช้เรียน' => 'graduate_duration',
        'จำนวนโรงเรียนที่มีไฟฟ้า-ไม่มีไฟฟ้า' => 'electricity',
        'จำนวนโรงเรียนที่มีไฟฟ้า.ไม่มีไฟฟ้า' => 'electricity',
        'จำนวนโรงเรียนที่ใช้น้ำประปา' => 'water',
        'จำนวนโรงเรียนที่ใช้อินเทอร์เน็ต' => 'internet',
    ];

    $ok = 0;
    $failed = 0;
    $skipped = 0;
    $importedRows = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));

    foreach ($iterator as $file) {
        if (! $file->isFile() || strtolower($file->getExtension()) !== 'csv') {
            continue;
        }

        $baseName = $file->getBasename('.csv');
        if (! preg_match('/^(\d{4})-(\d)-(.+)$/u', $baseName, $matches)) {
            $failed++;
            $this->warn('ข้ามไฟล์ชื่อไม่ตรงรูปแบบ: ' . $file->getFilename());
            continue;
        }

        if ($matches[3] === 'จำนวนนักเรียนแยกชั้น,เพศ') {
            $skipped++;
            $this->line('SKIP ' . $file->getFilename() . ': ใช้ข้อมูล SchoolMIS เดิม');
            continue;
        }

        $dataType = $map[$matches[3]] ?? null;
        if (! $dataType) {
            $failed++;
            $this->warn('ไม่รู้จักชนิดข้อมูล: ' . $file->getFilename());
            continue;
        }

        try {
            $result = $service->import($file->getPathname(), $file->getFilename(), [
                'academic_year' => $matches[1],
                'term' => $matches[2],
                'data_type' => $dataType,
                'source_filename' => $file->getFilename(),
                'created_by' => null,
            ]);
            $ok++;
            $importedRows += $result['imported_rows'];
            $this->line($matches[1] . '-' . $matches[2] . ' ' . $dataType . ' = ' . $result['imported_rows']);
        } catch (Throwable $e) {
            $failed++;
            $this->warn('FAIL ' . $file->getFilename() . ': ' . $e->getMessage());
        }
    }

    $this->info('นำเข้าสำเร็จ ' . $ok . ' ไฟล์, ข้าม ' . $skipped . ' ไฟล์, ไม่ผ่าน ' . $failed . ' ไฟล์, รวม ' . $importedRows . ' รายการ');

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Import student dashboard data from BIGDATA CSV files');
