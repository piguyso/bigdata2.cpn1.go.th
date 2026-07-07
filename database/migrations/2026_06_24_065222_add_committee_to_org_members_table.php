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
        Schema::table('org_members', function (Blueprint $table) {
            $table->string('committee')->default('operations')->after('role');
            $table->string('role_title')->nullable()->after('committee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('org_members', function (Blueprint $table) {
            $table->dropColumn(['committee', 'role_title']);
        });
    }
};
