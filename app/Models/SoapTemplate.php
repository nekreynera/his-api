<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoapTemplate extends Model
{
    protected $table = 'soap_templates';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'name',
        'description',
        'chief_complaint',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'icd_code_id',
        'is_default',
        'active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function icdCode()
    {
        return $this->belongsTo(IcdCode::class, 'icd_code_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('active', 'Y');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', 'Y');
    }
}