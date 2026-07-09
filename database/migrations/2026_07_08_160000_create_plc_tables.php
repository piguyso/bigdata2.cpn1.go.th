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
        Schema::create('plc_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('semester');
            $table->string('academic_year', 10);
            $table->foreignId('creator_user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('is_hidden')->default(0);
            $table->string('department', 150);
            $table->timestamps();
        });

        Schema::create('plc_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plc_group_id')->constrained('plc_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role', 100)->default('ครูคู่หู');
            $table->timestamps();
        });

        Schema::create('plc_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plc_group_id')->constrained('plc_groups')->onDelete('cascade');
            $table->string('step_name');
            $table->text('description')->nullable();
            $table->tinyInteger('sequence');

            // Files (JSON Array)
            $table->text('file_path')->nullable();

            // Statuses
            $table->tinyInteger('status')->default(0);
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->text('admin_comment')->nullable();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            // Step 1: Plan
            $table->text('step1_problem_statement')->nullable();
            $table->text('step1_root_cause')->nullable();
            $table->text('step1_goal_kpi')->nullable();
            $table->date('step1_timeline_step2')->nullable();
            $table->date('step1_timeline_step3')->nullable();
            $table->date('step1_timeline_step4')->nullable();
            $table->date('step1_timeline_step5')->nullable();
            $table->date('step1_timeline_step6')->nullable();

            // Step 2: Design
            $table->text('step2_unit_name')->nullable();
            $table->text('step2_grade_subject')->nullable();
            $table->text('step2_learning_objectives')->nullable();
            $table->text('step2_innovation')->nullable();
            $table->json('step2_idea_sharing')->nullable();

            // Step 3: Develop
            $table->json('step3_supervision_notes')->nullable();
            $table->text('step3_change_log')->nullable();
            $table->text('step3_plan_file_paths')->nullable();
            $table->tinyInteger('step3_ready_status')->default(0);

            // Step 4: Do & See
            $table->dateTime('step4_class_date')->nullable();
            $table->string('step4_period', 100)->nullable();
            $table->string('step4_room', 100)->nullable();
            $table->json('step4_observations')->nullable();

            // Step 5: Reflect
            $table->text('step5_self_reflection')->nullable();
            $table->json('step5_peer_reflections')->nullable();
            $table->integer('step5_total_students')->unsigned()->nullable();
            $table->integer('step5_passed_students')->unsigned()->nullable();
            $table->text('step5_qualitative_result')->nullable();

            // Step 6: Publish
            $table->text('step6_best_practice')->nullable();
            $table->string('step6_visibility', 20)->default('group');
            $table->text('step6_final_file_paths')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plc_steps');
        Schema::dropIfExists('plc_group_members');
        Schema::dropIfExists('plc_groups');
    }
};
