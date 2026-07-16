<?php

namespace App\Services;

use App\Support\AreaSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class BoppSchoolImportService
{
    // ──────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────

    /**
     * Preview: query dmc_schools filtered by area, return summary + sample rows
     */
    public function preview(): array
    {
        $this->ensureDmcSchoolsTable();

        $areaName = AreaSettings::name();
        $filtered = $this->getDmcSchools($areaName);

        $existing = DB::table('system_school')->count();
        $existingInArea = DB::table('system_school')
            ->where('ministry', 'like', substr(AreaSettings::code(), 0, 8) . '%')
            ->count();

        $sample = array_slice($filtered, 0, 15);
        $sample = array_map(function ($r) {
            $r['statusID'] = '1';
            $r['statusDetail'] = 'เปิด';
            return $r;
        }, $sample);

        return [
            'area_code'          => AreaSettings::code(),
            'area_name'          => $areaName,
            'rows_fetched'       => DB::table('dmc_schools')->count(),
            'rows_filtered'      => count($filtered),
            'rows_existing_area' => $existingInArea,
            'rows_existing_all'  => $existing,
            'sample'             => $sample,
        ];
    }

    /**
     * Import: read from dmc_schools, filter by area, save to system_school
     */
    public function import(string $mode = 'replace', ?int $createdBy = null): array
    {
        $this->ensureDmcSchoolsTable();

        $areaName = AreaSettings::name();
        $filtered = $this->getDmcSchools($areaName);

        if (empty($filtered)) {
            throw new RuntimeException(
                'ไม่พบข้อมูลโรงเรียนที่ตรงกับ "' . $areaName . '" ใน dmc_schools ' .
                'กรุณารัน: php artisan db:seed --class=DmcSchoolSeeder ก่อน'
            );
        }

        $imported = 0;
        DB::transaction(function () use ($filtered, $mode, &$imported, $createdBy, $areaName) {
            DB::statement('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

            // Create log record first to get ID
            $logId = null;
            if (Schema::hasTable('school_import_logs')) {
                $logId = DB::table('school_import_logs')->insertGetId([
                    'area_code'     => AreaSettings::code(),
                    'area_name'     => $areaName,
                    'mode'          => $mode,
                    'rows_fetched'  => DB::table('dmc_schools')->count(),
                    'rows_filtered' => count($filtered),
                    'rows_imported' => 0, // Will update after insert/upsert
                    'source_url'    => 'dmc_schools (local DB cache)',
                    'created_by'    => $createdBy,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
            
            if ($mode === 'replace') {
                $smisList = array_column($filtered, 'smis');
                DB::table('system_school')
                    ->whereIn('smis', $smisList)
                    ->delete();
            }

            foreach (array_chunk($filtered, 300) as $chunk) {
                $rows = [];
                foreach ($chunk as $r) {
                    $rows[] = [
                        'import_log_id'  => $logId,
                        'smis'           => $r['smis'],
                        'percode'        => '',
                        'ministry'       => $r['ministry'],
                        'schoolname'     => $r['schoolname'],
                        'schoolname_eng' => '',
                        'schoolgroup'    => $r['school_type'] ?? '',
                        'muti'           => $r['muban'],
                        'road'           => '',
                        'muban'          => $r['tambon'],
                        'tambon'         => $r['muban2'],
                        'amper'          => $r['amper'],
                        'province'       => $r['province'],
                        'postcode'       => $r['postcode'],
                        'lat'            => $r['lat'],
                        'lng'            => $r['lng'],
                        'length_km'      => '',
                        'maplink'        => '',
                        'tel'            => $r['tel'],
                        'email'          => '',
                        'website'        => '',
                        'statusID'       => '1',
                        'statusDetail'   => $r['size_criteria'] ?? '',
                    ];
                }

                if ($mode === 'replace') {
                    DB::table('system_school')->insert($rows);
                } else {
                    DB::table('system_school')->upsert(
                        $rows,
                        ['smis'],
                        ['import_log_id', 'percode', 'ministry', 'schoolname', 'schoolgroup',
                         'muti', 'road', 'muban', 'tambon', 'amper', 'province', 'postcode',
                         'lat', 'lng', 'length_km', 'maplink', 'tel', 'email', 'website',
                         'statusID', 'statusDetail']
                    );
                }
                $imported += count($rows);
            }

            // Update actual rows imported in the log
            if ($logId) {
                DB::table('school_import_logs')
                    ->where('id', $logId)
                    ->update(['rows_imported' => $imported]);
            }
        });

        return [
            'rows_fetched'  => DB::table('dmc_schools')->count(),
            'rows_filtered' => count($filtered),
            'rows_imported' => $imported,
            'mode'          => $mode,
        ];
    }

    /**
     * Get import history logs
     */
    public function getLogs(): array
    {
        if (! Schema::hasTable('school_import_logs')) {
            return [];
        }

        return DB::table('school_import_logs as logs')
            ->leftJoin('users', 'logs.created_by', '=', 'users.id')
            ->where('logs.area_code', AreaSettings::code())
            ->select('logs.*', 'users.name as created_by_name')
            ->orderByDesc('logs.id')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Get count of schools in dmc_schools for the current area areacode
     */
    public function getDmcCount(): array
    {
        if (! Schema::hasTable('dmc_schools')) {
            return ['total' => 0, 'area' => 0, 'area_name' => AreaSettings::name()];
        }

        $areaCode = $this->resolveAreaCode8();
        $areaCount = DB::table('dmc_schools')->where('areacode', $areaCode)->count();

        return [
            'total'     => DB::table('dmc_schools')->count(),
            'area'      => $areaCount,
            'area_name' => AreaSettings::name(),
        ];
    }

    // ──────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────

    /**
     * Query dmc_schools filtered strictly by areacode
     */
    private function getDmcSchools(string $areaName): array
    {
        $areaCode8 = $this->resolveAreaCode8();
        
        return DB::table('dmc_schools')
            ->where('areacode', $areaCode8)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }

    /**
     * Convert 10-digit AreaSettings::code() to 8-digit dmc_schools areacode
     * e.g., 1036020000 -> 36020000
     */
    private function resolveAreaCode8(): string
    {
        $code = trim(AreaSettings::code());
        if (strlen($code) === 10 && str_starts_with($code, '10')) {
            return substr($code, 2);
        }
        return $code;
    }

    /**
     * Ensure dmc_schools table exists and has data
     */
    private function ensureDmcSchoolsTable(): void
    {
        if (! Schema::hasTable('dmc_schools')) {
            throw new RuntimeException(
                'ยังไม่มีตาราง dmc_schools — กรุณารัน: php artisan migrate ก่อน'
            );
        }

        if (DB::table('dmc_schools')->count() === 0) {
            throw new RuntimeException(
                'ตาราง dmc_schools ยังไม่มีข้อมูล — กรุณารัน: php artisan db:seed --class=DmcSchoolSeeder ก่อน'
            );
        }
    }
}
