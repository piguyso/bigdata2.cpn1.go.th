<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_overview_imports', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 20);
            $table->string('area_name');
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->string('mode', 20)->default('replace');
            $table->unsignedInteger('records_count')->default(0);
            $table->longText('warnings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['area_code', 'academic_year', 'term'], 'personnel_overview_imports_area_year_term_idx');
        });

        Schema::create('personnel_overview_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('personnel_overview_imports')->cascadeOnDelete();
            $table->string('area_code', 20);
            $table->string('area_name');
            $table->unsignedSmallInteger('academic_year');
            $table->string('term', 10);
            $table->unsignedInteger('total_personnel')->default(0);
            $table->unsignedInteger('government_officer_total')->default(0);
            $table->unsignedInteger('civil_service_staff_total')->default(0);
            $table->unsignedInteger('government_employee_total')->default(0);
            $table->unsignedInteger('temporary_employee_total')->default(0);
            $table->unsignedInteger('permanent_employee_total')->default(0);
            $table->unsignedInteger('director_total')->default(0);
            $table->unsignedInteger('vice_director_total')->default(0);
            $table->unsignedInteger('teacher_total')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['area_code', 'academic_year', 'term'], 'personnel_overview_records_area_year_term_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_overview_records');
        Schema::dropIfExists('personnel_overview_imports');
    }
};
