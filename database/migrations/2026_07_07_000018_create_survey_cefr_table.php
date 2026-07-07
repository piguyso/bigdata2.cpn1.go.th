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
        if (!Schema::hasTable('teacher_cefr')) {
            Schema::create('teacher_cefr', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('record_id');
            $table->enum('source', ['obec','other']);
            $table->enum('cefr_level', ['A1','A2','B1','B2','C1','C2'])->nullable();
            $table->string('cert_no', 100)->nullable();
            $table->date('cert_date')->nullable();
            $table->unsignedInteger('cert_date_be')->nullable();
            $table->string('issuer', 255)->nullable();
            $table->index(['record_id'], 'fk_cefr_record');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_cefr');
    }
};
