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
        if (!Schema::hasTable('teacher_hsk')) {
            Schema::create('teacher_hsk', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('record_id');
            $table->string('source', 30)->default('');
            $table->string('hsk_level', 50)->default('');
            $table->string('cert_no', 100)->default('');
            $table->date('cert_date')->nullable();
            $table->unsignedInteger('cert_date_be')->nullable();
            $table->string('issuer', 255)->default('');
            $table->index(['record_id'], 'idx_record_id');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_hsk');
    }
};
