<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nt_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('academic_year', 4);
            $table->string('source_filename', 255);
            $table->string('stored_filename', 255)->nullable();
            $table->string('schema_version', 20)->default('nt13');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('unmatched_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->string('mode', 20)->default('replace');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();

            $table->index(['academic_year'], 'idx_nt_imports_year');
        });

        Schema::create('nt_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('academic_year', 4);
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('nt_school_code', 20)->index();
            $table->string('school_smis', 20)->nullable()->index();
            $table->string('school_name', 255);
            $table->string('district', 120)->default('');
            $table->string('school_size', 50)->default('');
            $table->decimal('math_score', 10, 4)->nullable();
            $table->decimal('math_percent', 10, 4)->nullable();
            $table->decimal('thai_score', 10, 4)->nullable();
            $table->decimal('thai_percent', 10, 4)->nullable();
            $table->decimal('total_score', 10, 4)->nullable();
            $table->decimal('total_percent', 10, 4)->nullable();
            $table->string('math_quality', 50)->default('');
            $table->string('thai_quality', 50)->default('');
            $table->string('total_quality', 50)->default('');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'nt_school_code'], 'uniq_nt_year_school_code');
            $table->index(['academic_year'], 'idx_nt_records_year');
            $table->index(['school_id'], 'idx_nt_records_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nt_records');
        Schema::dropIfExists('nt_imports');
    }
};
