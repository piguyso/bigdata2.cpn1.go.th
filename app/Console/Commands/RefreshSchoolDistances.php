<?php

namespace App\Console\Commands;

use App\Services\SchoolDistanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshSchoolDistances extends Command
{
    protected $signature = 'schools:refresh-distance
                            {--smis= : คำนวณเฉพาะโรงเรียนรหัส SMIS ที่ระบุ}
                            {--force : คำนวณใหม่แม้มีค่า length_km อยู่แล้ว}
                            {--sleep=150 : หน่วงเวลาแต่ละคำขอเป็นมิลลิวินาที}';

    protected $description = 'คำนวณและบันทึกระยะทางขับรถจากโรงเรียนไปยังเขตพื้นที่ลง system_school.length_km';

    public function handle(SchoolDistanceService $distanceService): int
    {
        $schoolSmis = trim((string) $this->option('smis'));
        $force = (bool) $this->option('force');
        $sleepMs = max(0, (int) $this->option('sleep'));

        $query = DB::table('system_school')
            ->select('smis', 'schoolname', 'lat', 'lng', 'length_km')
            ->where('lat', '<>', '')
            ->where('lng', '<>', '');

        if ($schoolSmis !== '') {
            $query->where('smis', $schoolSmis);
        }

        if (! $force) {
            $query->where(function ($builder) {
                $builder->whereNull('length_km')->orWhere('length_km', '');
            });
        }

        $schools = $query
            ->orderBy('schoolgroup')
            ->orderBy('schoolname')
            ->get();

        if ($schools->isEmpty()) {
            $this->info('ไม่พบโรงเรียนที่ต้องอัปเดตระยะทาง');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($schools as $school) {
            $distance = $distanceService->resolveAndPersistForSchool((string) $school->smis, $force);

            if ($distance === null) {
                $skipped++;
                $this->warn('ข้าม: '.$school->smis.' '.$school->schoolname);
            } else {
                $updated++;
                $this->line('อัปเดต: '.$school->smis.' '.$school->schoolname.' = '.$distance.' กม.');
            }

            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        $this->newLine();
        $this->info('อัปเดตสำเร็จ '.$updated.' โรงเรียน');

        if ($skipped > 0) {
            $this->warn('ข้าม/คำนวณไม่ได้ '.$skipped.' โรงเรียน');
        }

        return self::SUCCESS;
    }
}
