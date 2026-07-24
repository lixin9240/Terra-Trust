<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'notices';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'title', 'content', 'type', 'is_read',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_read' => 'integer',
        'create_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }
}
