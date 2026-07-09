<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriageVitalSign extends Model
{
    protected $table = 'triage_vital_signs';

    protected $fillable = [
        'triage_id',
        'chief_complaint',
        'systolic_bp',
        'diastolic_bp',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'weight',
        'height',
        'bmi',
        'taken_by',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function triage()
    {
        return $this->belongsTo(Triage::class, 'triage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }
}