<?php

namespace App\Infrastructure\Persistence\Eloquent\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveEventModel extends Model
{
    protected $table = 'live_events';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_type',
        'scheduled_at',
        'duration_minutes',
        'meeting_link',
        'meeting_id',
        'meeting_password',
        'max_attendees',
        'host_name',
        'is_published',
        'requires_registration',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'max_attendees' => 'integer',
        'is_published' => 'boolean',
        'requires_registration' => 'boolean',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistrationModel::class, 'live_event_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendanceModel::class, 'live_event_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now());
    }

    public function scopeHappeningNow($query)
    {
        $now = now();
        return $query->where('scheduled_at', '<=', $now)
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) >= ?', [$now]);
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->scheduled_at > now();
    }

    public function getIsHappeningNowAttribute(): bool
    {
        $now = now();
        $endTime = $this->scheduled_at->addMinutes($this->duration_minutes);
        return $now >= $this->scheduled_at && $now <= $endTime;
    }

    public function getIsPastAttribute(): bool
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes) < now();
    }
}
