<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class UserRelative extends Model
{
    protected $table = 'user_relatives';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'relative_id', 'relation_text', 'permission',
        'bind_status', 'bind_time',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'relative_id' => 'integer',
        'permission' => 'integer',
        'bind_status' => 'integer',
        'bind_time' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function relative()
    {
        return $this->belongsTo(Relative::class, 'relative_id');
    }
}
