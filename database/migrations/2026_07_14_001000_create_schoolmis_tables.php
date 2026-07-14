<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schoolmis_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('academic_year', 4);
            $table->unsignedTinyInteger('term');
            $table->string('source_filename', 255);
            $table->string('stored_filename', 255)->nullable();
            $table->string('schema_version', 10)->default('');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('unmatched_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->string('mode', 20)->default('replace');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();

            $table->index(['academic_year', 'term'], 'idx_schoolmis_imports_year_term');
        });

        Schema::create('schoolmis_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('academic_year', 4);
            $table->unsignedTinyInteger('term');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('school_smis', 20);
            $table->string('schema_version', 10)->default('');
            $table->string('raw_year_term', 20)->default('');
            $table->unsignedInteger('male_total')->default(0);
            $table->unsignedInteger('female_total')->default(0);
            $table->unsignedInteger('student_total')->default(0);
            $table->unsignedInteger('room_total')->default(0);
            $table->json('metrics');
            $table->timestamps();

            $table->unique(['academic_year', 'term', 'school_smis'], 'uniq_schoolmis_year_term_smis');
            $table->index(['school_id'], 'idx_schoolmis_school_id');
            $table->index(['academic_year_id'], 'idx_schoolmis_academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schoolmis_records');
        Schema::dropIfExists('schoolmis_imports');
    }
};
