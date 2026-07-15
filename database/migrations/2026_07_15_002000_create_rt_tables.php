<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('academic_year', 4);
            $table->string('source_filename', 255);
            $table->string('stored_filename', 255)->nullable();
            $table->string('sheet_name', 100)->default('Local05');
            $table->string('schema_version', 20)->default('rt_local05');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('unmatched_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->string('mode', 20)->default('replace');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();

            $table->index(['academic_year'], 'idx_rt_imports_year');
        });

        Schema::create('rt_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('academic_year', 4);
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('rt_school_code', 20)->index();
            $table->string('school_smis', 20)->nullable()->index();
            $table->string('school_name', 255);
            $table->string('district', 120)->default('');
            $table->string('school_size', 50)->default('');
            $table->unsignedInteger('students_count')->default(0);
            $table->decimal('reading_aloud_percent', 10, 4)->nullable();
            $table->decimal('reading_aloud_sd', 10, 4)->nullable();
            $table->decimal('reading_aloud_max', 10, 4)->nullable();
            $table->decimal('reading_aloud_min', 10, 4)->nullable();
            $table->decimal('reading_aloud_mode', 10, 4)->nullable();
            $table->decimal('reading_aloud_median', 10, 4)->nullable();
            $table->decimal('reading_comprehension_percent', 10, 4)->nullable();
            $table->decimal('reading_comprehension_sd', 10, 4)->nullable();
            $table->decimal('reading_comprehension_max', 10, 4)->nullable();
            $table->decimal('reading_comprehension_min', 10, 4)->nullable();
            $table->decimal('reading_comprehension_mode', 10, 4)->nullable();
            $table->decimal('reading_comprehension_median', 10, 4)->nullable();
            $table->decimal('total_percent', 10, 4)->nullable();
            $table->decimal('total_sd', 10, 4)->nullable();
            $table->decimal('total_max', 10, 4)->nullable();
            $table->decimal('total_min', 10, 4)->nullable();
            $table->decimal('total_mode', 10, 4)->nullable();
            $table->decimal('total_median', 10, 4)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'rt_school_code'], 'uniq_rt_year_school_code');
            $table->index(['academic_year'], 'idx_rt_records_year');
            $table->index(['school_id'], 'idx_rt_records_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_records');
        Schema::dropIfExists('rt_imports');
    }
};
