<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('network_schools');
    }

    public function down(): void
    {
        if (Schema::hasTable('network_schools')) {
            return;
        }

        Schema::create('network_schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('district')->nullable();
            $table->string('school_group')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }
};
