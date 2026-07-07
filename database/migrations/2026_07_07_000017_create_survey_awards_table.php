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
        if (!Schema::hasTable('teacher_awards')) {
            Schema::create('teacher_awards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('record_id');
            $table->string('work_name', 255)->nullable();
            $table->string('award_name', 255)->nullable();
            $table->date('award_date')->nullable();
            $table->unsignedInteger('award_date_be')->nullable();
            $table->string('issuer', 255)->nullable();
            $table->index(['record_id'], 'fk_awards_record');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_awards');
    }
};
