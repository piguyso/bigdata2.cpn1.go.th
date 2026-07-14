<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->teacherTables() as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Teacher survey tables were intentionally removed from the application.
    }

    private function teacherTables(): array
    {
        return [
            'teacher_awards',
            'teacher_cefr',
            'teacher_educations',
            'teacher_hsk',
            'teacher_subjects',
            'teacher_profile',
        ];
    }
};
