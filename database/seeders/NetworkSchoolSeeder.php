<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NetworkSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('network_schools')->insert([
            [
                'name' => 'โรงเรียนอนุบาลชุมพร',
                'district' => 'อำเภอเมืองชุมพร',
                'address' => 'อำเภอเมืองชุมพร จังหวัดชุมพร',
                'logo' => null,
                'website' => 'http://www.anubanchumphon.ac.th',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนวัดน้อมถวาย',
                'district' => 'อำเภอเมืองชุมพร',
                'address' => 'อำเภอเมืองชุมพร จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนวัดพิชัยยาราม',
                'district' => 'อำเภอเมืองชุมพร',
                'address' => 'อำเภอเมืองชุมพร จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนวัดหูรอ',
                'district' => 'อำเภอเมืองชุมพร',
                'address' => 'อำเภอเมืองชุมพร จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนชุมชนมาบอำมฤต',
                'district' => 'อำเภอปะทิว',
                'address' => 'อำเภอปะทิว จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนอนุบาลปะทิว',
                'district' => 'อำเภอปะทิว',
                'address' => 'อำเภอปะทิว จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนอนุบาลท่าแซะ',
                'district' => 'อำเภอท่าแซะ',
                'address' => 'อำเภอท่าแซะ จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนบ้านบางไม้แก้ว',
                'district' => 'อำเภอท่าแซะ',
                'address' => 'อำเภอท่าแซะ จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนไทยรัฐวิทยา ๗๗ (บ้านเนินสันติ)',
                'district' => 'อำเภอท่าแซะ',
                'address' => 'อำเภอท่าแซะ จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'โรงเรียนบ้านงาช้าง',
                'district' => 'อำเภอท่าแซะ',
                'address' => 'อำเภอท่าแซะ จังหวัดชุมพร',
                'logo' => null,
                'website' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
