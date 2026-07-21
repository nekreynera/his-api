<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratorySubList extends Model
{
     protected $table = 'laboratory_sub_list';

    protected $fillable = [
        'laboratory_sub_id',
        'name',
        'price',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(LaboratorySub::class, 'laboratory_sub_id');
    }
}
