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
        if (!Schema::hasTable('system_school')) {
            Schema::create('system_school', function (Blueprint $table) {
            $table->increments('id');
            $table->string('smis', 20);
            $table->string('percode', 20);
            $table->string('ministry', 20);
            $table->string('schoolname', 1500);
            $table->string('schoolname_eng', 999);
            $table->string('schoolgroup', 2);
            $table->string('muti', 10);
            $table->string('road', 100);
            $table->string('muban', 100);
            $table->string('tambon', 100);
            $table->string('amper', 100);
            $table->string('province', 100);
            $table->string('postcode', 100);
            $table->string('lat', 80);
            $table->string('lng', 80);
            $table->string('length_km', 10);
            $table->string('maplink', 255);
            $table->string('tel', 20);
            $table->string('email', 150);
            $table->string('website', 150);
            $table->string('statusID', 1);
            $table->string('statusDetail', 20);
        });

        // Force utf8mb4 charset for Thai text support
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE system_school CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_school');
    }
};
