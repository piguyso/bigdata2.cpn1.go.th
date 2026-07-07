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
        if (!Schema::hasTable('teacher_educations')) {
            Schema::create('teacher_educations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('record_id');
            $table->string('edu_level', 30)->default('');
            $table->string('field_of_study', 255)->default('');
            $table->string('major', 255)->default('');
            $table->index(['record_id'], 'idx_record_id');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_educations');
    }
};
