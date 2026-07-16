<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = [
            '2565' => 'ปีการศึกษา 2565',
            '2566' => 'ปีการศึกษา 2566',
            '2567' => 'ปีการศึกษา 2567',
            '2568' => 'ปีการศึกษา 2568',
            '2569' => 'ปีการศึกษา 2569',
        ];

        foreach ($years as $year => $name) {
            DB::table('academic_years')->updateOrInsert(
                ['year' => $year],
                [
                    'name'       => $name,
                    'is_active'  => $year === '2569', // Set 2569 as active by default
                    'sort_order' => (int) $year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
