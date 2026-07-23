<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    protected $table = 'patients';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birthday',
        'age',
        'civil_status',
        'address',
        'city_municipality',
        'brgy',
        'contact_no',
        'hospital_no',
        'barcode',
        'profile',
        'printed',
        'users_id',
        'walkin',
    ];

    protected $casts = [
        'birthday' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ================= FULL NAME =================
    public function getFullNameAttribute()
    {
        return trim(
            $this->first_name . ' ' .
            $this->middle_name . ' ' .
            $this->last_name . ' ' .
            $this->suffix
        );
    }

    // ================= COMPUTED AGE =================
   public function getAgeAttribute()
{
    if (!$this->birthday) {
        return null;
    }

    return Carbon::parse($this->birthday)->age;
}

    // ================= SCOPES =================
    public function scopeFindByBarcode($query, $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeFindByHospitalNo($query, $hospitalNo)
    {
        return $query->where('hospital_no', $hospitalNo);
    }

    // ================= RELATIONSHIP =================
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function getBirthdayAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->format('F d, Y')
            : null;
    }
    // ================= API HELPER (VERY USEFUL FOR QR SYSTEM) =================
    public function toSearchResponse()
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'hospital_no' => $this->hospital_no,
            'birthday' => $this->birthday ? Carbon::parse($this->birthday)->format('M d, Y') : null,
            'barcode' => $this->barcode,
            'sex' => $this->sex,
            'age' => $this->age,
            'address' => $this->address,
            'contact_no' => $this->contact_no,
            'civil_status'=>$this->civil_status,
        ];
    }
}
