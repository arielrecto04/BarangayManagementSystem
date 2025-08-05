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
        'filing_date',
        'respondent_id',
        'complainant_id',
        'status',
        'complainant_name',
        'respondent_name',
        'nature_of_complaint',
        'incident_datetime',
        'incident_location',
        'supporting_documents',
        'witness',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
    ];
    public function respondent()
    {
        return $this->belongsTo(Resident::class, 'respondent_id');
    }
    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }
}
