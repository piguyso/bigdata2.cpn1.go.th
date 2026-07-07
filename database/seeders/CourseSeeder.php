<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('courses')->insert([
            [
                'title' => 'AI กับการบริหารสถานศึกษาและกระบวนการเรียนรู้',
                'cover_image' => null,
                'objectives' => 'เรียนรู้และทดลองใช้งานเครื่องมือ AI ชั้นนำ เช่น ChatGPT และ Claude เพื่อช่วยวางแผนการสอน ออกแบบใบงาน และประเมินผลการเรียนการสอนอย่างรวดเร็ว',
                'hours' => '15',
                'registration_link' => 'https://forms.gle/mock-ai-course',
                'target_group' => 'ผู้บริหารและครูแกนนำ',
                'location' => 'ศูนย์ฝึกอบรมอนุบาลชุมพร',
                'status' => 'open',
                'duration_text' => 'ธ.ค. 68 - มี.ค. 69',
                'report_text' => null,
                'report_images' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'หลักสูตรวิทยาศาสตร์โลกและสิ่งแวดล้อม (Climate Change)',
                'cover_image' => null,
                'objectives' => 'การจัดการเรียนรู้เกี่ยวกับการเปลี่ยนแปลงสภาพภูมิอากาศด้วยแนวทางสะเต็มศึกษา (STEM) เน้นทักษะกระบวนการวิทยาศาสตร์และการลงมือทำการทดลอง',
                'hours' => '20',
                'registration_link' => 'https://forms.gle/mock-climate-course',
                'target_group' => 'ครูวิทยาศาสตร์ ป.4-ป.6',
                'location' => 'อบรมแบบผสมผสาน (Hybrid)',
                'status' => 'ongoing',
                'duration_text' => 'ก.พ. 2569',
                'report_text' => null,
                'report_images' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'ยกระดับทักษะคิดวิเคราะห์ วิทยาศาสตร์-คณิตศาสตร์',
                'cover_image' => null,
                'objectives' => 'การจัดทำแผนการสอนเพื่อพัฒนาการคิดวิเคราะห์ขั้นสูงตามแนวข้อสอบสากล PISA และทักษะการคำนวณที่สำคัญสำหรับเด็กปฐมวัยและประถมต้น',
                'hours' => '30',
                'registration_link' => 'https://forms.gle/mock-pisa-course',
                'target_group' => 'ครูวิทยากร และครูผู้สอนประถม',
                'location' => 'โรงเรียนอนุบาลชุมพร',
                'status' => 'upcoming',
                'duration_text' => 'มี.ค. 2569',
                'report_text' => null,
                'report_images' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
