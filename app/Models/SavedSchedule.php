<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSchedule extends Model
{
    protected $fillable = [
        'name',
        'schedule_data',
        'selected_events',
        'type',
        'user_session_id',
        'group_members'
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'selected_events' => 'array',
        'group_members' => 'array'
    ];

    public function getEventsCountAttribute()
    {
        return count($this->selected_events ?? []);
    }
}
