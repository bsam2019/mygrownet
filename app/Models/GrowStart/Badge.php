<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $table = 'growstart_badges';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'criteria_type',
        'criteria_value',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function journeys(): BelongsToMany
    {
        return $this->belongsToMany(UserJourney::class, 'growstart_user_badges', 'badge_id', 'user_journey_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function isStageComplete(): bool
    {
        return $this->criteria_type === 'stage_complete';
    }

    public function isTasksComplete(): bool
    {
        return $this->criteria_type === 'tasks_complete';
    }

    public function isStreakDays(): bool
    {
        return $this->criteria_type === 'streak_days';
    }

    public function isJourneyComplete(): bool
    {
        return $this->criteria_type === 'journey_complete';
    }
}
