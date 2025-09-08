<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'avatar',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'resident_number',
        'birthday',
        'age',
        'gender',
        'house_no',
        'street',
        'barangay',
        'city',
        'province',
        'zipcode',
        'email',
        'contact_number',
        'family_member',
        'emergency_contact',
        'contact_person',
        'place_of_birth',
        'civil_status',
        'citizenship',
        'religion',
        'years_of_residency',
        'voter_status',
        'voter_precinct_number',
        'valid_id_type',
        'valid_id_number',
        'sss_number',
        'philhealth_number',
        'tin_number',
        'pagibig_number',
        'occupation',
        'educational_attainment',
        'monthly_income',
        'employer_name',
        'employer_address',
        'is_pwd',
        'pwd_id_number',
        'disability_type',
        'is_senior_citizen',
        'senior_citizen_id_number',
        'is_4ps_beneficiary',
        '4ps_household_id',
        'is_registered_voter',
        'is_solo_parent',
        'solo_parent_id_number',
        'is_indigenous',
        'indigenous_group',
        'is_ofw',
        'ofw_country',
        'is_teen_parent',
        'is_lactating_mother',
        'is_pregnant',
        'resident_status',
        'medical_conditions',
        'allergies',
        'notes',
        'date_registered',
        'registered_by',
    ];

    /**
     * Relationships
     */
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
