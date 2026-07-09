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
        $missingTimestamps = [
            'banners' => ['updated_at'],
            'legacy_users' => ['updated_at'],
            'lms_enrollments' => ['created_at', 'updated_at'],
            'lms_lesson_activity' => ['updated_at'],
            'lms_lesson_progress' => ['created_at', 'updated_at'],
            'lms_lesson_submissions' => ['created_at', 'updated_at'],
            'lms_lessons' => ['updated_at'],
            'lms_progress' => ['created_at', 'updated_at'],
            'lms_quiz_answers' => ['created_at', 'updated_at'],
            'lms_quiz_attempts' => ['created_at', 'updated_at'],
            'lms_quiz_options' => ['created_at', 'updated_at'],
            'lms_quiz_questions' => ['created_at', 'updated_at'],
            'lms_quizzes' => ['updated_at'],
            'login_attempts' => ['created_at', 'updated_at'],
            'system_announcements' => ['created_at'],
            'system_group' => ['created_at', 'updated_at'],
            'system_school' => ['created_at', 'updated_at'],
            'teacher_awards' => ['created_at', 'updated_at'],
            'teacher_cefr' => ['created_at', 'updated_at'],
            'teacher_educations' => ['created_at', 'updated_at'],
            'teacher_hsk' => ['created_at', 'updated_at'],
            'teacher_profile' => ['updated_at'],
            'teacher_subjects' => ['created_at', 'updated_at'],
        ];

        foreach ($missingTimestamps as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    if (in_array('created_at', $columns)) {
                        $table->timestamp('created_at')->nullable()->useCurrent();
                    }
                    if (in_array('updated_at', $columns)) {
                        $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $missingTimestamps = [
            'banners' => ['updated_at'],
            'legacy_users' => ['updated_at'],
            'lms_enrollments' => ['created_at', 'updated_at'],
            'lms_lesson_activity' => ['updated_at'],
            'lms_lesson_progress' => ['created_at', 'updated_at'],
            'lms_lesson_submissions' => ['created_at', 'updated_at'],
            'lms_lessons' => ['updated_at'],
            'lms_progress' => ['created_at', 'updated_at'],
            'lms_quiz_answers' => ['created_at', 'updated_at'],
            'lms_quiz_attempts' => ['created_at', 'updated_at'],
            'lms_quiz_options' => ['created_at', 'updated_at'],
            'lms_quiz_questions' => ['created_at', 'updated_at'],
            'lms_quizzes' => ['updated_at'],
            'login_attempts' => ['created_at', 'updated_at'],
            'system_announcements' => ['created_at'],
            'system_group' => ['created_at', 'updated_at'],
            'system_school' => ['created_at', 'updated_at'],
            'teacher_awards' => ['created_at', 'updated_at'],
            'teacher_cefr' => ['created_at', 'updated_at'],
            'teacher_educations' => ['created_at', 'updated_at'],
            'teacher_hsk' => ['created_at', 'updated_at'],
            'teacher_profile' => ['updated_at'],
            'teacher_subjects' => ['created_at', 'updated_at'],
        ];

        foreach ($missingTimestamps as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
