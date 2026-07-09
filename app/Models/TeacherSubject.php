<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubject extends Model
{
    protected $table = 'teacher_subjects';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the profile that owns the subject.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'record_id');
    }
}
