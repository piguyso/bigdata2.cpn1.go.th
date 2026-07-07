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
        if (!Schema::hasTable('teacher_profile')) {
            Schema::create('teacher_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->string('school_code');
            $table->string('school_name', 255)->default('');
            $table->string('school_network', 100)->default('');
            $table->string('prefix', 20)->default('');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('personalid', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('recruitment_subject', 150)->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedInteger('birth_year_be')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('position', 60)->default('');
            $table->string('academic_rank', 40)->default('');
            $table->date('appointed_date')->nullable();
            $table->unsignedInteger('appointed_year_be')->nullable();
            $table->string('bachelor_major', 150)->nullable();
            $table->string('master_major', 150)->nullable();
            $table->string('doctoral_major', 150)->nullable();
            $table->text('other_workload')->nullable();
            $table->string('profile_image_name', 255)->nullable();
            $table->string('profile_image_path', 255)->nullable();
            $table->text('profile_image_url')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->index(['school_code'], 'idx_school_code');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_profile');
    }
};
