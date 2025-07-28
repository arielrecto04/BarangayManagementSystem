<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birthday',
        'age',
        'gender',
        'address',
        'contact_number',
        'family_member',
        'emergency_contact',
    ];
    public function complainantBlotter()
    {
        return $this->hasMany(Blotter::class,'complainant');
    }
    public function residentIdOfficial()
    {
        return $this->hasMany(Official::class,'resident_id');
    }
    public function respondentIdComplaint()
    {
        return $this->hasMany(Complaint::class,'respondent_id');
    }
    public function complainantIdComplaint()
    {
        return $this->hasMany(Complaint::class,'complainant_id');
    }
    public function assignable()
    {
        return $this->morphTo();
    }
    
}
