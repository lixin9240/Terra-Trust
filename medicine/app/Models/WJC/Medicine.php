<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $table = 'medicines';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'name', 'specification', 'manufacturer',
        'expire_date', 'stock', 'unit', 'usage', 'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'stock' => 'integer',
        'expire_date' => 'date',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'medicine_id');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'medicine_id');
    }
}
