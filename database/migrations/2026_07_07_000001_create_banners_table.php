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
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->default('');
            $table->string('image_path', 500)->default('');
            $table->string('link_url', 2000)->default('');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->integer('created_by')->default(0);
            $table->index(['sort_order', 'is_active'], 'idx_sort');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
