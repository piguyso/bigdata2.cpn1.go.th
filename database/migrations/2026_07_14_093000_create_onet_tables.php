<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onet_imports', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 20)->default('1086010000');
            $table->string('area_name', 255)->default('สพป.ชุมพร เขต 1');
            $table->unsignedInteger('academic_year');
            $table->string('mode', 20)->default('replace');
            $table->unsignedInteger('schools_count')->default(0);
            $table->unsignedInteger('records_count')->default(0);
            $table->unsignedInteger('subjects_count')->default(0);
            $table->unsignedInteger('skipped_schools_count')->default(0);
            $table->json('warnings')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['academic_year', 'area_code'], 'idx_onet_imports_year_area');
        });

        Schema::create('onet_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->nullable()->constrained('onet_imports')->nullOnDelete();
            $table->unsignedInteger('school_id')->nullable();
            $table->string('area_code', 20)->default('1086010000');
            $table->string('area_name', 255)->default('สพป.ชุมพร เขต 1');
            $table->unsignedInteger('academic_year');
            $table->string('grade_code', 10);
            $table->string('grade_abbr', 20)->nullable();
            $table->string('school_smis', 20)->nullable();
            $table->string('school_code', 20);
            $table->string('school_name', 255);
            $table->string('subject_code', 20);
            $table->string('subject_name', 255);
            $table->unsignedInteger('student_count')->default(0);
            $table->decimal('school_avg', 10, 4)->default(0);
            $table->decimal('province_avg', 10, 4)->default(0);
            $table->decimal('regional_avg', 10, 4)->default(0);
            $table->decimal('country_avg', 10, 4)->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(
                ['academic_year', 'grade_code', 'school_code', 'subject_code'],
                'uniq_onet_year_grade_school_subject'
            );
            $table->index(['academic_year', 'grade_code'], 'idx_onet_year_grade');
            $table->index(['school_smis'], 'idx_onet_school_smis');
            $table->index(['school_code'], 'idx_onet_school_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onet_records');
        Schema::dropIfExists('onet_imports');
    }
};
