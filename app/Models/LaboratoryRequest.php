<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryRequest extends Model
{
    protected $table = 'laboratory_requests';

    protected $fillable = [
        'consultation_id',
        'request_no',
        'priority',
        'remarks',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function consultation()
    {
        return $this->belongsTo(
            Consultation::class,
            'consultation_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            LaboratoryRequestItem::class,
            'laboratory_request_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Computed Overall Status
    |--------------------------------------------------------------------------
    */

    public function getStatusAttribute()
    {
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }

        if ($this->items->isEmpty()) {
            return 'Pending';
        }

        if ($this->items->every(fn($item) => $item->status === 'Cancelled')) {
            return 'Cancelled';
        }

        if ($this->items->every(fn($item) => $item->status === 'Completed')) {
            return 'Completed';
        }

        if ($this->items->contains(fn($item) => in_array($item->status, [
            'Collected',
            'Processing',
            'Completed'
        ]))) {
            return 'Processing';
        }

        return 'Pending';
    }
}