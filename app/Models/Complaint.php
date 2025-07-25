<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_name',
        'respondent_name',
        'case_no',
        'title',
        'description',
        'resolution',
        'date',
        'filing_date',

    ];
}
