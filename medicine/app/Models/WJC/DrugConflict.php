<?php

namespace App\Models\WJC;

use Illuminate\Database\Eloquent\Model;

class DrugConflict extends Model
{
    protected $table = 'drug_conflicts';
    public $timestamps = false;

    protected $fillable = [
        'medicine_a_id', 'medicine_b_id', 'conflict_level',
        'conflict_desc', 'suggest',
    ];

    protected $casts = [
        'medicine_a_id' => 'integer',
        'medicine_b_id' => 'integer',
        'conflict_level' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function medicineA()
    {
        return $this->belongsTo(DrugLibrary::class, 'medicine_a_id');
    }

    public function medicineB()
    {
        return $this->belongsTo(DrugLibrary::class, 'medicine_b_id');
    }
}
