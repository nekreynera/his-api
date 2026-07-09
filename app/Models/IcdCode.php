<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcdCode extends Model
{
    protected $table = 'icd_codes';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'description',
        'case_type',
    ];
}