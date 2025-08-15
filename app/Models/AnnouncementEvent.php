<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'image',
        'status',
    ];

    // Automatically set status when creating or updating
    protected static function booted()
    {
        static::saving(function ($event) {
            $today = Carbon::today();

            if ($event->start_date && $event->end_date) {
                $start = Carbon::parse($event->start_date);
                $end = Carbon::parse($event->end_date);

                if ($today->lt($start)) {
                    $event->status = 'Upcoming';
                } elseif ($today->between($start, $end)) {
                    $event->status = 'Ongoing';
                } else {
                    $event->status = 'Past';
                }
            } else {
                // If dates are missing, default to Upcoming
                $event->status = 'Upcoming';
            }
        });
    }
}
