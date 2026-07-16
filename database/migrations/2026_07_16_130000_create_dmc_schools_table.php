<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dmc_schools', function (Blueprint $table) {
            $table->id();
            // Key identifiers
            $table->string('areacode', 20)->default('');
            $table->string('smis', 20)->default('');
            $table->string('ministry', 20)->default('');
            // Names
            $table->string('schoolname', 500)->default('');
            $table->string('area_name', 255)->default('');
            $table->string('amper', 100)->default('');
            $table->string('province', 100)->default('');
            // Address
            $table->string('muban', 100)->default('');
            $table->string('tambon', 100)->default('');
            $table->string('muban2', 100)->default('');
            $table->string('postcode', 20)->default('');
            $table->string('tel', 50)->default('');
            // Geo
            $table->string('lat', 30)->default('');
            $table->string('lng', 30)->default('');
            // Metadata
            $table->string('school_type', 20)->default('');
            $table->string('region', 50)->default('');
            $table->string('size_criteria', 30)->default('');
            $table->string('expand_opportunity', 10)->default('');
            $table->integer('total_students')->default(0);
            $table->integer('total_rooms')->default(0);
            // Index for fast lookup
            $table->index('areacode');
            $table->index('smis');
            $table->index('ministry');
            $table->index('area_name');
        });

        // Force utf8mb4 charset for Thai text support
        DB::statement("ALTER TABLE dmc_schools CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        Schema::dropIfExists('dmc_schools');
    }
};
