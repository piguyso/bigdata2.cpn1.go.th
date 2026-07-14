<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table) {
                $table->id();
                $table->string('year', 4)->unique();
                $table->string('name', 100);
                $table->date('starts_at')->nullable();
                $table->date('ends_at')->nullable();
                $table->boolean('is_active')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $years = collect();

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'academic_year')) {
            $years = DB::table('courses')
                ->whereNotNull('academic_year')
                ->where('academic_year', '<>', '')
                ->distinct()
                ->pluck('academic_year');
        }

        if ($years->isEmpty()) {
            $years = collect([(string) (now()->year + 543)]);
        }

        foreach ($years as $index => $year) {
            $normalizedYear = preg_replace('/[^0-9]/', '', (string) $year);

            if ($normalizedYear === '') {
                continue;
            }

            DB::table('academic_years')->updateOrInsert(
                ['year' => $normalizedYear],
                [
                    'name' => 'ปีการศึกษา ' . $normalizedYear,
                    'is_active' => $index === 0,
                    'sort_order' => (int) $normalizedYear,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
