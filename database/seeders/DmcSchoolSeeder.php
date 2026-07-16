<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use XMLReader;

class DmcSchoolSeeder extends Seeder
{
    /**
     * Path to the local DMC XLSX file.
     * Place DMC691.xlsx in the project root (same level as composer.json).
     */
    private const XLSX_PATH = 'DMC691.xlsx';

    /**
     * Column positions in DMC XLSX (0-indexed based on actual file structure)
     */
    private const COL = [
        'school_type'       => 1,   // ประเภท
        'region'            => 2,   // ภาค
        'areacode'          => 3,   // areacode (8 หลัก)
        'smis'              => 4,   // smis
        'ministry'          => 5,   // รหัส 10 หลัก
        'schoolname'        => 6,   // ชื่อโรงเรียน
        'amper'             => 7,   // ชื่ออำเภอ
        'province'          => 8,   // ชื่อจังหวัด
        'area_name'         => 9,   // ชื่อเขต
        'muban'             => 10,  // หมู่
        'tambon'            => 11,  // ชื่อหมู่บ้าน
        'muban2'            => 12,  // ชื่อตำบล
        'postcode'          => 13,  // ไปรษณีย์
        'tel'               => 14,  // โทรศัพท์
        'lat'               => 71,  // lat
        'lng'               => 72,  // long
        'size_criteria'     => 101, // เกณฑ์7ขนาด
        'expand_opportunity'=> 105, // ขยายโอกาส
        'total_rooms'       => 99,  // รวมห้อง
        'total_students'    => 100, // นักเรียนทั้งหมด
    ];

    public function run(): void
    {
        $xlsxPath = base_path(self::XLSX_PATH);

        if (! file_exists($xlsxPath)) {
            $this->command->warn('⚠  ไม่พบไฟล์ ' . self::XLSX_PATH . ' ที่ ' . $xlsxPath);
            $this->command->warn('   ข้ามการนำเข้า dmc_schools — วางไฟล์ DMC XLSX ไว้ที่ root โปรเจกต์แล้วรัน seeder ใหม่');
            return;
        }

        $this->command->info('📂 เปิดไฟล์ DMC XLSX: ' . basename($xlsxPath));

        // Raise limits for large file processing
        $origMem  = ini_get('memory_limit');
        $origTime = ini_get('max_execution_time');
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            // Extract XLSX contents to temp dir
            $zip = new ZipArchive();
            if ($zip->open($xlsxPath) !== true) {
                $this->command->error('ไม่สามารถเปิดไฟล์ XLSX ได้');
                return;
            }

            $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dmc_seed_' . uniqid();
            mkdir($tmpDir, 0700, true);
            $zip->extractTo($tmpDir, ['xl/sharedStrings.xml', 'xl/worksheets/sheet1.xml']);
            $zip->close();

            // Read shared strings
            $sharedStrings = $this->readSharedStrings($tmpDir . '/xl/sharedStrings.xml');
            $this->command->info('✓ โหลด shared strings: ' . count($sharedStrings) . ' รายการ');

            // Truncate existing data
            DB::statement('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
            DB::table('dmc_schools')->truncate();
            $this->command->info('✓ ล้างข้อมูลเดิมใน dmc_schools แล้ว');

            // Stream rows and batch insert
            $this->streamAndInsert(
                $tmpDir . '/xl/worksheets/sheet1.xml',
                $sharedStrings
            );

            // Cleanup temp files
            $this->cleanupTemp($tmpDir);

        } finally {
            ini_set('memory_limit', $origMem);
            set_time_limit((int) $origTime);
        }
    }

    private function streamAndInsert(string $sheetPath, array $sharedStrings): void
    {
        $reader = new XMLReader();
        $reader->open($sheetPath);

        $batch       = [];
        $batchSize   = 500;
        $rowIndex    = 0;
        $imported    = 0;

        $inRow = false; $inCell = false;
        $cellRef = ''; $cellType = ''; $cellValue = '';
        $currentRowCells = [];

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                if ($reader->localName === 'row') {
                    $inRow = true;
                    $currentRowCells = [];
                } elseif ($reader->localName === 'c' && $inRow) {
                    $inCell   = true;
                    $cellRef  = (string) ($reader->getAttribute('r') ?? '');
                    $cellType = (string) ($reader->getAttribute('t') ?? '');
                    $cellValue = '';
                }
            } elseif ($reader->nodeType === XMLReader::TEXT && $inCell) {
                $cellValue .= $reader->value;
            } elseif ($reader->nodeType === XMLReader::END_ELEMENT) {
                if ($reader->localName === 'c' && $inCell) {
                    $colLetter = preg_replace('/[0-9]/', '', $cellRef);
                    $colIndex  = $this->colLetterToIndex($colLetter);
                    $value     = $cellType === 's'
                        ? ($sharedStrings[(int) $cellValue] ?? '')
                        : $cellValue;
                    $currentRowCells[$colIndex] = $value;
                    $inCell = false; $cellValue = ''; $cellRef = ''; $cellType = '';
                } elseif ($reader->localName === 'row') {
                    $rowIndex++;

                    if ($rowIndex === 1) {
                        // Skip header row
                        $inRow = false;
                        continue;
                    }

                    if (! empty(array_filter($currentRowCells))) {
                        $c = self::COL;
                        $batch[] = [
                            'areacode'          => mb_substr(trim($currentRowCells[$c['areacode']] ?? ''), 0, 20, 'UTF-8'),
                            'smis'              => mb_substr(trim($currentRowCells[$c['smis']] ?? ''), 0, 20, 'UTF-8'),
                            'ministry'          => mb_substr(trim($currentRowCells[$c['ministry']] ?? ''), 0, 20, 'UTF-8'),
                            'schoolname'        => mb_substr(trim($currentRowCells[$c['schoolname']] ?? ''), 0, 500, 'UTF-8'),
                            'area_name'         => mb_substr(trim($currentRowCells[$c['area_name']] ?? ''), 0, 255, 'UTF-8'),
                            'amper'             => mb_substr(trim($currentRowCells[$c['amper']] ?? ''), 0, 100, 'UTF-8'),
                            'province'          => mb_substr(trim($currentRowCells[$c['province']] ?? ''), 0, 100, 'UTF-8'),
                            'muban'             => mb_substr(trim($currentRowCells[$c['muban']] ?? ''), 0, 100, 'UTF-8'),
                            'tambon'            => mb_substr(trim($currentRowCells[$c['tambon']] ?? ''), 0, 100, 'UTF-8'),
                            'muban2'            => mb_substr(trim($currentRowCells[$c['muban2']] ?? ''), 0, 100, 'UTF-8'),
                            'postcode'          => mb_substr(trim($currentRowCells[$c['postcode']] ?? ''), 0, 20, 'UTF-8'),
                            'tel'               => mb_substr(trim($currentRowCells[$c['tel']] ?? ''), 0, 50, 'UTF-8'),
                            'lat'               => mb_substr(trim($currentRowCells[$c['lat']] ?? ''), 0, 30, 'UTF-8'),
                            'lng'               => mb_substr(trim($currentRowCells[$c['lng']] ?? ''), 0, 30, 'UTF-8'),
                            'school_type'       => mb_substr(trim($currentRowCells[$c['school_type']] ?? ''), 0, 20, 'UTF-8'),
                            'region'            => mb_substr(trim($currentRowCells[$c['region']] ?? ''), 0, 50, 'UTF-8'),
                            'size_criteria'     => mb_substr(trim($currentRowCells[$c['size_criteria']] ?? ''), 0, 30, 'UTF-8'),
                            'expand_opportunity'=> mb_substr(trim($currentRowCells[$c['expand_opportunity']] ?? ''), 0, 10, 'UTF-8'),
                            'total_students'    => (int) ($currentRowCells[$c['total_students']] ?? 0),
                            'total_rooms'       => (int) ($currentRowCells[$c['total_rooms']] ?? 0),
                        ];

                        if (count($batch) >= $batchSize) {
                            DB::table('dmc_schools')->insert($batch);
                            $imported += count($batch);
                            $batch = [];
                            $this->command->line("  → นำเข้าแล้ว {$imported} รายการ...");
                        }
                    }

                    $inRow = false;
                }
            }
        }

        $reader->close();

        // Insert remaining batch
        if (! empty($batch)) {
            DB::table('dmc_schools')->insert($batch);
            $imported += count($batch);
        }

        $this->command->info("✅ นำเข้า dmc_schools สำเร็จ: {$imported} โรงเรียน");
    }

    private function readSharedStrings(string $path): array
    {
        $sharedStrings = [];
        if (! file_exists($path)) {
            return $sharedStrings;
        }

        $reader = new XMLReader();
        $reader->open($path);
        $current = ''; $inSi = false; $inT = false;

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                if ($reader->localName === 'si') { $inSi = true; $current = ''; }
                elseif ($reader->localName === 't' && $inSi) { $inT = true; }
            } elseif ($reader->nodeType === XMLReader::TEXT && $inT) {
                $current .= $reader->value;
            } elseif ($reader->nodeType === XMLReader::END_ELEMENT) {
                if ($reader->localName === 't') { $inT = false; }
                elseif ($reader->localName === 'si') {
                    $sharedStrings[] = $current;
                    $inSi = false; $current = '';
                }
            }
        }

        $reader->close();
        return $sharedStrings;
    }

    private function colLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $index = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function cleanupTemp(string $tmpDir): void
    {
        foreach (glob($tmpDir . '/xl/worksheets/*') as $f) { @unlink($f); }
        foreach (glob($tmpDir . '/xl/*') as $f) { if (is_file($f)) @unlink($f); }
        @rmdir($tmpDir . '/xl/worksheets');
        @rmdir($tmpDir . '/xl');
        @rmdir($tmpDir);
    }
}
