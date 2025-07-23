<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birthday',
        'age',
        'gender',
        'address',
        'contact_number',
        'family_member',
        'emergency_contact',
    ];
}
