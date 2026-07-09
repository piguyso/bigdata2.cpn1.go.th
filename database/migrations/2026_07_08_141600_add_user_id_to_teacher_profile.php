<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - เพิ่ม column user_id (nullable FK → users.id)
     * - Backfill จาก email ที่ตรงกันใน users
     * - เพิ่ม index บน user_id
     */
    public function up(): void
    {
        // 1. เพิ่ม user_id column (nullable เพื่อรองรับ record เก่าที่ backfill ไม่ได้)
        Schema::table('teacher_profile', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->index('user_id', 'idx_teacher_profile_user_id');
        });

        // 2. Backfill: จับคู่ teacher_profile.email กับ users.email
        DB::statement("
            UPDATE teacher_profile tp
            INNER JOIN users u ON u.email = tp.email
            SET tp.user_id = u.id
            WHERE tp.user_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_profile', function (Blueprint $table) {
            $table->dropIndex('idx_teacher_profile_user_id');
            $table->dropColumn('user_id');
        });
    }
};
