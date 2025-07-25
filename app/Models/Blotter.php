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
        'complainants',  
        'respondents',  
        'place',  
        'datetime_of_incident',  
        'blotter_type',  
        'barangay_case_no',
    ];
}
