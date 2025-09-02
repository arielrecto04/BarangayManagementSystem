<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'birthday',
        'resident_number',
        'email',
        'age',
        'gender',
        'address',
        'contact_number',
        'contact_person',
        'family_member',
        'emergency_contact',
        'avatar', // ✅ ADD THIS LINE - This was missing!
    ];

    public function complainantBlotter()
    {
        return $this->hasMany(Blotter::class, 'complainant');
    }

    public function officialRecords()
    {
        return $this->hasMany(Official::class);
    }

    public function respondentComplaints()
    {
        return $this->hasMany(Complaint::class, 'respondent_id');
    }

    public function complainantComplaints()
    {
        return $this->hasMany(Complaint::class, 'complainant_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function assignable()
    {
        return $this->morphTo();
    }

    public function documentRequests()
    {
        return $this->morphMany(DocumentRequest::class, 'requestable');
    }
}
