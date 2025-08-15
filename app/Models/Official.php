<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    protected $fillable = ([
        'name',
        'description',
        'position',
        'terms',
        'no_of_per_term',
        'elected_date',
        'start_date',
        'end_date',
        'resident_id',

    ]);
    public function firstName()
    {
        return $this->belongsTo(Resident::class);
    }
}
