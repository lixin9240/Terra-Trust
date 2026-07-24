<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class DrugLibrary extends Model
{
    protected $table = 'drug_library';
    public $timestamps = false;

    protected $fillable = [
        'drug_name', 'specification', 'manufacturer', 'usage',
        'taboo', 'side_effect', 'effect', 'match_conflict', 'save_note',
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function conflictsA()
    {
        return $this->hasMany(DrugConflict::class, 'medicine_a_id');
    }

    public function conflictsB()
    {
        return $this->hasMany(DrugConflict::class, 'medicine_b_id');
    }
}
