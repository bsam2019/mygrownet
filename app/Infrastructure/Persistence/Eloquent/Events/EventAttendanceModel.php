<?php

namespace App\Infrastructure\Persistence\Eloquent\Events;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendanceModel extends Model
{
    protected $table = 'event_attendances';

    protected $fillable = [
        'user_id',
        'live_event_id',
        'checked_in_at',
        'checked_out_at',
        'attendance_minutes',
        'check_in_method',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'attendance_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(LiveEventModel::class, 'live_event_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCheckedInToday($query)
    {
        return $query->whereDate('checked_in_at', today());
    }
}
