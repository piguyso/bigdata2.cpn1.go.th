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
        if (!Schema::hasTable('system_announcements')) {
            Schema::create('system_announcements', function (Blueprint $table) {
            $table->increments('id');
            $table->text('message');
            $table->boolean('is_active')->default(0);
            $table->dateTime('updated_at')->useCurrent();
            $table->integer('updated_by')->default(0);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_announcements');
    }
};
