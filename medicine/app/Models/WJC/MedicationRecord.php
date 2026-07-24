<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class MedicationRecord extends Model
{
    protected $table = 'medication_records';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'reminder_id', 'prescription_id', 'medicine_id',
        'plan_time', 'actual_time', 'dosage_taken', 'status', 'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'reminder_id' => 'integer',
        'prescription_id' => 'integer',
        'medicine_id' => 'integer',
        'status' => 'integer',
        'plan_time' => 'datetime',
        'actual_time' => 'datetime',
        'create_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    const STATUS_TAKEN = 1;
    const STATUS_MISSED = 2;
    const STATUS_SKIPPED = 3;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function reminder()
    {
        return $this->belongsTo(Reminder::class, 'reminder_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
