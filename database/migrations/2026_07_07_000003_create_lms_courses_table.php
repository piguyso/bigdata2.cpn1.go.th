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
        if (!Schema::hasTable('lms_courses')) {
            Schema::create('lms_courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->default('');
            $table->text('description');
            $table->string('cover_url', 1000)->default('');
            $table->string('certificate_bg_url', 1000)->default('');
            $table->double('pass_threshold')->default(60.00);
            $table->string('thumbnail_url', 500)->default('');
            $table->string('category', 100)->default('');
            $table->enum('level', ['ทั่วไป','ต้น','กลาง','สูง'])->default('ทั่วไป');
            $table->enum('status', ['draft','published'])->default('draft');
            $table->unsignedInteger('created_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index(['status'], 'idx_status');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_courses');
    }
};
