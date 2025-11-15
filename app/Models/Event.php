<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'location',
        'duration',
        'category'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function getTimeAttribute()
    {
        return $this->start_time->format('H:i') . '-' . $this->end_time->format('H:i');
    }

    public function conflictsWith(Event $otherEvent)
    {
        $thisStart = $this->start_time;
        $thisEnd = $this->end_time;
        $otherStart = $otherEvent->start_time;
        $otherEnd = $otherEvent->end_time;

        $timeConflict = !($thisEnd <= $otherStart || $otherEnd <= $thisStart);
        $locationConflict = $this->location === $otherEvent->location && $timeConflict;

        return $timeConflict || $locationConflict;
    }

}
