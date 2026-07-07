<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->string('highlight')->nullable();
            $table->text('slogan')->nullable();
            $table->string('image');
            $table->string('link')->nullable();
            $table->string('btn_text')->nullable();
            $table->string('btn2_text')->nullable();
            $table->string('btn2_link')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default slides to preserve layout contents
        DB::table('slides')->insert([
            [
                'badge' => 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1',
                'title' => 'ยกระดับศักยภาพครูและบุคลากรทางการศึกษา',
                'highlight' => 'สู่นวัตกรรมการเรียนรู้เชิงรุก',
                'slogan' => 'ขับเคลื่อนความรู้เชิงรุก (Active Learning) บูรณาการวิทยาศาสตร์ คณิตศาสตร์ และเทคโนโลยี เพื่อการพัฒนาการศึกษาที่ยั่งยืนในท้องถิ่น',
                'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1920&q=80',
                'link' => '#courses',
                'btn_text' => 'ดูหลักสูตรเปิดรับสมัคร',
                'btn2_text' => 'เกี่ยวกับศูนย์ฯ',
                'btn2_link' => '#about',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'badge' => 'หลักสูตรการสอนแห่งอนาคต 2026',
                'title' => 'พัฒนาห้องเรียนแห่งอนาคต',
                'highlight' => 'ด้วยทักษะดิจิทัลและ AI',
                'slogan' => 'เรียนรู้และประยุกต์ใช้งานระบบสารสนเทศ เทคโนโลยีดิจิทัล และเครื่องมือ AI เพื่อช่วยครูวางแผนการสอน ออกแบบใบงาน และประเมินผลการเรียนรู้',
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1920&q=80',
                'link' => '#courses',
                'btn_text' => 'สำรวจคอร์สฝึกอบรม',
                'btn2_text' => 'โรงเรียนในเครือข่าย',
                'btn2_link' => '#schools',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'badge' => 'เครือข่ายโรงเรียนพัฒนาคุณภาพครู',
                'title' => 'ผสานเครือข่ายวิชาชีพ',
                'highlight' => 'ชุมชนการเรียนรู้ที่ยั่งยืน (PLC)',
                'slogan' => 'จัดกลุ่มแลกเปลี่ยนประสบการณ์ระดับเครือข่ายโรงเรียน 8 อำเภอในจังหวัดชุมพร ส่งเสริมให้ครูช่วยเหลือกันและยกระดับคุณภาพสอนร่วมกัน',
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1920&q=80',
                'link' => '#schools',
                'btn_text' => 'รายชื่อโรงเรียนแกนนำ',
                'btn2_text' => 'ติดต่อประสานงาน',
                'btn2_link' => '#contact',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
