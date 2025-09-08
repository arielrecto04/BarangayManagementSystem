<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'start_date',
        'target_completion',
        'actual_completion',
        'funding_source',
        'barangay_zone',
        'completion_percentage',
        'milestone_achieved',
        'files', // JSON array of files
        'project_lead',
        'assigned_organizations', // JSON or comma-separated
        'number_of_members',
        'site_address',
        'disbursement_schedule',
        'challenges_encountered',
    ];

    // Cast JSON fields to array automatically
    protected $casts = [
        'files' => 'array',
        'assigned_organizations' => 'array',
        'start_date' => 'datetime',
        'target_completion' => 'datetime',
        'actual_completion' => 'datetime',
    ];
}
