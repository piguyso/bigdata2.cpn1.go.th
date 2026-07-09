<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfile extends Model
{
    protected $table = 'teacher_profile';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the user account linked to this teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the school that owns this teacher profile.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(SystemSchool::class, 'school_code', 'smis');
    }

    /**
     * Get educations for the teacher.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(TeacherEducation::class, 'record_id');
    }

    /**
     * Get subjects taught by the teacher.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class, 'record_id');
    }

    /**
     * Get awards received by the teacher.
     */
    public function awards(): HasMany
    {
        return $this->hasMany(TeacherAward::class, 'record_id');
    }

    /**
     * Get CEFR english exam record.
     */
    public function cefr(): HasOne
    {
        return $this->hasOne(TeacherCefr::class, 'record_id');
    }

    /**
     * Get HSK chinese exam record.
     */
    public function hsk(): HasOne
    {
        return $this->hasOne(TeacherHsk::class, 'record_id');
    }

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute($value)
    {
        if ($this->profile_image_path) {
            if (str_starts_with($this->profile_image_path, 'http://') || str_starts_with($this->profile_image_path, 'https://')) {
                return $this->profile_image_path;
            }
            return asset('storage/' . $this->profile_image_path);
        }
        return $value;
    }
}
