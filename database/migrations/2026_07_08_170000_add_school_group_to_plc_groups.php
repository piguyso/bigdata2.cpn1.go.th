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
        Schema::table('plc_groups', function (Blueprint $table) {
            $table->string('school_group', 255)->after('department')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plc_groups', function (Blueprint $table) {
            $table->dropColumn('school_group');
        });
    }
};
