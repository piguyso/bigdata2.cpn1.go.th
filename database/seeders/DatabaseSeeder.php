<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // สร้างผู้ใช้ Admin เริ่มต้น
        $this->call([
            AdminUserSeeder::class,
        ]);

        // หากต้องการนำเข้าข้อมูลตัวอย่างอื่น ๆ บนเว็บบอร์ด/เว็บใหม่ สามารถเอาคอมเมนต์ออกได้
        // $this->call([
        //     NetworkSchoolSeeder::class,
        //     CourseSeeder::class,
        //     OrgMemberSeeder::class,
        // ]);
    }
}
