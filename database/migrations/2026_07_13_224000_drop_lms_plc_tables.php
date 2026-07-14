<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->tables() as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // LMS and PLC tables were intentionally removed from the application.
    }

    private function tables(): array
    {
        return [
            'lms_quiz_answers',
            'lms_quiz_attempts',
            'lms_quiz_options',
            'lms_quiz_questions',
            'lms_quizzes',
            'lms_lesson_submissions',
            'lms_lesson_progress',
            'lms_lesson_activity',
            'lms_lessons',
            'lms_progress',
            'lms_enrollments',
            'lms_courses',
            'plc_group_members',
            'plc_steps',
            'plc_groups',
        ];
    }
};
