<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AnnouncementEvent extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
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

    // Automatically cast date fields to Carbon instances
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Automatically calculate and set the status before saving
     * Only update status if dates are being changed or it's a new record
     */
    protected static function booted()
    {
        static::saving(function ($event) {
            // Only auto-calculate status if:
            // 1. It's a new record (creating), OR
            // 2. The dates are being changed
            if (!$event->exists || $event->isDirty(['start_date', 'end_date'])) {
                if ($event->start_date && $event->end_date) {
                    $event->status = $event->calculateStatus();
                } else {
                    $event->status = 'Upcoming';
                }
            }
        });
    }

    /**
     * Calculate the current status based on start and end dates
     *
     * @return string
     */
    public function calculateStatus(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return 'Upcoming';
        }

        $now = Carbon::now();
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        if ($now->lt($start)) {
            return 'Upcoming';
        } elseif ($now->between($start, $end)) {
            return 'Ongoing';
        } else {
            return 'Past';
        }
    }

    /**
     * Accessor to get real-time status even if stored status is outdated
     *
     * @return string
     */
    public function getCurrentStatusAttribute(): string
    {
        return $this->calculateStatus();
    }
}
