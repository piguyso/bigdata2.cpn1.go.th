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
        if (!Schema::hasTable('lms_lessons')) {
            Schema::create('lms_lessons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('course_id');
            $table->string('title', 255)->default('');
            $table->text('content_type')->default('text');
            $table->string('content_url', 1000)->default('');
            $table->text('content_html');
            $table->text('rubric_html');
            $table->text('content_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('min_focus_seconds')->default(30);
            $table->boolean('require_submission')->default(0);
            $table->unsignedInteger('min_video_seconds')->default(0);
            $table->unsignedInteger('duration_min')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['course_id'], 'idx_course');
            $table->index(['course_id', 'sort_order'], 'idx_sort');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_lessons');
    }
};
