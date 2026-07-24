<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    protected $table = 'health_records';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'blood_pressure_high', 'blood_pressure_low',
        'blood_sugar', 'weight',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'create_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
