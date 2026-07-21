<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratorySub extends Model
{
     protected $table = 'laboratory_sub';

    public $timestamps = false;

    protected $fillable = [
        'laboratory_id',
        'name',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id');
    }

    public function tests()
    {
        return $this->hasMany(LaboratorySubList::class, 'laboratory_sub_id');
    }
}
