<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlcGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'semester',
        'academic_year',
        'creator_user_id',
        'is_hidden',
        'department',
        'school_group',
        'school_name',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function members()
    {
        return $this->hasMany(PlcGroupMember::class, 'plc_group_id');
    }

    public function steps()
    {
        return $this->hasMany(PlcStep::class, 'plc_group_id')->orderBy('sequence');
    }
}
