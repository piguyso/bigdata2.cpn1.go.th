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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('cover_image')->nullable();
            $table->text('objectives')->nullable();
            $table->string('hours')->nullable();
            $table->string('registration_link')->nullable();
            $table->string('target_group')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('upcoming'); // upcoming, open, ongoing, closed
            $table->string('duration_text')->nullable();
            $table->text('report_text')->nullable();
            $table->text('report_images')->nullable(); // JSON array of report image paths
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
