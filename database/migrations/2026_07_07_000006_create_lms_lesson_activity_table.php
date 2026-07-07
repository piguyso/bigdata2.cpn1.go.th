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
        if (!Schema::hasTable('lms_lesson_activity')) {
            Schema::create('lms_lesson_activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('lesson_id');
            $table->enum('event_type', ['focus','video']);
            $table->unsignedInteger('seconds_spent')->default(0);
            $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('lms_lesson_activity');
    }
};
