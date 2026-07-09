<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAward extends Model
{
    protected $table = 'teacher_awards';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the profile that owns the award.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'record_id');
    }
}
