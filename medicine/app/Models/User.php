<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'username', 'password', 'phone', 'email', 'real_name',
        'gender', 'age', 'avatar', 'status', 'last_login_time',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'gender' => 'integer',
        'age' => 'integer',
        'status' => 'integer',
        'last_login_time' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function relatives()
    {
        return $this->belongsToMany(
            \App\Models\WJC\Relative::class,
            'user_relatives',
            'user_id',
            'relative_id'
        )->withPivot(['relation_text', 'permission', 'bind_status', 'bind_time']);
    }

    public function medicines()
    {
        return $this->hasMany(\App\Models\WJC\Medicine::class, 'user_id');
    }

    public function reminders()
    {
        return $this->hasMany(\App\Models\WJC\Reminder::class, 'user_id');
    }

    public function medicationRecords()
    {
        return $this->hasMany(\App\Models\WJC\MedicationRecord::class, 'user_id');
    }

    public function healthRecords()
    {
        return $this->hasMany(\App\Models\WJC\HealthRecord::class, 'user_id');
    }

    public function notices()
    {
        return $this->hasMany(\App\Models\WJC\Notice::class, 'user_id');
    }
}
