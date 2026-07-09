<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCefr extends Model
{
    protected $table = 'teacher_cefr';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the profile that owns the CEFR record.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'record_id');
    }
}
