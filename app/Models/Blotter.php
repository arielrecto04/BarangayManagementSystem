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
        'complainant_type',
        'complainant_id',
        'respondent_type',
        'respondent_id',  
        'place',  
        'datetime_of_incident',  
        'blotter_type',  
        'barangay_case_no',
        'total_cases',
        'Open_cases',
        'in_progress',
        'resolved',
    ];
    public function resident()
    {
        return $this->belongsToMany(Resident::class, 'complainant');
    }
    
}
    