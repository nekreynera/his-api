<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicQueue extends Model
{
    protected $table = 'clinic_queue';

    protected $fillable = [
        'triage_id',
        'queue_number',
        'status',
        'arrived_at',
        'called_at',
        'started_at',
        'completed_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'arrived_at' => 'datetime',
        'called_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function triage()
    {
        return $this->belongsTo(Triage::class, 'triage_id');
    }

    public function assignment()
    {
        return $this->hasOne(DoctorAssignment::class, 'clinic_queue_id');
    }

    // ⭐ helper: check if already assigned
    public function getIsAssignedAttribute()
    {
        return $this->assignment !== null;
    }
}