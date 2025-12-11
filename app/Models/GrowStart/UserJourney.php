<?php

namespace App\Models\GrowStart;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserJourney extends Model
{
    protected $table = 'growstart_user_journeys';

    protected $fillable = [
        'user_id',
        'industry_id',
        'country_id',
        'business_name',
        'business_description',
        'current_stage_id',
        'started_at',
        'target_launch_date',
        'status',
        'is_premium',
        'province',
        'city',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'target_launch_date' => 'date',
        'is_premium' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function userTasks(): HasMany
    {
        return $this->hasMany(UserTask::class, 'user_journey_id');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'growstart_user_badges', 'user_journey_id', 'badge_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getDaysActive(): int
    {
        return $this->started_at->diffInDays(now());
    }

    public function isOnTrack(): bool
    {
        if (!$this->target_launch_date) {
            return true;
        }
        return $this->target_launch_date->isFuture();
    }

    public function getCompletedTasksCount(): int
    {
        return $this->userTasks()
            ->whereIn('status', ['completed', 'skipped'])
            ->count();
    }

    public function getTotalTasksCount(): int
    {
        return Task::forIndustry($this->industry_id)
            ->forCountry($this->country_id)
            ->count();
    }

    public function getProgressPercentage(): float
    {
        $total = $this->getTotalTasksCount();
        if ($total === 0) {
            return 0;
        }
        return round(($this->getCompletedTasksCount() / $total) * 100, 1);
    }
}
