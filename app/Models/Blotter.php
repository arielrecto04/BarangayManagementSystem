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
        'description'
    ];

    public function complainants()
    {
        return $this->morphToMany(Resident::class, 'complainant');
    }

    public function respondents()
    {
        return $this->morphToMany(Resident::class, 'respondent');
    }
}
