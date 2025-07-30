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
        'status'
    ];

    protected $casts = [
        'filing_date' => 'date',
        'datetime_of_incident' => 'date'
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
}
