<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryRequestItem extends Model
{
    protected $table = 'laboratory_request_items';

    protected $fillable = [
        'laboratory_request_id',
        'laboratory_sub_list_id',
        'status',
        'result_status',
        'remarks',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function request()
    {
        return $this->belongsTo(
            LaboratoryRequest::class,
            'laboratory_request_id'
        );
    }

    public function laboratory()
    {
        return $this->belongsTo(
            LaboratorySubList::class,
            'laboratory_sub_list_id'
        );
    }
}