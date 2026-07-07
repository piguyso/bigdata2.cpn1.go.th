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
        if (!Schema::hasTable('lms_quizzes')) {
            Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('course_id');
            $table->enum('quiz_type', ['pre','post']);
            $table->string('title', 255)->default('');
            $table->boolean('is_active')->default(1);
            $table->string('header_image', 1000)->default('');
            $table->text('instructions');
            $table->unsignedInteger('draw_count')->default(0);
            $table->tinyInteger('options_count')->default(4);
            $table->enum('shuffle_mode', ['none','questions','options','both'])->default('none');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['course_id', 'quiz_type'], 'uniq_course_quiz_type');
            $table->index(['course_id', 'quiz_type'], 'idx_course_type');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_quizzes');
    }
};
