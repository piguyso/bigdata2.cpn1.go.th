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
        if (!Schema::hasTable('legacy_users')) {
            Schema::create('legacy_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('username', 60);
            $table->string('title', 100)->default('');
            $table->string('fname', 255)->default('');
            $table->string('lname', 255)->default('');
            $table->string('personalid', 50)->default('');
            $table->string('school', 255)->default('');
            $table->string('email', 255)->default('');
            $table->integer('user_level')->default(0);
            $table->string('password_hash', 255);
            $table->enum('role', ['admin','viewer'])->default('viewer');
            $table->boolean('is_active')->default(1);
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('last_login')->nullable();
            $table->unique(['username'], 'username');
            $table->unique(['user_id'], 'uniq_user_id');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_users');
    }
};
