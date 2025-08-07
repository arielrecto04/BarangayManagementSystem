<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'health_worker_id',
        'visit_date',
        'reason_for_visit',
        'diagnosis',
        'treatment_notes',
        'prescription',
    ];


    // Defines the relationship that a visit belongs to a resident
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    // Defines the relationship that a visit was logged by a health worker (a user)
    public function healthWorker()
    {
        return $this->belongsTo(User::class, 'health_worker_id');
    }
}
