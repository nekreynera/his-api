<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
     protected $table = 'consultations';

    protected $fillable = [
        'doctor_assignment_id',
        'chief_complaint',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'icd_code_id',
        'status',
        'consultation_completed_at',
    ];

    protected $casts = [
        'consultation_completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Consultation belongs to a doctor assignment
    public function assignment()
    {
        return $this->belongsTo(
            DoctorAssignment::class,
            'doctor_assignment_id'
        );
    }

    // ICD Diagnosis
    public function icd()
    {
        return $this->belongsTo(
            IcdCode::class,
            'icd_code_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Accessors
    |--------------------------------------------------------------------------
    */

    // Patient
    public function getPatientAttribute()
    {
        return optional(
            $this->assignment?->queue?->triage
        )->patient;
    }

    // Doctor
    public function getDoctorAttribute()
    {
        return $this->assignment?->doctor;
    }

    // Clinic
    public function getClinicAttribute()
    {
        return optional(
            $this->assignment?->queue?->triage
        )->clinic;
    }

    // Queue
    public function getQueueAttribute()
    {
        return $this->assignment?->queue;
    }

    // Triage
    public function getTriageAttribute()
    {
        return $this->assignment?->queue?->triage;
    }
}
