<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_school', function (Blueprint $table) {
            $table->unsignedBigInteger('import_log_id')->nullable()->after('id');
            
            // Index for performance when deleting
            $table->index('import_log_id');
        });
    }

    public function down(): void
    {
        Schema::table('system_school', function (Blueprint $table) {
            $table->dropColumn('import_log_id');
        });
    }
};
