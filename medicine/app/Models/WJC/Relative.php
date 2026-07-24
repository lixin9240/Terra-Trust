<?php

namespace App\Models\WJC;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Relative extends Authenticatable implements JWTSubject
{
    protected $table = 'relatives';
    public $timestamps = false;

    protected $fillable = [
        'username', 'password', 'phone', 'real_name', 'avatar',
        'status', 'last_login_time',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'status' => 'integer',
        'last_login_time' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['guard' => 'relative'];
    }

    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'user_relatives',
            'relative_id',
            'user_id'
        )->withPivot(['relation_text', 'permission', 'bind_status', 'bind_time']);
    }
}
