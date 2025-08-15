<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    use HasFactory;


    protected $fillable = [
        'resident_id',
        'vaccine_name',
        'dose',
        'date_administered',
        'administered_by',
        'remarks',
    ];


    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
