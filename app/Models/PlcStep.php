<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlcStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'plc_group_id',
        'step_name',
        'description',
        'sequence',
        'file_path',
        'status',
        'submitted_by',
        'submitted_at',
        'admin_comment',
        'reviewer_user_id',
        'reviewed_at',
        'step1_problem_statement',
        'step1_root_cause',
        'step1_goal_kpi',
        'step1_timeline_step2',
        'step1_timeline_step3',
        'step1_timeline_step4',
        'step1_timeline_step5',
        'step1_timeline_step6',
        'step2_unit_name',
        'step2_grade_subject',
        'step2_learning_objectives',
        'step2_innovation',
        'step2_idea_sharing',
        'step3_supervision_notes',
        'step3_change_log',
        'step3_plan_file_paths',
        'step3_ready_status',
        'step4_class_date',
        'step4_period',
        'step4_room',
        'step4_observations',
        'step5_self_reflection',
        'step5_peer_reflections',
        'step5_total_students',
        'step5_passed_students',
        'step5_qualitative_result',
        'step6_best_practice',
        'step6_visibility',
        'step6_final_file_paths',
    ];

    protected $casts = [
        'file_path' => 'array',
        'step2_idea_sharing' => 'array',
        'step3_supervision_notes' => 'array',
        'step3_plan_file_paths' => 'array',
        'step4_observations' => 'array',
        'step5_peer_reflections' => 'array',
        'step6_final_file_paths' => 'array',
        'step1_timeline_step2' => 'date',
        'step1_timeline_step3' => 'date',
        'step1_timeline_step4' => 'date',
        'step1_timeline_step5' => 'date',
        'step1_timeline_step6' => 'date',
        'step4_class_date' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(PlcGroup::class, 'plc_group_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
