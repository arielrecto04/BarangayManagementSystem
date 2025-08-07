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
        'complainants_type',
        'complainants_id',
        'respondents_type',
        'respondents_id',
        'place',
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
        'filing_date' => 'date',
        'datetime_of_incident' => 'date',
        'supporting_documents' => 'array',
    ];

    public function complainant()
    {
        return $this->morphTo('complainant', 'complainants_type', 'complainants_id');
    }

    public function respondent()
    {
        return $this->morphTo('respondent', 'respondents_type', 'respondents_id');
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

