<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherHsk extends Model
{
    protected $table = 'teacher_hsk';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the profile that owns the HSK record.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'record_id');
    }
}
