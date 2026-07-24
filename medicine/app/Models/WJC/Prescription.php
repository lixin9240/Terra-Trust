<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescriptions';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'medicine_id', 'doctor_name', 'hospital',
        'diagnosis', 'dosage', 'frequency', 'start_date', 'end_date',
        'conflict_warning', 'status', 'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'medicine_id' => 'integer',
        'status' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'prescription_id');
    }

    public function medicationRecords()
    {
        return $this->hasMany(MedicationRecord::class, 'prescription_id');
    }
}
