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
        return $this->morphMany(DocumentRequest::class,'requestable');
    }
}
