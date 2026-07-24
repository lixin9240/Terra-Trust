<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $table = 'reminders';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'prescription_id', 'medicine_id', 'remind_time',
        'dosage', 'repeat_type', 'repeat_days', 'remind_method', 'is_active',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'prescription_id' => 'integer',
        'medicine_id' => 'integer',
        'repeat_type' => 'integer',
        'remind_method' => 'integer',
        'is_active' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    const REPEAT_DAILY = 1;
    const REPEAT_WEEKLY = 2;
    const REPEAT_ONCE = 3;

    const METHOD_APP = 1;
    const METHOD_SMS = 2;
    const METHOD_CALL = 3;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function medicationRecords()
    {
        return $this->hasMany(MedicationRecord::class, 'reminder_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
