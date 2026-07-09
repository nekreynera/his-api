<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Triage extends Model
{
    protected $table = 'triage';

    protected $fillable = [
        'patients_id',
        'users_id',
        'clinic_id',
        'finished',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Patient info
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patients_id');
    }

    // Clinic assigned
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    // User who handled triage (optional staff)
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // Link to clinic queue (if already arrived in clinic)
    public function queue()
    {
        return $this->hasOne(ClinicQueue::class, 'triage_id');
    }

     // ⭐ IMPORTANT: frontend-friendly flag
    public function getIsCheckedInAttribute()
    {
        return $this->queue !== null;
    }

    public function vitalSign()
    {
        return $this->hasOne(TriageVitalSign::class, 'triage_id');
    }
}