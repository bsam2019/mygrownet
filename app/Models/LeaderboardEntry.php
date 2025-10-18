<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class LeaderboardEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'leaderboard_id',
        'user_id',
        'position',
        'score',
        'tier_at_entry',
        'score_breakdown',
        'previous_position',
        'previous_score',
        'trend',
        'calculated_at'
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'previous_score' => 'decimal:2',
        'score_breakdown' => 'array',
        'calculated_at' => 'datetime'
    ];

    protected $attributes = [
        'score_breakdown' => '[]'
    ];

    // Relationships
    public function leaderboard(): BelongsTo
    {
        return $this->belongsTo(Leaderboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeTopPositions(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('position')->limit($limit);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier_at_entry', $tier);
    }

    public function scopeMovingUp(Builder $query): Builder
    {
        return $query->where('trend', 'up');
    }

    public function scopeMovingDown(Builder $query): Builder
    {
        return $query->where('trend', 'down');
    }

    public function scopeNewEntries(Builder $query): Builder
    {
        return $query->where('trend', 'new');
    }

    // Business Logic Methods
    public function getPositionChange(): int
    {
        if ($this->previous_position === null) {
            return 0; // New entry
        }

        return $this->previous_position - $this->position; // Positive = moved up
    }

    public function getScoreChange(): float
    {
        return $this->score - ($this->previous_score ?? 0);
    }

    public function isTopPosition(int $threshold = 3): bool
    {
        return $this->position <= $threshold;
    }

    public function getTrendIcon(): string
    {
        return match ($this->trend) {
            'up' => '↗️',
            'down' => '↘️',
            'same' => '➡️',
            'new' => '🆕',
            default => ''
        };
    }

    public function getTrendColor(): string
    {
        return match ($this->trend) {
            'up' => '#10b981', // Green
            'down' => '#ef4444', // Red
            'same' => '#6b7280', // Gray
            'new' => '#3b82f6', // Blue
            default => '#6b7280'
        };
    }

    public function getEntrySummary(): array
    {
        return [
            'position' => $this->position,
            'user_name' => $this->user->name,
            'score' => $this->score,
            'tier' => $this->tier_at_entry,
            'trend' => $this->trend,
            'trend_icon' => $this->getTrendIcon(),
            'position_change' => $this->getPositionChange(),
            'score_change' => $this->getScoreChange(),
            'is_top_position' => $this->isTopPosition(),
            'calculated_at' => $this->calculated_at,
            'score_breakdown' => $this->score_breakdown
        ];
    }
}