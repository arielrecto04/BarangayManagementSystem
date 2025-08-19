<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blotter extends Model
{
    protected $fillable = [
        'blotter_no',
        'filing_date',
        'title_case',
        'nature_of_case',
        'complainants_name',
        'complainants_id',
        'respondents_name',
        'respondents_id',
        'incident_location',
        'datetime_of_incident',
        'blotter_type',
        'barangay_case_no',
        'total_cases',
        'status',
        'description',
        'witness',
        'supporting_documents',
    ];

    protected $casts = [
        'filing_date' => 'datetime',
        'datetime_of_incident' => 'datetime',
        'supporting_documents' => 'array',
    ];

    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainants_id');
    }

    public function respondent()
    {
        return $this->belongsTo(Resident::class, 'respondents_id');
    }

    protected $with = ['complainant', 'respondent'];
    public function getSupportingDocumentsAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function setSupportingDocumentsAttribute($value)
    {
        $this->attributes['supporting_documents'] = json_encode($value);
    }
}
