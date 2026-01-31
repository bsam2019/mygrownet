<?php

namespace App\Models\LifePlus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $table = 'lifeplus_tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'due_date',
        'due_time',
        'is_completed',
        'completed_at',
        'is_synced',
        'local_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_time' => 'datetime:H:i',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'is_synced' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scheduleBlocks(): HasMany
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', now()->toDateString());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->where('is_completed', false);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now()->toDateString())
            ->where('is_completed', false)
            ->orderBy('due_date');
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true)
            ->orderBy('completed_at', 'desc');
    }
}
