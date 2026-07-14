<?php

namespace App\Console\Commands;

use App\Services\ObecSafetySchoolService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SyncObecSchoolCoordinates extends Command
{
    protected $signature = 'schools:sync-osds-coordinates
                            {--dry-run : แสดงผลที่พบโดยยังไม่บันทึกลงฐานข้อมูล}
                            {--refresh-distance : คำนวณระยะทางใหม่หลังอัปเดตพิกัด}
                            {--distance-sleep=150 : หน่วงเวลาแต่ละคำขอคำนวณระยะทางเป็นมิลลิวินาที}';

    protected $description = 'อัปเดตพิกัดโรงเรียนจากระบบ OSDS ของ สพฐ. โดยจับคู่ผ่าน system_school.ministry';

    public function handle(ObecSafetySchoolService $obecSafetySchoolService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $refreshDistance = (bool) $this->option('refresh-distance');
        $distanceSleep = max(0, (int) $this->option('distance-sleep'));

        $remoteSchools = $obecSafetySchoolService->fetchSchools()->keyBy('ministry');
        $localSchools = DB::table('system_school')
            ->select('id', 'smis', 'ministry', 'schoolname', 'lat', 'lng')
            ->orderBy('schoolgroup')
            ->orderBy('schoolname')
            ->get();

        if ($remoteSchools->isEmpty()) {
            $this->error('ไม่พบข้อมูลโรงเรียนจากระบบ OSDS');

            return self::FAILURE;
        }

        $updated = 0;
        $unchanged = 0;
        $unmatched = 0;

        foreach ($localSchools as $school) {
            $remote = $remoteSchools->get(trim((string) $school->ministry));

            if (! is_array($remote)) {
                $unmatched++;
                $this->warn('ไม่พบข้อมูล OSDS: '.$school->smis.' '.$school->schoolname.' ['.$school->ministry.']');
                continue;
            }

            $newLatitude = $remote['latitude'];
            $newLongitude = $remote['longitude'];
            $currentLatitude = $this->normalizeValue($school->lat);
            $currentLongitude = $this->normalizeValue($school->lng);

            if ($newLatitude === null || $newLongitude === null) {
                $unmatched++;
                $this->warn('OSDS ไม่มีพิกัดที่ใช้ได้: '.$school->smis.' '.$school->schoolname);
                continue;
            }

            if ($currentLatitude === $newLatitude && $currentLongitude === $newLongitude) {
                $unchanged++;
                continue;
            }

            $updated++;
            $this->line('อัปเดตพิกัด: '.$school->smis.' '.$school->schoolname.' => '.$newLatitude.', '.$newLongitude);

            if ($dryRun) {
                continue;
            }

            DB::table('system_school')
                ->where('id', $school->id)
                ->update([
                    'lat' => $newLatitude,
                    'lng' => $newLongitude,
                    'maplink' => $remote['maplink'] ?? '',
                    'length_km' => '',
                ]);
        }

        $remoteOnly = $remoteSchools
            ->keys()
            ->diff($localSchools->pluck('ministry')->map(fn ($value) => trim((string) $value)));

        $this->newLine();
        $this->info('ข้อมูล OSDS: '.$remoteSchools->count().' โรงเรียน');
        $this->info('อัปเดตพิกัด: '.$updated.' โรงเรียน');
        $this->info('พิกัดเดิมตรงอยู่แล้ว: '.$unchanged.' โรงเรียน');

        if ($unmatched > 0) {
            $this->warn('จับคู่ไม่ได้/ไม่มีพิกัด: '.$unmatched.' โรงเรียน');
        }

        if ($remoteOnly->isNotEmpty()) {
            $this->warn('มีข้อมูลฝั่ง OSDS ที่ไม่พบในฐานข้อมูลอีก '.count($remoteOnly).' รายการ');
        }

        if ($dryRun || ! $refreshDistance) {
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('เริ่มคำนวณระยะทางใหม่จากพิกัดล่าสุด...');

        Artisan::call('schools:refresh-distance', [
            '--force' => true,
            '--sleep' => $distanceSleep,
        ]);

        $this->line(Artisan::output());

        return self::SUCCESS;
    }

    private function normalizeValue(mixed $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '' || ! is_numeric($text)) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $text, 6, '.', ''), '0'), '.');
    }
}
