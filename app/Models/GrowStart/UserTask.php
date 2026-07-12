<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTask extends Model
{
    protected $table = 'growstart_user_tasks';

    protected $fillable = [
        'user_journey_id',
        'task_id',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(UserJourney::class, 'user_journey_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    public function scopeDone($query)
    {
        return $query->whereIn('status', ['completed', 'skipped']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }

    public function isDone(): bool
    {
        return $this->isCompleted() || $this->isSkipped();
    }

    public function getDurationInHours(): ?float
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        return round($this->started_at->diffInMinutes($this->completed_at) / 60, 2);
    }
}
