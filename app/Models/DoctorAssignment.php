<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorAssignment extends Model
{
    protected $table = 'doctor_assignments';

    protected $fillable = [
        'clinic_queue_id',
        'doctor_id',
        'doctor_queue_no',
        'status',
        'assigned_at',
        'called_at',
        'started_at',
        'paused_at',
        'resumed_at',
        'paused_reason',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Assignment belongs to a queue entry
    public function queue()
    {
        return $this->belongsTo(ClinicQueue::class, 'clinic_queue_id');
    }

    // Doctor (assuming users table)
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function consultation()
    {
        return $this->hasOne(
            Consultation::class,
            'doctor_assignment_id'
        );
    }
}