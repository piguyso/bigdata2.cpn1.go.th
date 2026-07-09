<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherEducation extends Model
{
    protected $table = 'teacher_educations';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the profile that owns the education.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'record_id');
    }
}
