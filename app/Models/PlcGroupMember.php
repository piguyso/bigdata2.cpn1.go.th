<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlcGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'plc_group_id',
        'user_id',
        'role',
    ];

    public function group()
    {
        return $this->belongsTo(PlcGroup::class, 'plc_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
