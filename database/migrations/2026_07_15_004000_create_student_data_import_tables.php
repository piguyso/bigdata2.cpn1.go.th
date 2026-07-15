<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_data_imports', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 4)->index();
            $table->string('term', 10)->index();
            $table->string('data_type', 80)->index();
            $table->string('data_label', 255);
            $table->string('source_filename', 255);
            $table->string('stored_filename', 255)->nullable();
            $table->string('schema_version', 40);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('unmatched_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->string('mode', 20)->default('replace');
            $table->json('warnings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['academic_year', 'term', 'data_type']);
        });

        Schema::create('student_data_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('student_data_imports')->cascadeOnDelete();
            $table->string('academic_year', 4)->index();
            $table->string('term', 10)->index();
            $table->string('data_type', 80)->index();
            $table->unsignedInteger('school_id')->nullable()->index();
            $table->string('school_smis', 20)->index();
            $table->string('category', 255)->nullable()->index();
            $table->unsignedInteger('row_order')->default(0);
            $table->json('metrics')->nullable();
            $table->integer('total_male')->default(0);
            $table->integer('total_female')->default(0);
            $table->integer('total')->default(0);
            $table->integer('rooms_total')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'term', 'data_type', 'school_smis', 'category'], 'student_data_unique_row');
            $table->index(['academic_year', 'term', 'data_type', 'total']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_data_records');
        Schema::dropIfExists('student_data_imports');
    }
};
