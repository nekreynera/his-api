<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $table = 'laboratory';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function categories()
    {
        return $this->hasMany(LaboratorySub::class, 'laboratory_id');
    }
}
