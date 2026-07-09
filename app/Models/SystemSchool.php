<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemSchool extends Model
{
    protected $table = 'system_school';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the profiles of teachers in this school.
     */
    public function teacherProfiles(): HasMany
    {
        return $this->hasMany(TeacherProfile::class, 'school_code', 'smis');
    }
}
