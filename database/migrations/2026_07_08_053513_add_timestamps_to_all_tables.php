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
        $missingTimestamps = [
            'banners' => ['updated_at'],
            'legacy_users' => ['updated_at'],
            'login_attempts' => ['created_at', 'updated_at'],
            'system_announcements' => ['created_at'],
            'system_group' => ['created_at', 'updated_at'],
            'system_school' => ['created_at', 'updated_at'],
        ];

        foreach ($missingTimestamps as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    if (in_array('created_at', $columns)) {
                        $table->timestamp('created_at')->nullable()->useCurrent();
                    }
                    if (in_array('updated_at', $columns)) {
                        $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $missingTimestamps = [
            'banners' => ['updated_at'],
            'legacy_users' => ['updated_at'],
            'login_attempts' => ['created_at', 'updated_at'],
            'system_announcements' => ['created_at'],
            'system_group' => ['created_at', 'updated_at'],
            'system_school' => ['created_at', 'updated_at'],
        ];

        foreach ($missingTimestamps as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
