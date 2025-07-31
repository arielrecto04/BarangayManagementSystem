<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
    'complainant_name',
    'respondent_name',
    'case_no',
    'title',
    'description',
    'resolution',
    'date',
    'filing_date',
    'respondent_id',
    'complainant_id',
    'status',
];
    public function respondent_complaint()
    {
        return $this->belongsTo(Complaint::class,'respondent_id');

    }
    public function complainant_complaint()
    {
        return $this->belongsTo(Complaint::class,'complainant_id');
    }
}
