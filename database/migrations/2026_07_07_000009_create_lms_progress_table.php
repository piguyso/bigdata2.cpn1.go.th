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
        if (!Schema::hasTable('lms_progress')) {
            Schema::create('lms_progress', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('lesson_id');
            $table->unsignedInteger('course_id');
            $table->timestamp('completed_at')->useCurrent();
            $table->unique(['user_id', 'lesson_id'], 'uniq_prog');
            $table->index(['user_id', 'course_id'], 'idx_user_course');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_progress');
    }
};
