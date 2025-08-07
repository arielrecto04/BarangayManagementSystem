<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    public function members(){
        return $this->hasMany(Resident::class);
    }

    public function head(){
        return $this->belongsTo(Resident::class, 'head_of_household_id');
    }
}
