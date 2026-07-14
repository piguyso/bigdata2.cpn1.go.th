<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 20);
            $table->string('area_name');
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->string('mode', 20)->default('replace');
            $table->unsignedInteger('sources_count')->default(0);
            $table->unsignedInteger('normalized_records_count')->default(0);
            $table->unsignedInteger('matched_schools_count')->default(0);
            $table->unsignedInteger('unmatched_schools_count')->default(0);
            $table->json('warnings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['area_code', 'academic_year', 'term'], 'idx_personnel_batches_area_year_term');
        });

        Schema::create('personnel_import_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('personnel_import_batches')->cascadeOnDelete();
            $table->string('area_code', 20);
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->string('source_key', 40);
            $table->string('endpoint', 500);
            $table->unsignedInteger('records_count')->default(0);
            $table->string('payload_hash', 64);
            $table->longText('payload');
            $table->timestamps();

            $table->unique(['area_code', 'academic_year', 'term', 'source_key'], 'uniq_personnel_sources_snapshot');
            $table->index(['source_key'], 'idx_personnel_sources_key');
        });

        Schema::create('personnel_report_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('personnel_import_batches')->cascadeOnDelete();
            $table->string('area_code', 20);
            $table->string('area_name')->nullable();
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->string('report_key', 20);
            $table->string('level', 20);
            $table->unsignedInteger('school_id')->nullable();
            $table->string('school_smis', 20)->nullable();
            $table->string('school_code', 30)->nullable();
            $table->string('school_name')->nullable();
            $table->json('metrics')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['area_code', 'academic_year', 'term', 'report_key', 'level', 'school_smis'], 'uniq_personnel_report_school');
            $table->index(['report_key', 'level'], 'idx_personnel_report_key_level');
            $table->index(['school_smis'], 'idx_personnel_report_smis');
        });

        Schema::create('personnel_workload_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('personnel_import_batches')->cascadeOnDelete();
            $table->string('area_code', 20);
            $table->string('area_name')->nullable();
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->unsignedInteger('school_id')->nullable();
            $table->string('school_smis', 20)->nullable();
            $table->string('school_code', 30)->nullable();
            $table->string('school_name')->nullable();
            $table->string('district')->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('province')->nullable();
            $table->string('school_type')->nullable();
            $table->string('school_size')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('students_total')->default(0);
            $table->unsignedInteger('rooms_total')->default(0);
            $table->unsignedInteger('personnel_total')->default(0);
            $table->unsignedInteger('teacher_total')->default(0);
            $table->unsignedInteger('director_total')->default(0);
            $table->unsignedInteger('vice_director_total')->default(0);
            $table->integer('teacher_shortage_total')->default(0);
            $table->json('students')->nullable();
            $table->json('manpowers')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['area_code', 'academic_year', 'term', 'school_smis'], 'uniq_personnel_workload_school');
            $table->index(['school_id'], 'idx_personnel_workload_school_id');
            $table->index(['school_size'], 'idx_personnel_workload_school_size');
        });

        Schema::create('personnel_area_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('personnel_import_batches')->cascadeOnDelete();
            $table->string('area_code', 20);
            $table->string('area_name')->nullable();
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->json('metrics')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['area_code', 'academic_year', 'term'], 'uniq_personnel_area_profile_snapshot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_area_profiles');
        Schema::dropIfExists('personnel_workload_schools');
        Schema::dropIfExists('personnel_report_records');
        Schema::dropIfExists('personnel_import_sources');
        Schema::dropIfExists('personnel_import_batches');
    }
};
