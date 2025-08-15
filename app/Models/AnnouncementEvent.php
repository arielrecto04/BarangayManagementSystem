<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'author',
    ];
}
