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
        if (!Schema::hasTable('lms_quiz_questions')) {
            Schema::create('lms_quiz_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->text('question_text');
            $table->enum('media_type', ['none','image','video'])->default('none');
            $table->string('media_url', 1000)->default('');
            $table->unsignedInteger('sort_order')->default(0);
            $table->double('difficulty_value')->nullable();
            $table->double('discrimination_value')->nullable();
            $table->index(['quiz_id', 'sort_order', 'id'], 'idx_quiz_sort');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_quiz_questions');
    }
};
