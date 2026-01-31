<?php

namespace App\Models\LifePlus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleBlock extends Model
{
    protected $table = 'lifeplus_schedule_blocks';

    protected $fillable = [
        'user_id',
        'task_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'color',
        'category',
        'is_completed',
        'completed_at',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_end_date',
        'is_synced',
        'local_id',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'is_recurring' => 'boolean',
        'recurrence_end_date' => 'date',
        'is_synced' => 'boolean',
    ];

    protected $appends = ['duration_minutes'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function getDurationMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time');
    }

    public function scopeToday($query)
    {
        return $query->where('date', now()->toDateString())
            ->orderBy('start_time');
    }
}
