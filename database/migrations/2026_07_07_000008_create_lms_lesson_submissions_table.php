<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('lms_lesson_submissions')) {
            Schema::create('lms_lesson_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('lesson_id');
            $table->string('file_url', 1000)->default('');
            $table->enum('status', ['pending','passed','failed'])->default('pending');
            $table->text('student_note');
            $table->text('admin_comment');
            $table->string('student_name', 255)->default('');
            $table->string('student_school', 255)->default('');
            $table->unsignedInteger('reviewed_by')->default(0);
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->index(['lesson_id', 'status'], 'idx_lesson_status');
            $table->index(['user_id', 'lesson_id'], 'idx_user_lesson');
            $table->index(['course_id'], 'idx_course');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_lesson_submissions');
    }
};
