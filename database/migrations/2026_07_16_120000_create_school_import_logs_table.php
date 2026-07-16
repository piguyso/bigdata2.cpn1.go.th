<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 20);
            $table->string('area_name', 255);
            $table->string('mode', 20)->default('replace');
            $table->integer('rows_fetched')->default(0);
            $table->integer('rows_filtered')->default(0);
            $table->integer('rows_imported')->default(0);
            $table->string('source_url', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Force utf8mb4 charset for Thai text support
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE school_import_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Schema::dropIfExists('school_import_logs');
    }
};
